<?php
class UserActionLogManager extends ManagerBase{
	private $gameuid = 0;
	private $logger_self =null;
	public function __construct($gameuid) {
		parent::__construct($gameuid);
		$this->gameuid = $gameuid;
		$this->logger_self = LogFactory::getLogger(array(
					'prefix' => "action_log", // 文件名的前缀
					'log_dir' => APP_ROOT.'/log/actionLog/', // 文件所在的目录
					'archive' => ILogger::ARCHIVE_YEAR_MONTH, // 文件存档的方式
					'log_level' => 1
					));
	}
	public function writeLog($action_type,$log) {
		if (empty($log)) return;
		// 只记录金钱变化的action
		if(!empty($log['coin'])&&abs($log['coin'])>=500 
			|| !empty($log['money']) 
			|| (!empty($log['experience']) && intval($log['experience']) > 50)
			|| (!empty($log['coupon']))) {
			$log['action_type'] = $action_type;
			if(empty($log['create_time'])) $log['create_time'] = time();
			$key = $this->getMemkey();
			$logs = $this->getFromCache($key, $this->gameuid);
			if ($logs === false) $logs = array();
			$logs[] = $log;
			if(count($logs) >= 20){
				$this->commitToDB($logs);
			} else{
				$this->setToCache($key, $logs, $this->gameuid);
			}
		}
	}
	
	private function commitToDB($logs = null){
		$key = $this->getMemkey();
		if($logs == null) $logs = $this->getFromCache($key, $this->gameuid);
		if(empty($logs)) return;
		$contents='';
		$fomat="g[%d]a[%d]c[%d]m[%d]e[%d]d[%d]it[%d]t[%d] \r\n";
		foreach ($logs as $k=>$log){
			if ($log['money']==0){
				$contents.=sprintf($fomat,$this->gameuid,$log['action_type'],intval($log['coin']),intval($log['money']),intval($log['experience']),intval($log['coupon']),intval($log['content']),$log['create_time']);
				unset($logs[$k]);
			}
		}
		if (!empty($contents)){
			$this->logger_self->writeError($contents);
		}
		if (!empty($logs)){
			$this->commitToDB2($logs);
		}
		$logs = array();
		$this->setToCache($key, $logs, $this->gameuid);
	}
	private function commitToDB2($logs = null){
		if (empty($logs)) return ;
		// 插入到数据库
		$req = RequestFactory::createInsertRequest(get_app_config($this->gameuid));
		// 不需要缓存数据
		$req->setNoCache(true);
		$req->setKey("gameuid", $this->gameuid);
		$req->setTable($this->getTableName());
		$req->setColumns("gameuid,action_type,coin,money,experience,create_time,content,coupon");
		foreach ($logs as $log){
			$req->addValues(array($this->gameuid, 
						$log['action_type'], 
						$log['coin'], 
						$log['money'], 
						$log['experience'], 
						$log['create_time'],
						intval($log['content']),
						intval($log['coupon'])));
		}
		$req->execute();
	}
	
	private function commitToDB_bak($logs = null){
		$key = $this->getMemkey();
		if($logs == null) $logs = $this->getFromCache($key, $this->gameuid);
		if(empty($logs)) return;
		if (PLATFORM=="test"||PLATFORM=="facebook_tw"){
			$contents='';
			$fomat="g[%d]a[%d]c[%d]m[%d]e[%d]d[%d]it[%d]t[%d] \r\n";
			foreach ($logs as $log){
				$contents.=sprintf($fomat,$this->gameuid,$log['action_type'],intval($log['coin']),intval($log['money']),intval($log['experience']),intval($log['coupon']),intval($log['content']),$log['create_time']);
			}
			if (!empty($contents)){
//				$logger = LogFactory::getLogger(array(
//					'prefix' => "action_log", // 文件名的前缀
//					'log_dir' => APP_ROOT.'/log/', // 文件所在的目录
//					'archive' => ILogger::ARCHIVE_YEAR_MONTH, // 文件存档的方式
//					'log_level' => 1
//					));
				$this->logger_self->writeError($contents);
			}
			$logs = array();
			$this->setToCache($key, $logs, $this->gameuid);
		}else {
			// 插入到数据库
			$req = RequestFactory::createInsertRequest(get_app_config($this->gameuid));
			// 不需要缓存数据
			$req->setNoCache(true);
			$req->setKey("gameuid", $this->gameuid);
			$req->setTable($this->getTableName());
			$req->setColumns("gameuid,action_type,coin,money,experience,create_time,content,coupon");
			foreach ($logs as $log){
				$req->addValues(array($this->gameuid, 
							$log['action_type'], 
							$log['coin'], 
							$log['money'], 
							$log['experience'], 
							$log['create_time'],
							intval($log['content']),
							intval($log['coupon'])));
			}
			$affected_rows = $req->execute();
			if ($affected_rows > 0) {
				// 将缓存清空
				$logs = array();
				$this->setToCache($key, $logs, $this->gameuid);
			}
		}
		
	}
	
	protected function getTableName() {
    	return "user_action_log";
    }
	public function getchangeList(){
		$start= date("Y-m-d",strtotime("-1 day"));
		$start=$start."00:00:00";
		$time=strtotime($start);
		$e_time=date("Y-m-d",strtotime("-1 day"));
		$end_time=$e_time."23:59:59";
		$end_time=strtotime($end_time);
		$dbhelper=$this->getDBHelperInstance();
		$sql="select * from user_action_log where create_time >=$time and create_time <=$end_time";
		$result=$dbhelper->getAll($sql);
		return $result;
	}
	private function getMemkey(){
		return sprintf(CacheKey::CACHE_KEY_USER_ACTION_LOG, $this->gameuid);
	}
}
?>