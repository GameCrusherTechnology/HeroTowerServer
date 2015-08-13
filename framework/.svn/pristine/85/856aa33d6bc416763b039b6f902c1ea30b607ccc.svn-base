<?php
/**
 * 各个平台API请求的基类
 * @author shusl
 * @since v2.0
 *
 */
class APIPlugInBase {

	protected $secret;

	protected $session_key;

	protected $api_key;

	protected $version = '1.0';

	protected $server_addr = '';

	protected $method;
	
	protected $method_prefix = '';
	/**
     * request timeout period, in seconds
     * @var int
	 */
	protected $timeout = 5;
	
	protected $throwException = false;
	

	public function __construct($api_key, $secret, $session_key, $v = '1.0') {
		$this->secret = $secret;
		$this->api_key = $api_key;
		$this->version = $v;
		$this->session_key = $session_key;
	}
	/**
	 * 设置是否在发送错误时候，抛出exception
	 * @param $throwException the $throwException to set
	 */
	public function setThrowException($throwException) {
		$this->throwException = (bool)$throwException;
	}

	/**
	 * @return the $throwException
	 */
	public function getThrowException() {
		return $this->throwException;
	}

	/**
	 * 设置api方法的前缀
	 * @param $method_prefix the $method_prefix to set
	 */
	public function setMethodPrefix($method_prefix) {
		$this->method_prefix = $method_prefix;
	}

	/**
	 * 设置api服务器的地址
	 * @param $server_addr the $server_addr to set
	 */
	public function setServerAddress($server_addr) {
		$this->server_addr = $server_addr;
	}

	/**
	 * 获取api方法的前缀
	 * @return the $method_prefix
	 */
	public function getMethodPrefix() {
		return $this->method_prefix;
	}

	/**
	 * 获取api服务器的地址
	 * @return the $server_addr
	 */
	public function getServerAddress() {
		return $this->server_addr;
	}


	protected function create_post_string($method, $params) {
		$params['method'] = $method;
		$params['session_key'] = $this->session_key;
		$params['api_key'] = $this->api_key;
		$params['call_id'] = microtime(true);
		if(! isset($params['v'])){
			$params['v'] = '1.0';
		}
		$post_params = array();
		foreach($params as $key => &$val){
			if(is_array($val))
				$val = implode(',', $val);
			$post_params[] = $key . '=' . urlencode($val);
		}
		$secret = $this->secret;
		$post_params[] = 'sig=' . $this->generate_sig($params, $secret);
		return implode('&', $post_params);
	}
	
	protected static function generate_sig($params_array, $secret) {
		$str = '';
		ksort($params_array);
		// Note: make sure that the signature parameter is not already included in
		//       $params_array.
		if(isset($params_array['sig'])){
			unset($params_array['sig']);
		}
		foreach ($params_array as $k=>$v) {
		  $str .= "$k=$v";
		}
		$str .= $secret;
		return md5($str);
	}
		  
	protected function post_request($method, $params) {
		$this->method = $method;
		$post_string = $this->create_post_string($method, $params);
		
		if(function_exists('curl_init')){
			// Use CURL if installed...
			$result = $this->curlRequest($post_string);
		}
		else{
			// Non-CURL based version...
			$result = $this->nonCurlRequest($post_string);
		}
		// 如果返回值是false，则说明执行过程出现错误或者执行超时
		if($result === false){
			error_log('call api timeout or error occurred.');
			// 如果有必要，抛出异常
			if($this->throwException){
				throw new APICallException(1,
				'execute request error',
				$this->method,$this->server_addr);
			}
			return array('error_code' => 1,
			'error_msg' => 'execute request error');
		}
		// 根据格式将结果解析为array
		if(strtolower($params['format']) == 'json'){
			require_once FRAMEWORK . '/json/JSON.php';
			$svr = new Services_JSON();
			$ret = $svr->decode($ret);
		}else{
			$ret = $this->xml_to_array($result);
		}
		$this->checkReturn($ret);
		return $ret;
	}
	/**
	 * 使用curl发送请求
	 * @param $post_string 请求字符串
	 * @return mixed
	 */
	protected function curlRequest($post_string){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->server_addr);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if($this->timeout > 0){
		  curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
		}
		//curl_setopt($ch, CURLOPT_USERAGENT, 'Facebook API PHP5 Client 1.1 (curl) ' . phpversion());
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
	/**
	 * 非curl格式的request
	 * @param $post_string post请求字符窜
	 * @return mixed
	 */
	protected function nonCurlRequest($post_string){
		$context = array(
				'http' => array(
						'method' => 'POST',
						'header' => 'Content-type: application/x-www-form-urlencoded' . "\r\n" .
						'User-Agent: Facebook API PHP5 Client 1.1 (non-curl) ' . phpversion() . "\r\n" .
						'Content-length: ' . strlen($post_string),
						'content' => $post_string));
		
		$contextid = stream_context_create($context);
		
		$sock = fopen($this->server_addr, 'r', false, $contextid);
		
		if($sock){
			if($this->timeout > 0){
				stream_set_timeout($sock,$this->timeout);
			}
			$result = '';
			while(! feof($sock)){
				$result .= fgets($sock, 4096);
			}
			fclose($sock);
			return $result;
		}
		return false;
	}

	protected static function xml_to_array($xml) {
		if(empty($xml)){
			return array();
		}
		$array = (array) (simplexml_load_string($xml));
		foreach($array as $key => $item){
			if(is_string($item)){
				$array[$key] = $item;
			}
			else{
				$array[$key] = self::struct_to_array($item);
			}
		}
		return $array;
	}

	protected static function struct_to_array($item) {
		if(! is_string($item)){
			$item_as_arr = (array) $item;
			// for CDATA element
			if(empty($item_as_arr))
				return strval($item);
			foreach($item_as_arr as $key => $val){
				$item_as_arr[$key] = self::struct_to_array($val);
			}
			return $item_as_arr;
		}
		return $item;
	}

	/**
	 * 检查返回的结果，如果发生错误，则会写error log
	 * @param $result
	 * @return void
	 */
	protected function checkReturn($result) {
		if($result['error_code']){
			$msg='';
			$msg.='call API error!' . "\n";
			$msg.='Code:'.$result['error_code'] . "\n";
			if($result['error_msg'])	{
				$msg.='Message:'.$result['error_msg'] . "\n";
			}
			error_log($msg);
			// 如果有必要，抛出异常
			if($this->throwException){
				throw new APICallException($result['error_code'],
				$result['error_msg'],
				$this->method,$this->server_addr);
			}
		}
	}
}
/**
 * API调用出错的异常
 * @author shusl
 *
 */
class APICallException extends Exception{
	protected $method = '';
	protected $server = '';
	public function __construct($message,$code,$method = null,$server_addr = null){
		parent::__construct($message,$code);
		$this->method = $method;
		$this->server = $server_addr;
	}
	/**
	 * 设置调用的API方法
	 * @param $method the $method to set
	 */
	public function setAPIMethod($method) {
		$this->method = $method;
	}

	/**
	 * 获取调用的API方法
	 * @return the $method
	 */
	public function getAPIMethod() {
		return $this->method;
	}
	/**
	 * 设置发生错误的API服务器地址
	 * @param $server the $server to set
	 */
	public function setServer($server) {
		$this->server = $server;
	}

	/**
	 * 获取发生错误的API服务器地址
	 * @return the $server
	 */
	public function getServer() {
		return $this->server;
	}

	public function __toString(){
		$msg = 'Api method:' . $this->method . "\n";
		$msg .= 'API Server:' . $this->server . "\n";
		$msg .= parent::__toString();
		return $msg;
	}
}

?>