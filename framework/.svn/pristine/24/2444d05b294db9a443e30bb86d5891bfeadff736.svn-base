<?php
require_once FRAMEWORK . '/platform/alisoft/RESTRequestHelper.class.php';

/**
 * 阿里软件平台的REST方式认证
 * [url]http://sipdev.alisoft.com/sip/rest[/url]
 * ?sip_timestamp=2008-05-02+18%3A48%3A12
 * &sip_appkey=10184
 * &appId=10184&userId=2175944
 * &sip_sign=713A26A75979CB2FCF915EE2DE41F2C7
 * &appInstanceId=f2bf7b29-abee-4a3e-8678-949cf8bad9c7
 * &token=0C1BC761C916D2A203D188993F29621D2D739299195A75B07CDC9A4E534C28F1FCF006F0FB8DE9E24E731D93275AFC56
 * &sip_apiname=alisoft.validateUser
 * &sip_sessionid=b4927f1fef8b4155b2c9b5e2dfe5ae0e
 *
 * sip_sign=md5(sip_appsecret+[param1+value1…paramn+valuen])
 */
class AlisoftValidateUserService {
	/**
	 * validate user by alisoft REST api.
	 *
	 * @param string $userId
	 * @param string $appInstanceId
	 * @param string $token
	 * @return string If the validate success, the return code of the API call.
	 * Otherwise return false.
	 * The API return code:
	 * 返回值  	返回值描述  	返回值说明
	 *	1 	应用的订购者 	订购者就是订购该服务的人
	 *	0 	应用的使用者 	使用者就是使用该服务的人，只有被订阅者授权(邀请)以后才可以使用该服务
	 *	-1 	尚未订购该应用 	表示尚未订购该应用的用户
	 *	-2 	非法用户 	表示非法用户(没有正常登录软件互联平台，或者URL已过期失效)
	 */
	public static function validateUser($userId, $appInstanceId, $token) {
		$sip_sessionid = session_id();
		// 发送REST请求
		$result = RESTRequestHelper::postRequest('alisoft.validateUser',
			array('appInstanceId' => $appInstanceId,
			'userId' => $userId,
			'token' => $token),
			$sip_sessionid);
	
		writeDebug('request result: ' . $result);
		// 解析返回的xml结果，返回解析后的调用返回值
		$ret_code = AlisoftValidateUserService::getReturnCode ( $result);
		return $ret_code;
	}
	
	/**
	 * 查询用户是否订阅，订阅是否过期。
	 * @param $appInstanceId 用户订阅的app实例id
	 *
	 * @return
	 * 1. 用户没有订阅，返回false
	 * 2. 用户已订阅，但是已过期，返回订阅结束的时间
	 * 3. 用户已订阅，并且没有过期，返回true
	 *
	 */
	public static function isSubscriber($appInstanceId){
		$sip_sessionid = session_id();
		$result = RESTRequestHelper::postRequest('alisoft.getSubscCtrl',
		 array('appInstanceId' => $appInstanceId),$sip_sessionid);
		writeDebug("getSubscCtrl of appInstance $appInstanceId result:" . $result);
		 if($result == null || strlen($result) < 1){
			return false;
		}
		$xml = new SimpleXMLElement($result);
		$endDate = $xml->gmtEnd;
		writeDebug('End date of subscribe:' . $endDate);
		$endDate = substr($endDate,0,10);
		$currentDate = getTimeStamp('Y-m-d');
		if($endDate <= $currentDate){
			return $endDate;
		}
		return true;
	}
	/**
	 * 验证用户是否是该应用的使用者。
	 *
	 * @param string $userId
	 * @param string $appInstanceId
	 *
	 * @return boolean 如果该用户是应用的使用者，返回true，否则返回false。
	 */
	public static function isAppUser($userId,$appInstanceId){
		$sip_sessionid = session_id();
		$result = RESTRequestHelper::postRequest('alisoft.validateAppUser',
		 array('appInstanceId' => $appInstanceId,'userId' => $userId),$sip_sessionid);
		writeDebug("validateAppUser of appInstance $appInstanceId result:" . $result);
		$ret_code = AlisoftValidateUserService::getReturnCode($result);
		if($ret_code == 1){
			return true;
		}
		return false;
	}
	
	private static function getReturnCode($result) {
		return trim(strip_tags($result));
//		$reg = "/.*<String>(.*)</String>.*/";
//		if (preg_match ( $reg, $result, $matchs )) {
//			// the first match is the return code
//			return $matchs [1];
//		}
//		// not match found
//		return false;
	}
}
?>