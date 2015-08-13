<?php
if (!defined('APP_ROOT')) define('APP_ROOT',realpath(dirname(__FILE__) . '/../../'));
if (!defined('FRAMEWORK')) define('FRAMEWORK', APP_ROOT . '/framework');
if (!defined('GAMELIB')) define('GAMELIB', APP_ROOT . '/libgame');

require_once GAMELIB . '/config/GameConfig.class.php';
require_once FRAMEWORK . '/log/LogFactory.class.php';
require_once FRAMEWORK . '/db/RequestFactory.class.php';
require_once GAMELIB . '/GameConstants.php';

require_once APP_ROOT . '/libgame/actions/GameActionBase.class.php';
require_once FRAMEWORK . '/action/ActionInvoker.class.php';
require_once FRAMEWORK . '/action/ActionLocker.class.php';

date_default_timezone_set(get_app_config()->getTimeZone());

class API_Base{
	/**
	* @var ActionLocker
	*/
	protected $action_locker = null;
	protected $logger = null;
	protected function setLocker($gameuid,$action,$lock_time = 60){
		$this->action_locker =  new ActionLocker(get_app_config(),$action,$gameuid);
		$this->action_locker->setLocker($lock_time);
	}
	protected function releaseLocker(){
		if($this->action_locker instanceof ActionLocker){
			$this->action_locker->releaseLocker();
			$this->action_locker = null;
		}
	}
	public function __construct() {
		$this->logger = LogFactory::getLogger(array(
			'prefix' => LogFactory::LOG_MODULE_AMF_ENTRY, // 文件名的前缀
			'log_dir' => APP_ROOT.'/log/', // 文件所在的目录
			'archive' => ILogger::ARCHIVE_YEAR_MONTH, // 文件存档的方式
			'log_level' => get_app_config()->getLogLevel(LogFactory::LOG_MODULE_AMF_ENTRY)
		));
	}
	public function __destruct(){
		$this->releaseLocker();
	}
	protected function nonlockInvoke($amf_entry,$module,$action,$data){
		try{
			$start = microtime(true);
			$GLOBALS['uid'] = $data['uid'];
			$GLOBALS['gameuid'] = intval($data['gameuid']);
			
			if ($this->logger->isDebugEnabled()) {
				$this->logger->writeDebug($this->get_log_msg($data, "$module.$action", false));
			}
			
			// 执行动作
			$invoker = new ActionInvoker(APP_ROOT . '/libgame/actions/');
			$result = $invoker->invoke($module,$action,$data);
			if ($this->logger->isDebugEnabled()) {
				$this->logger->writeDebug($this->get_log_msg($result, "$module.$action", true));
			}
			if (get_app_config()->isPerfTestMode()) {
				$this->record_action($amf_entry, $start);
			}
		}catch(ConfigException $e){
			error_log($e->getMessage() . "\n");
			return array('__code' => $e->getCode(), '__msg' => $e->getMessage());
		}catch(Exception $e){
			if(get_app_config()->isDebugMode()){
				return array('__code' => $e->getCode(),
				'__msg' => $e->getMessage());
			}else{
				error_log($e->getMessage() . "\n");
				return array('__code' => 1, '__msg' => 'Unknown Error');
			}
		}
		return $result;
	}

	protected function invoke($amf_entry,$module,$action,$data,$lock_time = 60){
		try{
			$start = microtime(true);
			$GLOBALS['uid'] = $data['uid'];
			$GLOBALS['gameuid'] = intval($data['gameuid']);
			
			if ($this->logger->isDebugEnabled()) {
				$this->logger->writeDebug($this->get_log_msg($data, "$module.$action", false));
			}
			$gameuid = intval($data['gameuid']);
			//锁定该动作
			$this->setLocker($gameuid,$action,$lock_time);
			// 执行动作
			$invoker = new ActionInvoker(APP_ROOT . '/libgame/actions/');
			$result = $invoker->invoke($module,$action,$data);
			if ($this->logger->isDebugEnabled()) {
				$this->logger->writeDebug($this->get_log_msg($result, "$module.$action", true));
			}
			// 释放锁
			$this->releaseLocker();
			if (get_app_config()->isPerfTestMode()) {
				$this->record_action($amf_entry, $start);
			}
		}catch(ConfigException $e){
			$this->releaseLocker();
			error_log($e->getMessage() . "\n");
			return array('__code' => $e->getCode(), '__msg' => $e->getMessage());
		}catch(ActionException $e){
			$this->releaseLocker();
			error_log($e->getMessage() . "\n");
			return array('__code' => $e->getCode(), '__msg' => $e->getMessage());
		}catch(Exception $e){
			$this->releaseLocker();
			if(get_app_config()->isDebugMode()){
				return array('__code' => $e->getCode(),
				'__msg' => $e->getMessage());
			}else{
				error_log($e->getMessage() . "\n");
				return array('__code' => 1, '__msg' => 'Unknown Error');
			}
		}
		return $result;
	}
	
	private function get_log_msg($var, $method = null, $returned = false) {
		include_once FRAMEWORK .'/json/JSON.php';
    	$json = new Services_JSON();
    	$msg = '';
    	if(isset($method)){
    		if ($returned) {
    			$msg .= "$method returned result:";
    		} else {
    			$msg .= "call $method with params:";
    		}
    	}
    	$msg .= print_r($var,true) . "and in json:\n";
    	$msg .= $json->encode($var) . "\n";
    	return $msg;
	}
	
	private function record_action($amf_entry, $start) {
		$gameuid = $GLOBALS['gameuid'];
		$now = microtime(true);
		$app_config = get_app_config();
		$cache_helper = $app_config->getDefaultCacheInstance();
		
		$interval = intval(($now - $start)*1000);
		$statistic_key = $gameuid."_".$amf_entry;
		$micro_start_as_str = strval($start*1000).'.';
		$cache_helper->set(
			$statistic_key, 
			array(
				'gameuid'=>$gameuid,
				'action'=>$amf_entry, 
				'start'=>substr($micro_start_as_str, 0, strpos($micro_start_as_str, '.')),
				'interval'=>$interval), 
			0);
	}
}