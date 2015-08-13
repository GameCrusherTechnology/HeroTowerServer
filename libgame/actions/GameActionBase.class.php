<?php
require_once APP_ROOT . '/libgame/GameBase.class.php';
require_once FRAMEWORK . '/action/IAction.class.php';
require_once GAMELIB . '/model/ManagerBase.class.php';
include_once GAMELIB . '/model/UserAccountManager.class.php';
include_once GAMELIB . '/model/UserActionLogManager.class.php';
include_once GAMELIB . '/model/UserEventLogManager.class.php';


abstract class GameActionBase extends GameBase implements IAction {
	private $params = null;

	/**
	 * @var UserAccountManager
	 */
	protected $user_account_mgr = null;
	protected $user_account = null;
	protected $action_def = null;
	protected $action_start_time =0;
	/**
	 * @var PaymentQQBP
	 */
	protected $payment = null;
	
	/**
	 * 日志操作对象
	 *
	 * @var ILogger
	 */
	protected $logger = null;
	protected $action_logger = null;
	public function __construct() {
		$this->action_start_time=microtime(true);
		$this->logger = LogFactory::getLogger(array(
			'prefix' => LogFactory::LOG_MODULE_ACTIONS, // 文件名的前缀
			'log_dir' => APP_ROOT.'/log/', // 文件所在的目录
			'archive' => ILogger::ARCHIVE_YEAR_MONTH, // 文件存档的方式
			'log_level' => get_app_config()->getLogLevel(LogFactory::LOG_MODULE_ACTIONS)
		));
		$this->user_account_mgr = new UserAccountManager();

	}
	/**
	 * @param $params
	 */
	public function execute($params = null) {
		try{
			if($this->isServiceClosed($params['gameuid'])){
				return $this->getReturnArray(GameStatusCode::SYSTEM_MAINTAIN, 'service closed.');
			}
//				return $this->getReturnArray(0,'ok',"test");
			if (isset($params['gameuid'])) {
				$gameuid = intval($params['gameuid']);
				if ($gameuid <= 0) {
					$this->throwException("user[$gameuid] not exists", GameStatusCode::USER_NOT_EXISTS);
				}
				if (isset($params['friend_gameuid'])){
					if (empty($params['friend_gameuid'])||$params['friend_gameuid']<=0){
						$this->throwException("friend user[".$params['friend_gameuid']."] not exist",GameStatusCode::USER_NOT_EXISTS);
					}
				}
				$this->action_logger = new UserActionLogManager($gameuid);
				$this->user_account = $this->user_account_mgr->getUserAccount($gameuid);
				if (empty($this->user_account)) {
					$this->throwException("user[$gameuid] not exists",GameStatusCode::USER_NOT_EXISTS);
				}

//				//是否被禁用
//				if($this->user_account['disabled'] == "1"){
//					return $this->getReturnArray(GameStatusCode::USER_NOT_EXISTS,"user[$gameuid] is disbaled.");
//				}
//				$this->checkSession($gameuid);
			}
			// 对参数做处理
			$this->params = $params;
//			if(!$this->isPerfTestMode($params['gameuid'])){
//				if(!get_app_config()->getGlobalConfig("debug_mode")){
//					$this->checkSignature($params);
//				}
//				$this->validate();
//			}

			$now = time();
			//调用游戏接口
			$result = $this->_exec();



//			// 根据动作的定义，获取用户的经验值和金币的修改值
//			$modify = array_intersect_key($result,array('coin'=>0,'money'=>0,'coupon'=>0,'experience'=>0));
//			if (isset($this->params['action_id'])) {
//		       	if (intval($this->action_def['coin']) != 0) $modify['coin'] += intval($this->action_def['coin']);
//		       	if (intval($this->action_def['experience']) != 0) $modify['experience'] += intval($this->action_def['experience']);
//			}
//
//		    //修改用户的经验值和金币数量
//		    if (count($modify) > 0) {
//		       	$this->user_account_mgr->updateUserStatus($gameuid, $modify);
//		       	$result = array_merge($result, $modify);
//		    }
//
//			//检查action是否要进行计数
//			if (isset($this->params['action_id'])&&in_array($this->params['action_id'], $this->action_record_count)){
//				UserActionCountManager::updateActionCount($this->params['gameuid'],$this->params['action_id']);
//			}
//			//判断是否要写actionlog日志
//			if (!empty($modify) && isset($this->params['action_id'])){
//				$action_log_params=array();
//				$action_log_params=$modify;
//				if (isset($result['action_log_params'])){
//					$action_log_params['content']=$result['action_log_params']['content'];
//					unset($result['action_log_params']);
//				}
//				$this->action_logger->writeLog($this->params['action_id'], $action_log_params);
//			}
			//如果有eventlog，则写log日志，同时将返回值中的相关信息删除
	       	if (isset($result['event_log_params'])&&is_array($result['event_log_params'])) {
	       		$event_log_params = $result['event_log_params'];
	       		$event_gameuid = $event_log_params['gameuid'];
	       		$event_action_id = $event_log_params['action_id'];
	       		$event_params = $event_log_params['params'];
	       		$event_logger = new UserEventLogManager($event_gameuid);
	       		$event_logger->writeLog($event_action_id, $event_params);
	       		unset($result['event_log_params']);
	       	}
	       	return $this->getReturnArray(0,'ok',$result);

		}catch(DBException $e){
			// 数据库类型的错误特殊处理
			$this->logger->writeError("database exception happens while execute sql, error_msg:".$e->getMessage());
			$this->logger->writeError("and the stack trace is as below:\n".$e->getTraceAsString());
			if(get_app_config()->getGlobalConfig("debug_mode")){
				return $this->getReturnArray(GameStatusCode::DATABASE_ERROR, $e->getMessage());
			}else{
				return $this->getReturnArray(GameStatusCode::DATABASE_ERROR, 'database error');
			}
		}catch (GameException $e){
			$this->logger->writeError("game exception happens while execute action:".$e);//log输出过多，关闭之
			return $this->getReturnArray($e->getCode(), $e->getMessage());
		}catch(Exception $e){
			// 其他的错误
			$this->logger->writeError("unknown exception happens while execute action.".$e->getTraceAsString());
			return $this->getReturnArray(GameStatusCode::UNKNOWN_ERROR, $e->getMessage());
		}
		return $this->getReturnArray(GameStatusCode::UNKNOWN_ERROR,'');
	}

	protected function getReturnArray($return_code,$message,$array = null){
		$ret_arr = array('__code' => $return_code, '__msg' => $message,
		'__start_time'=>$this->action_start_time, '__end_time'=>time());
		if(is_array($array)){
			$ret_arr = array_merge($ret_arr,$array);
		}elseif(!empty($array)){
			$ret_arr['result'] = $array;
		}
		return $ret_arr;
	}

	/**
	 * 验证参数的签名是否正确
	 * @param $params
	 * @return void
	 */
	protected function checkSignature($params){
		// 签名的key必须不为空
		if(empty($params['k'])){
			$this->throwException('signature error',GameStatusCode::DATA_ERROR);
		}
		// action_id,need_check_sleep是在happyranch.php中添加的参数，不需要作验证的
		if(isset($params['action_id'])) unset($params['action_id']);
		if (isset($params['need_check_sleep']))	unset($params['need_check_sleep']);
		$sig_key = get_app_config()->getGlobalConfig(AppConfig::SIGNATURE_KEY);
		require_once FRAMEWORK . '/rest/Signature.class.php';
		$sig = Signature::sign($params,$sig_key,'k');
		if($sig != $params['k']){
			$this->throwException("signature error",GameStatusCode::DATA_ERROR);
		}
	}
	/**
	 * 取得映射后的参数数组
	 * @param array $map
	 * @return array
	 */
	protected function getMappedParams(array $map = null){
		if(empty($map)){
			return $this->params;
		}else{
			$params = $this->params;
			foreach($map as $key => $map_key){
				if(array_key_exists($map_key,$params)){
					$params[$key] = $params[$map_key];
					unset($params[$map_key]);
				}else{
					$params[$key] = null;
				}
			}
			return $params;
		}
	}

	/**
	 * 根据参数的名称获取指定类型的参数值
	 * @param $name
	 * @param $type 参数的返回类型，可以是：int，string，array，bool
	 * @return mixed
	 */
	protected function getParam($name, $type = 'int'){
		switch($type){
			case 'int':
			case 'integer':
				$val = intval($this->params[$name]);
				break;
			case 'string':
				$val = isset($this->params[$name])? $this->params[$name] : null;
				break;
			case 'array':
				$val = isset($this->params[$name]) ? $this->params[$name] : array();
				break;
			case 'bool':
				$val = isset($this->params[$name]) ? (bool)$this->params[$name] : false;
				break;
			default:
				$val = isset($this->params[$name]) ? $this->params[$name] : NULL;
		}
		return $val;
	}
	//反外挂
	protected function validate() {
		if(!get_app_config()->getGlobalConfig("debug_mode")){
			$authcode = $this->user_account['secretid'];
			if ($authcode != $this->getParam('authcode')) {
				$this->throwException("authcode error",GameStatusCode::AUTH_CODE_ERROR);
			}
		}
	}
	protected function getTargetGameuid() {
		$target_gameuid = $this->getParam('friend_gameuid');
		if (empty($target_gameuid)) $target_gameuid = $this->getParam("gameuid");
		return $target_gameuid;
	}
	/**
	 * 针对被压缩的字段，在返回给客户端的时候，需要将其还原成字符串
	 * @param 需要还原的数据库记录集(多条数据库记录) $rows
	 * @return 经过连接还原的数据库记录集
	 */
	protected function implodeRows($rows) {
		if (empty($rows)) return array();
		foreach ($rows as $key => $row) {
			$rows[$key] = $this->implodeRow($row);
		}
		return $rows;
	}
	/**
	 * 针对被压缩的字段，在返回给客户端的时候，需要将其还原成字符串
	 * @param 需要还原的数据库记录 $row
	 * @return 经过连接还原的数据库记录
	 */
	protected function implodeRow($row) {
		if (empty($row)) return array();
		foreach ($row as $field=>$value) {
			if (is_array($value)) {
				if (count($value) === 0) $row[$field] = '';
				$row[$field] = implode_string($value);
			}
		}
		return $row;
	}
	abstract protected function _exec();
}
?>
