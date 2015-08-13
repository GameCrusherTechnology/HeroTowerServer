<?php
header('Cache-control: no-cache, no-store, must-revalidate');
/* 
  +-----------------------------------------------------------------------------+
  | 139.com Platform PHP5 client	                                            |
  +-----------------------------------------------------------------------------+
*/
define('MIOP_API_URL','http://api.139.com/restserver.php');/*�޸ĳ��Լ�������*/
define('MIOP_API_PHP_SDK_VERSION','1.0');

class MiopClient {
	var $app_key;
	var $app_secret;
	var $session_key;
	var $user;
	var $time;
	var $friends_list;
	var $final_encode;		// the encoding print out finally  ����������ݵı����ʽ

	var $server_addr;		// ����Ľӿ��ļ����

	/**
	* ����API�ͻ��� 
	* @param string $session_key ���$session_keyû��ֵ,��Ĭ��Ϊnull. 
	*                            ���Ĳ������丳ֵ.
	*                            
	*/
	function __construct($app_key, $app_secret, $session_key=null, $user=null, $time=null) {
		$this->app_key		= $app_key;
		$this->app_secret	= $app_secret;
		$this->session_key	= $session_key;
		$this->user			= $user;
		$this->time			= $time;
		$this->final_encode	= "utf-8";
		$this->last_call_id = 0;

		$this->server_addr  =  MIOP_API_URL;
	}

	/**
	 * ���� php4
	 */
	function MiopClient($app_key, $app_secret, $session_key=null, $user=null, $time=null) {
		$this->__construct($app_key, $app_secret, $session_key, $user, $time);
	}

	/**
	 * ����Token
	 *
	 */
	function &auth_createToken() {
		return $this->call_method('miop.auth.createToken',array());
	}

	/**
	 * ���Token��ȡsession_key.
	 *
	 */
	function &auth_getSession($token) {
		return $this->call_method('miop.auth.getSession',array('auth_token'=>$token));
	}


	/**
	* ��ȡ����ID�б�
	* ����uid�����ȡ��ǰ��¼�û��ĺ��ѣ�
	* ָ��uid���򷵻�ָ��uid�ĺ���
	* ָ����uid�����ǵ�ǰ��Ȩ��App���ʵ��û�ID
	*
	*/
	function &friends_get($uid=null) {
		if (isset($this->friends_list)) {
		  return $this->friends_list;
		}
		return $this->call_method('miop.friends.get', array('uid'=>$uid));
	}


	/**
	* ���ص�ǰsession�û��ĺ�������ӵ�ǰӦ�õ�ID�б� 
	* @return array of user
	*/
	function &friends_getAppUsers() {
		return $this->call_method('miop.friends.getAppUsers', array());
	}


	/**
	* �Ƚ�}���û��Ĺ�ϵ
	* uids1 �� uids2 }������Ԫ�صĸ��������ͬ
	* uids1 �� uids2 }�������Ԫ�ر����ǵ�ǰ��¼�û���ǰ��¼�û��ĺ���
	*/
	function &friends_areFriends($uids1, $uids2) {
		return $this->call_method('miop.friends.areFriends', array('uids1'=>$uids1, 'uids2'=>$uids2));
	}

	/**
	* ���ز�ѯ�û����ϵ�ָ���ֶ� 
	* user_ids ��ѯ�û�������,�������Ϊ��,���ѯ��ǰ�û����û�����. 
	* fields ָ�����ϵ��ֶ�
	*
	*/
	function &users_getInfo($user_ids, $fields) {
		return $this->call_method('miop.users.getInfo', array('uids' => $user_ids, 'fields' => $fields));
	}

	/**
	* ���ص�ǰsession���û�ID 
	*
	*/
	function &users_getLoggedInUser() {
		return $this->call_method('miop.users.getLoggedInUser', array());
	}

	/**
	* �ж��û��Ƿ����赱ǰApp����
	* ��ָ��user_id��Ĭ���ǵ�ǰsession���û�.
	*
	*/
	function &users_isAppUser($user_id) {
		$params = array();
		if( intval($user_id) ) {
			$params['uid'] = intval($user_id);
		}
		return $this->call_method('miop.users.isAppUser', $params);
	}

	/**
	* ��ָ���û��ķ���֪ͨ�� 
	*
	*/
	function &notifications_send($notification,$to_ids=array(),$type='app_to_user', $params ) {
		$params += array('notification'=>$notification,
				'type'=>$type,
				'format'=>'json',
				'to_ids'=>$to_ids);
		return $this->call_method('miop.notifications.send', $params );
	}

	/**
	* ��ǰ�û����ѷ���feed,Ŀǰֻ��֧�ְ�װapp��feed������:{"title":{"text":"����","href":"http://game.139.com/gift/"}} 
	*
	*/
	function &feed_publishTemplatizedAction($template_data, $templateId, $feedType=null, $pushFlag=null, $targetId=null, $targetType=null, $receiveId = null) {
		$options = array(
					'template_data'	=>$template_data,
					'template_id'	=>$templateId
					
				);
		if( isset( $feedType ) ) $options['feed_type'] = $feedType;
		if( isset( $pushFlag ) ) $options['push_flag'] = $pushFlag;
		if( isset( $targetId ) ) $options['target_id'] = $targetId;
		if( isset( $targetType ) ) $options['target_type'] = $targetType;
		if( isset( $receiveId ) ) $options['receive_id'] = $receiveId;
		return $this->call_method('miop.feed.publishTemplatizedAction', $options );
	}



	
	/**
	 * post params to the API at 139.com
	 */
	function create_post_string($method, $params) {

		$namespace = "mi_sig";

		$params['user'] = $this->user;
		$params['session_key'] = $this->session_key;
		$params['api_key'] = $this->app_key;
		$params['time'] = $this->time;
		$params['method'] = $method;
		$params['v'] = MIOP_API_PHP_SDK_VERSION;

		$params['call_id'] = $this->get_microtime();
		if ($params['call_id'] <= $this->last_call_id) {
		  $params['call_id'] = $this->last_call_id + 0.001;
		}
		$this->last_call_id = $params['call_id'];

		$prefix = $namespace . '_';
		$prefix_len = strlen($prefix);
		$fb_params = array();
		$post_data = "";
	    
		foreach ($params as $name => $val) {
			if (is_array($val)) {
				$val = implode(',', $val);
			}
			$params[$name] = Miop::no_magic_quotes($val);
		}
		$sig = Miop::generate_signature($params, $this->app_secret);
		foreach ($params as $name => $val)
		{
			if($this->final_encode != "utf-8" && $this->final_encode != "utf8")
				$val = iconv($this->final_encode,"utf-8",$val);

			//$post_data .= $prefix . $name . "=" . urlencode($val) . "&";
			$post_data .= $name . "=" . urlencode($val) . "&";
		}
		$post_data .= $namespace . "=" . $sig;
	    
		return $post_data;
	}

	/**
	 * Interprets a string of XML into an array
	 */
	function convert_xml_to_result($xml, $method, $params) {

		$sxml = simplexml_load_string($xml);
		$result = self::convert_simplexml_to_array($sxml);

		return $result;
	}


	function post_request($method, $params) {
		$post_string = $this->create_post_string($method, $params);
		$result = httpRequest($this->server_addr, $post_string);
		return $result;
	}

	/**
	 * set the encoding that printing out
	 * ���÷��ص���ݱ���
	 */
	function set_encoding($enc="utf-8")
	{
		$arrEnc = array("utf-8", "gbk", "gb2312");
		if(!in_array(strtolower($enc), $arrEnc)) {
			$enc="utf-8";
		}
		$this->final_encode = $enc;
	}

	/**
	 * Performs a character set conversion on the string str from utf-8 to gbk
	 * @param	string	str		the string want to convert
	 * @param	bool	ignore	if ignore the chars that represented failed,
	 * @return	string	str		represented string
	 */
	function utf2Gbk($str, $ignore=true)
	{
		$this->final_encode = strtolower($this->final_encode);
		if($this->final_encode == "utf-8" || $this->final_encode == "utf8") {
			return $str;
		}
		if($ignore) {
			return iconv("utf-8", "{$this->final_encode}//IGNORE", $str);
		}else {
			return iconv("utf-8", "{$this->final_encode}", $str);
		}
	}

	function convert_simplexml_to_array($sxml) {
		$arr = array();
		if ($sxml) {
			foreach ($sxml as $k => $v) {
				if ($sxml['list']) {
					$arr[] = self::convert_simplexml_to_array($v);
				} else {
					$tmp = self::convert_simplexml_to_array($v);
					if(strtolower($v['enc']) == "base64") {
						$arr[$k] = self::utf2Gbk(base64_decode($tmp));
					}else {
						$arr[$k] = self::utf2Gbk($tmp);
					}
				}
			}
		}
		if (count($arr) > 0) {
			return $arr;
		} else {
			return (string)$sxml;
		}
	}

	/* UTILITY FUNCTIONS */
	function &call_method($method, $params) {

		$params['sdk_from'] = "php";
		$retStr = $this->post_request($method, $params);
		if($retStr == false) {
			$arr['error_code'] = 2;
			$arr['error_msg'] = "Service temporarily unavailable";
			return $arr;
		}
		if( $retStr ) { 
			if (empty($params['format']) || strtolower($params['format']) != 'json') {
				$result = $this->convert_xml_to_result($retStr, $method, $params);
			} else {
				$result = json_decode($retStr,true);
			}
			if($result === "") {
				$result = array();
			}
			return $result;
		}	
		return array();
	}

	/**
	 * 
	 */
	function get_microtime()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}

} // end class


/**
 * http post
 */
function httpRequest($url, $post_string, $connectTimeout = CONNECT_TIMEOUT, $readTimeout = READ_TIMEOUT)
{
	$result = "";
	if (function_exists('curl_init')) {
		$timeout = $connectTimeout + $readTimeout;
		// Use CURL if installed...
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connectTimeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, '139.com API PHP5 Client 1.1 (curl) ' . phpversion());
		$result = curl_exec($ch);
		curl_close($ch);
	} else {
		// Non-CURL based version...
		$result = socketPost($url, $post_string, $connectTimeout = CONNECT_TIMEOUT, $readTimeout = READ_TIMEOUT);
	}
	return $result;
}

/**
 * http post
 */
function socketPost($url, $post_string, $connectTimeout = CONNECT_TIMEOUT, $readTimeout = READ_TIMEOUT){
	$urlInfo = parse_url($url);
	$urlInfo["path"] = ($urlInfo["path"] == "" ? "/" : $urlInfo["path"]);
	$urlInfo["port"] = ($urlInfo["port"] == "" ? 80 : $urlInfo["port"]);
	$hostIp = gethostbyname($urlInfo["host"]);

	$urlInfo["request"] =  $urlInfo["path"]	. 
		(empty($urlInfo["query"]) ? "" : "?" . $urlInfo["query"]) . 
		(empty($urlInfo["fragment"]) ? "" : "#" . $urlInfo["fragment"]);

	$fsock = fsockopen($hostIp, $urlInfo["port"], $errno, $errstr, $connectTimeout);
	if (false == $fsock) {
		return false;
	}
	/* begin send data */
	$in = "POST " . $urlInfo["request"] . " HTTP/1.0\r\n";
	$in .= "Accept: */*\r\n";
	$in .= "User-Agent: 139.com API PHP5 Client 1.1 (non-curl)\r\n";
	$in .= "Host: " . $urlInfo["host"] . "\r\n";
	$in .= "Content-type: application/x-www-form-urlencoded\r\n";
	$in .= "Content-Length: " . strlen($post_string) . "\r\n";
	$in .= "Connection: Close\r\n\r\n";
	$in .= $post_string . "\r\n\r\n";

	stream_set_timeout($fsock, $readTimeout);
	if (!fwrite($fsock, $in, strlen($in))) {
		fclose($fsock);
		return false;
	}
	unset($in);

	//process response
	$out = "";
	while ($buff = fgets($fsock, 2048)) {
		$out .= $buff;
	}
	//finish socket
	fclose($fsock);
	$pos = strpos($out, "\r\n\r\n");
	$head = substr($out, 0, $pos);		//http head
	$status = substr($head, 0, strpos($head, "\r\n"));		//http status line
	$body = substr($out, $pos + 4, strlen($out) - ($pos + 4));		//page body
	if (preg_match("/^HTTP\/\d\.\d\s([\d]+)\s.*$/", $status, $matches)) {
		if (intval($matches[1]) / 100 == 2) {//return http get body
			return $body;
		} else {
			return false;
		}
	} else {
		return false;
	}
}
?>
