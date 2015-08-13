<?php
require_once FRAMEWORK . '/common.func.php';
require_once FRAMEWORK . '/rest/rest_error.php';
require_once FRAMEWORK . '/rest/RestServiceActionBase.class.php';

class RestService {
	/**
	 * 返回的模式，json或者xml
	 * @var string
	 */
	protected $format = 'json';
	
	/**
	 * 接口的版本
	 * @var string
	 */
	protected $version = '1.0';
	
	/**
	 * 应用的加密字符串
	 * @var string
	 */
	protected $appkey;

	/**
	 * @return string
	 */
	public function getAppkey() {
		return $this->appkey;
	}

	/**
	 * @param string $appkey
	 */
	public function setAppkey($appkey) {
		if(empty($appkey)){
			trigger_error('appkey can not be empty',E_ERROR);
		}
		$this->appkey = $appkey;
	}
	
	/**
	 * @param string $format
	 */
	protected function setFormat($format) {
		if(!empty($format)){
			$format = strtolower($format);
			if($format == 'json' || $format == 'xml'){
				$this->format = $format;
			}
		}
	}

	/**
	 * 是否运行与debug模式。
	 * @var bool
	 */
	protected $debug = false;
	/**
	 * @var string
	 */
	protected $actionPath = './actions';

	/**
	 * @return bool
	 */
	public function getDebug() {
		return $this->debug;
	}

	/**
	 * @param bool $debug
	 */
	public function setDebug($debug) {
		$this->debug = $debug;
	}
	/**
	 * 返回动作类的路径
	 * @return the $actionPath
	 */
	public function getActionPath() {
		return $this->actionPath;
	}

	/**
	 * 设置动作类的路径,设置成功，返回原来的路径，否则返回false
	 * @param $actionPath the $actionPath to set
	 * @return string
	 */
	public function setActionPath($actionPath) {
		if(is_dir($actionPath)){
			$prev_path = $this->actionPath;
			$this->actionPath = realpath($actionPath . '/');
			return $prev_path;
		}
		return false;
	}

	/**
	 * API的主函数
	 *
	 */
 	public function service(){
 		// 验证必要的参数
 		$this->validate();
 		
 		$format = getGPC('format','string');
 		$this->setFormat($format);
 		require_once FRAMEWORK . '/action/ActionInvoker.class.php';
 		try{
	 		$invoker = new ActionInvoker($this->actionPath);
	 		$params = elex_addslashes($_REQUEST);
	 		$method = getGPC('method','string');
	 		$pos = strpos($method,'.');
			if($pos === false){
				$module = '';
				$action = $method;
			}else{
				$module = substr($method,0,$pos);
				$action = substr($method,$pos + 1);
			}
	 		$result = $invoker->invoke($module,$action,$params);
	 		echo $this->getReturnResult($result,$this->format);
 		}catch (Exception $e){
 			$this->errorMessage($e->getCode(),$e->getMessage());
 		}
 	}
 	/**
 	 * 验证rest服务必须的参数
 	 */
 	protected function validate(){
 		$params = &$_REQUEST;
 		if(!isset($params['sig'],
	 		$params['timestamp'],
	 		$params['method'],
	 		$params['sig_appkey'])){
 			$this->errorMessage(ELEX_API_CODE_PARAMETER_ERROR,'request parameters error.');
 		}
 		$request_time = $_SERVER['REQUEST_TIME'];
 		if(empty($request_time)){
 			$request_time = time();
 		}
 		// 验证时间戳
 		$timestamp = getGPC('timestamp','int');
 		if(abs($timestamp - $request_time) > 30){
 			$this->errorMessage(ELEX_API_CODE_PARAMETER_ERROR,'timestamp error');
 		}
 		// 验证签名
		require_once FRAMEWORK . '/rest/Signature.class.php';
		$sign = Signature::sign($params,API_SIG_KEY);
		$sig_request = $params['sig'];
		if($sign != $sig_request){
			$this->errorMessage(ELEX_API_CODE_SIGNATURE_ERROR,'signature error.');
		}
 	}
 	/**
 	 * 取得返回的字符串。
 	 * @param mixed $result
 	 * @param string $type 返回的格式，现在支持json和xml
 	 * @return string
 	 */
 	protected function getReturnResult($result,$type = 'json'){
 		if(empty($result)){
 			return '';
 		}
 		if($type == 'json'){
	 		require_once FRAMEWORK . '/json/JSON.php';
	 		$serv = new Services_JSON();
	 		return $serv->encode($result);
 		}else{
 			return '';
 		}
 		
 	}
 	/**
 	 * api接口返回错误，并且退出程序。
 	 * @param int $code
 	 * @param string $message
 	 */
 	protected function errorMessage($code,$message,$type = 'json'){
 		$result = array('code' => $code,'message' => $message);
 		echo $this->getReturnResult($result,$type);
 		exit(1);
 	}
}

?>