<?php
/**
 * 用来产生请求签名的类。
 * 签名的规则是把所有的参数，除了sig参数外，把参数名和参数值
 */
class Signature {
	/**
	 * 根据传入的参数和一个加密key，产生一个签名字符串。
	 * @param array $params 原始用来产生签名的参数列表，是以参数名和参数值的键值断
	 * @param string $sig_key 双方约定的加密key
	 * @param string $key 加密字符串在参数数组中使用的key值
	 * @return string
	 */
	public static function sign(array $params,$sig_key, $key = 'sig'){
		global $framework_logger;
		$signStr = $sig_key ;
		if(isset($params[$key])){
			unset($params[$key]);
		}
		$signStr .= self::getSignString($params);
		$sip_sign = strtoupper ( md5 ( $signStr ) );
		if ($framework_logger->isDebugEnabled()) {
			$framework_logger->writeDebug("generated sign string : $signStr, and signed for : $sip_sign");
		}
		return  $sip_sign;
	}
	
	protected static function getSignString(array $params){
		$signStr = '';
		ksort($params, SORT_STRING);
		foreach ($params as $k => $val) {
			if(is_array($val) || is_object($val)){
				$signStr .= $k . self::getSignString($val);
			}
			else{
				$signStr .= $k . $val;
			}
		}
		return $signStr;
	}
}

?>