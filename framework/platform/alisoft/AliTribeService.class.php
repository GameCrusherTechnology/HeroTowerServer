<?php
require_once FRAMEWORK . '/platform/alisoft/RESTRequestHelper.class.php';

class AliTribeService {
	/**
	 * 传入群号，获取群成员列表
	 *
	 * @param string $trib_id 群号
	 * @return array 每一个元素包含一个用户id
	 */
	public static function getTribeMembers($trib_id) {
		$result = RESTRequestHelper::postRequest('alisoft.im.getTribMemberList',
		array('tribId' => $trib_id));
		writeDebug('request result: ' . $result);
		$xml = new SimpleXMLElement($result);
		$users = array();
		foreach ($xml->LongId as $id){
			$users[] = (string)$id;
		}
		return $users;
	}
	/**
	 * 传入群号，获取此旺旺群群主的网站ID，群管理员的网站ID
	 *
	 * @param string $trib_id
	 * @return array 其中每一个元素以用户id为key，role为值
	 */
	public static function getTribChiefUsers($trib_id){
		$result = RESTRequestHelper::postRequest('alisoft.im.getTribChiefUser',
		array('tribId' => $trib_id));
		writeDebug('request result: ' . $result);
		$xml = new SimpleXMLElement($result);
		$users = array();
		foreach ($xml->node as $user) {
			$users[$user->LongId] = $user->userRole;
		}
		return $users;
	}
	
	/**
	 * 取得一个用户在群组中的role
	 *
	 * @param string $trib_id
	 * @param string $user_id
	 * @return string
	 */
	public static function getUserTribRole($trib_id,$user_id){
		$result = RESTRequestHelper::postRequest('alisoft.im.getUserRole',
		array('tribId' => $trib_id,
		'LongId' => $user_id));
		writeDebug('request result: ' . $result);
		$xml = new SimpleXMLElement($result);
		return (string)$xml->userRole;
	}
	
	/**
	 * 取得群的基本信息。
	 *
	 * @param string $trib_id
	 * @return array 包含以下信息
	 * key   		值描述
	 * ret   		返回值
	 * tribId		群组Id
	 * tribName 	群组名称
	 * tribDescribe 群组描述
	 */
	public static function getTribInfo($trib_id){
		$result = RESTRequestHelper::postRequest('alisoft.im.getTribInfo',
		array('tribId' => $trib_id));
		writeDebug('request result: ' . $result);
		$result = str_replace('&','&amp;',$result);
		$xml = new SimpleXMLElement($result);
		$info['group_id'] = (string)$xml->tribId;
		$info['group_name'] = (string)$xml->tribName;
		$info['description'] = (string)$xml->tribDescrib;
//        $result = json_decode($result,true);
//		writeDebug('request result after decode: ' . print_r($result,true));
//		$info['group_id'] = $result['tribId'];
//		$info['group_name'] = $result['tribName'];
//		$info['describe'] = $result['tribDescrib'];
		return $info;
	}
	
	/**
	 * 向群组发送一条消息。
	 *
	 * @param string $trib_id
	 * @param string $message
	 * @return int 成功发送消息返回0
	 */
	public static function sendTribMessage($trib_id,$message){
		$result = RESTRequestHelper::postRequest('alisoft.im.sendTribMessage',
		array('tribId' => $trib_id,
		'message' => $message));
		writeDebug('request result: ' . $result);
		return trim(strip_tags($result));
	}
	
	/**
	 * 验证群用户
	 *
	 * @param string $userId
	 * @param string $appInstanceId
	 * @param string $toke
	 * @return array 包含tribId和返回值的数组
	 *<table>
	 *  <tr><td>返回值</td><td>返回值描述   		</td><td>返回值说明</td></tr>
	 *  <tr><td>3 	</td><td>用户是群主 			</td><td>群主也是该应用的订购者</td></tr>
	 *	<tr><td>2	</td><td>用户是群的管理员		</td><td>管理员是针对该群的，但不是该应用的订购者</td></tr>
	 *	<tr><td>1	</td><td>用户是群的有效成员	</td><td>有效的群成员，不包括游客，不是该应用的订购者</td></tr>
	 *	<tr><td>0	</td><td>用户是群游客		</td><td>临时的群成员，不是该应用的订购者</td></tr>
	 *	<tr><td>-1	</td><td>用户不是群内有效成员	</td><td>不是有效的群成员，包括游客</td></tr>
	 *	<tr><td>-2	</td><td>订购无效			</td><td>订购已过期、或者未订购</td></tr>
	 *	<tr><td>-3	</td><td>token无效		</td><td>token被篡改、或者已过期</td></tr>
	 *	<tr><td>-9	</td><td>系统异常</td><td></td></tr>
	 * </table>
	 */
	public static function validateUser($userId, $appInstanceId, $token){
		$sip_sessionid = session_id();
		// 发送REST请求
		$result = RESTRequestHelper::postRequest('alisoft.validateTribeUser',
			array('appInstanceId' => $appInstanceId,
			'userId' => $userId,
			'token' => $token),
			$sip_sessionid);
		//$result = json_decode($result,true);
		//writeDebug('request result: ' . print_r($result,true));
		//return $result;
		writeDebug('request result: ' . $result);
		$xml = new SimpleXMLElement($result);
		$trib_id = $xml->tribeId;
		$result_code = $xml->result;
		return array('tribeId' => (string)$trib_id, 'result' => (string)$result_code);
	}
}

?>