<?php
/**
 * 阿里平台签名生成类。
 *
 * 签名生成规则：
 *  通过URL传递的参数除sig_sign外，变量的参数是升序排列，
 *  然后连接字符串形成： $sip_sign=md5(sip_appsecret+[param1+value1…paramn+valuen])。
 *  其中sip_appsecret是软件注册时候的唯一的字符串（32字符）。
 *
 */
class SignGenerator{
	/**
	 * 阿里平台SIP_SIGN签名参数生成函数。
	 *
	 * @param array $params 用于URL传递的除sig_sign之外的所以参数的数组，
	 * 	键名是参数名，值为参数值。
	 * @return string
	 */
	public static function generateSign($params){
		$signStr = CERT_CODE ;
		ksort($params);
		foreach ($params as $key => $value) {
			$signStr .= $key . $value;
		}
		writeDebug('sign string:' . $signStr);
		$sip_sign = strtoupper ( md5 ( $signStr ) );
		writeDebug("The generated sign is: " . $sip_sign );
		return  $sip_sign;
	}
}
?>