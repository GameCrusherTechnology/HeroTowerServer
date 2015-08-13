<?php
require_once FRAMEWORK .'/snoopy/Snoopy.class.php';
require_once FRAMEWORK . '/platform/alisoft/SignGenerator.class.php';

class RESTRequestHelper{
	private static $error_codes = array(
	'1001' => '签名无效',
	'1002' => '请求已过期',
	'1004' => '需要绑定用户',
	'1005' => '需要提供appid',
	'1006' => '需要提供服务名',
	'1007' => '需要提供签名',
	'1008' => '需要提供时间戳',
	'1010' => '无权访问服务',
	'1011' => '服务不存在',
	'1012' => '需要提供SessionId',
	'1013' => '需要提供用户名',
	'1014' => '回调服务不存在',
	'1015' => 'AppKey不存在',
	'1016' => '服务次数超过限制',
	'1017' => '服务请求过于频繁',
	'1018' => '登录请求URL过长',
	'1019' => 'ISP请求IP非法',
	'1020' => '请求参数值长度溢出',
	'1021' => 'isp处理请求失败',
	'0000' => '未知异常',
	'8888' => '调用补偿'
	);
	/**
	 * 向阿里REST服务器发送调用请求。
	 *
	 * @param string $apiname api名字
	 * @param string $appInstanceId 用户订购的appid
	 * @param string $userId 用户id
	 * @param string $token 阿里平台产生的请求token
	 * @param string $sip_sessionid 用户的session id
	 * @param string $sip_format 请求结果格式，xml或者json，默认xml。
	 * @param string $otherParams 其他附加的参数。
	 * @return string  请求结果字符串。
	 */
	public static function postRequest($apiname, $appParams = null,$sip_sessionid = null, $sip_format = null){
		//准备本次请求参数
		$timestamp = getTimeStamp();
		
		/* REST request url. Use snoopy to submit a request
		$url = $this->SIP_URL_PRE."?sip_timestamp=".date("Y-m-d H:i:s")
		.'&sip_appkey='.$this->APP_ID.'&appId='.$this->APP_ID.'&sip_sign='.$sip_sign.
		'&token='.$token.'&appInstanceId='.$appInstanceId
		.'&userId='.$userId.'&sip_apiname=alisoft.validateUser'.'&sip_sessionid='.$sip_sessionid;
		*/
		$formVars ['sip_appkey'] = APP_ID;
		$formVars ['sip_apiname'] = $apiname;
		$formVars ['sip_timestamp'] = $timestamp;
		$formVars ['appId'] = APP_ID;
		if(isset($sip_sessionid)){
			$formVars ['sip_sessionid'] = $sip_sessionid;
		}
		if(isset($sip_format)){
			$formVars['sip_format'] = $sip_format;
		}
		
		if(isset($appParams) && is_array($appParams)){
			$formVars = array_merge($formVars,$appParams);
			//writeDebug('form var before encode:' . print_r($formVars,true));
			//$encodeParams = self::encodeParams($appParams);
		}
		// 生成签名
		$sip_sign = SignGenerator::generateSign($formVars);
		
		$formVars ['sip_sign'] = $sip_sign;
		//$formVars = array_merge($formVars,$encodeParams);
		writeDebug(sprintf("API: %s\nParams: %s",$apiname,print_r($formVars, true)));
		$proxy = new Snoopy ( );
		//$proxy->set_submit_multipart();
		//向AEP发出服务端请求
		$proxy->submit ( SIP_REST_URI, $formVars );
		WriteDebug("http header:" . print_r($proxy->headers,true));
		self::handleError($proxy->headers);
		// 返回请求结果
		return $proxy->results ;
	}
	private static function encodeParams($appParams){
		if(is_array($appParams)){
			foreach ($appParams as $key => $param) {
				$appParams[$key] = urlencode($param);
			}
		}
		return $appParams;
	}
	private static function handleError($headers){
		if(!is_array($headers)){
			return false;
		}
		$codes = preg_grep("/^sip_status:\s*\d{4}.*/",$headers);
		//WriteDebug("sip_status:" . print_r($codes, true));
		$codes = array_values($codes);
		if(count($codes) > 0){
			$code = $codes[0];
			$error_code = trim(substr($code,11));
			if($error_code != '9999'){
				$message = self::$error_codes[$error_code];
				showmessage(sprintf('请求API服务发生错误。<br />Code:%s<br />Message:%s',$error_code,$message));
			}
		}
	}
}
?>