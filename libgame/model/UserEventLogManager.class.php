<?php
define ('KEY_CURRENT_EVENT_LOG','logs');
define ('KEY_TOBE_COMMIT_LOG','cmt');
define ('KEY_HISTORY_EVENT_LOG','his');
define ('KEY_LAST_MERGE_TIME','lmt');
define ('KEY_LAST_SET_HISTORY_TIME','lct');
class UserEventLogManager extends ManagerBase {
	private $log = null;
	private $gameuid = 0;
	private $log_formatter =null;
	private static $merge_action_interval = 300;
	// 将买卖操作提交到数据库的缓存阈值，超过这个缓存的条数，就会提交到数据库
	private static $commit_entry_threshold = 100;
	// 在缓存中保存的event log记录条数
	private static $entries_in_history_cache = 20;
	private $logger_self=null;
	public function __construct($gameuid) {
		parent::__construct();
		$this->gameuid = $gameuid;
		$this->log = $this->getFromCache($this->getMemKey(), $this->gameuid);
		if ($this->log === false) $this->log = array();
		$this->log_formatter = new EventLogFormatter();
		$this->logger_self = LogFactory::getLogger(array(
					'prefix' => "event_log", // 文件名的前缀
					'log_dir' => APP_ROOT.'/log/eventLog/', // 文件所在的目录
					'archive' => ILogger::ARCHIVE_YEAR_MONTH, // 文件存档的方式
					'log_level' => 1
					));
	}
	
	public function getEventLog($limit) {
		if (!isset($limit)) $limit = self::$entries_in_history_cache;
		$event_logs = $this->getAllEventLog();
		
		if(count($event_logs) > $limit){
			$this->sortEventLog($event_logs);
			return array_slice($event_logs,0,$limit);
		}
		return $event_logs;
	}
	/*
	 * 写事件日志，主要记录的是玩家的物品的变化
	 * @para action:动作的action_id，需要进行常量定义,如果需要记录物品的变化，则要在这里的switch中进行定义
	 * @para params:主要是该动作要进行记录的内容
	 * 如果是记录物品的变化，结构必须是array(array(item_id=>123,count=>123))
	 */
	public function writeLog($action, $params = null) {
		$this->autoCommit();
		$this->writeEventLog($action, $params);
		$this->setToCache($this->getMemKey(), $this->log, $this->gameuid);
	}
	
	public function getEventLogFromDB($limit = 30){
		$req = RequestFactory::createGetRequest(get_app_config($this->gameuid));
		$req->setKey('gameuid',$this->gameuid);
		$req->setTable($this->getTableName());
		$req->setColumns("action,content, create_time");
		$req->addKeyValue('gameuid',$this->gameuid);
		$req->setExtraCondition("order by create_time desc");
		$req->setLimit($limit);
		$req->setNoCache(true);
		$list = $req->execute();
		return $list;
	}
	
	public function clearUserEventLog(){
		$req = RequestFactory::createDeleteRequest(get_app_config($this->gameuid));
		$req->setKey('gameuid',$this->gameuid);
		$req->setTable($this->getTableName());
		$req->addKeyValue('gameuid',$this->gameuid);
		return $req->execute();
		
	}
	
	public function getActionMessage($action){
		static $action_log_map = null;
		if(is_null($action_log_map)){
			$action_log_map = $this->getLanguage();
		}
		if(isset($action_log_map[$action])){
			return $action_log_map[$action];
		}
		else{
			return null;
		}
	}

	private function writeFriendEventLog($action, $param) {
		$uid = $param;
		$name = '';
		if(is_array($param)) {
			$uid = $param[0];
			$name = $param[1];
		}
		$now = time();
		if(!isset($this->log[KEY_CURRENT_EVENT_LOG][$action][$uid])){
			$this->log[KEY_CURRENT_EVENT_LOG][$action][$uid] = array('time' => $now, 'name' => $name);
		}
		$this->log[KEY_LAST_MERGE_TIME] = $now;
	}

	private function writeEventLog($action, $param = null) {
		if(is_array($param) && isset($param['time'])){
			$time = $param['time'];
			unset($param['time']);
		} else {
			$time = time();
		}
		if(empty($param)){
			$this->log[KEY_CURRENT_EVENT_LOG][$action][] = array('time' => $time);
		} else {
			$this->log[KEY_CURRENT_EVENT_LOG][$action][] = array('time' => $time, 'params' => $param);
		}
	}
	private function writeUseItemEvent($action,$items) {
		//注意这里的items必须是多重数组
		if(count($items) < 1) return;
		$now = time();
		// 判断是否已经有相同的日志
		if(! isset($this->log[KEY_CURRENT_EVENT_LOG][$action])) {
			$action_log = &$this->log[KEY_CURRENT_EVENT_LOG][$action];
			$action_log = array('time' => $now);
		}
		else{
			$action_log = &$this->log[KEY_CURRENT_EVENT_LOG][$action];
		}
		// 写卖出的item和数目
		foreach($items as $item){
			$item_id = $item['item_id'];
			$item_count = isset($item["item_count"])?$item["item_count"]:$item['count'];
			if($item_count == 0) continue;
			$action_log[$item_id] += $item_count;
		}
		$this->log[KEY_LAST_MERGE_TIME] = $now;
	}


	public function autoCommit($force = false) {
		$now = time();
		$last_commit_time = isset($this->log[KEY_LAST_SET_HISTORY_TIME]) ? $this->log[KEY_LAST_SET_HISTORY_TIME] : 0;
		$last_merge_action =  isset($this->log[KEY_LAST_MERGE_TIME]) ? $this->log[KEY_LAST_MERGE_TIME] : 0;
		// 如果要求强制提交，那么提交缓存
		if($force) {
			$this->setToHistory();
		} elseif($now - $last_commit_time > self::$merge_action_interval 
					&& $now - $last_merge_action > self::$merge_action_interval){
			// 如果上次提交到现在已经超过3分钟，并且3分钟内没有可以合并的动作，那么提交缓存到历史缓存
			$this->setToHistory();
		}
	}
	
	/**
	 * 将current log转移到history log里边去.
	 * current_log仅仅是用于合并操作用的
	 */
	private function setToHistory() {
		$log_in_cache = $this->log[KEY_CURRENT_EVENT_LOG];
		if(empty($log_in_cache)) return;
		
		$event_log = $this->getAllEventLog();
		
		// history log日志控制在self::$entries_in_history_cache条以内
		if(count($event_log) > self::$entries_in_history_cache){
			$this->sortEventLog($event_log);
			$spliced = array_splice($event_log,self::$entries_in_history_cache);
			// 设置需要提交的event
			$this->setToCommit($spliced);
		}
		$this->log[KEY_CURRENT_EVENT_LOG] = array();
		$this->log[KEY_HISTORY_EVENT_LOG] = $event_log;
		$this->log[KEY_LAST_SET_HISTORY_TIME] = time();
		$this->setToCache($this->getMemKey(), $this->log, $this->gameuid);
	}
	
	private function setToCommit($spliced_logs){
		$log_to_commit =array();
		foreach ($spliced_logs as $event) {
				$log_to_commit[] = $event;
		}
		if(!empty($log_to_commit)){
			if(!is_array($this->log[KEY_TOBE_COMMIT_LOG])){
				$this->log[KEY_TOBE_COMMIT_LOG] = $log_to_commit;
			}else{
				$this->log[KEY_TOBE_COMMIT_LOG] = array_merge($this->log[KEY_TOBE_COMMIT_LOG],$log_to_commit);
				// 将买卖操作插入到数据库
				if(count($this->log[KEY_TOBE_COMMIT_LOG]) > self::$commit_entry_threshold){
//					$req = RequestFactory::createInsertRequest(get_app_config($this->gameuid));
//					// 不需要缓存数据
//					$req->setNoCache(true);
//					$req->setKey("gameuid", $this->gameuid);
//					$req->setTable($this->getTableName());
//					$req->setColumns("gameuid,content,action,create_time");
//					foreach ($this->log[KEY_TOBE_COMMIT_LOG] as $log) {
//						$req->addValues(array($this->gameuid, $log['content'], $log['action'], $log['create_time']));
//					}
//					$req->execute();
					$contents='';
					$fomat="%d+%s+%d+%d \r\n";
					foreach ($this->log[KEY_TOBE_COMMIT_LOG] as $log){
						$contents.=sprintf($fomat,$this->gameuid, $log['content'], $log['action'], $log['create_time']);
					}
					if (!empty($contents)){
						$this->logger_self->writeError($contents);
					}
					$this->log[KEY_TOBE_COMMIT_LOG] = array();
					$this->setToCache($this->getMemKey(), $this->log, $this->gameuid);
				}
			}
		}
	}
	
	/**
	 * 根据时间来对事件进行排序
	 * @param array $logs 需要排序的日志
	 */
	private function sortEventLog(&$logs){
		$times = array();
		foreach ($logs as $index => $log) {
			$times[$index] = $log['create_time'];
		}
		array_multisort($times,SORT_DESC,$logs);
	}
	
	private function getAllEventLog(){
		$curr_log = $this->getCurrentLogs();
		$his_log = $this->log[KEY_HISTORY_EVENT_LOG];
		if(empty($his_log)) return $curr_log;
		return array_merge($curr_log,$his_log);
	}
	
	private function getCurrentLogs(){
		if(empty($this->log[KEY_CURRENT_EVENT_LOG])) return array();
		$event_log = array();
		foreach($this->log[KEY_CURRENT_EVENT_LOG] as $action => $events){
			$action_log = $this->log_formatter->format($action,$events);
			if(!empty($action_log)){
				$event_log = array_merge($event_log, $action_log);
			}
		}
		return $event_log;
	}
	
	private function getMemKey() {
		return sprintf(CacheKey::CACHE_KEY_USER_EVENT_LOG, $this->gameuid);
	}
	
	protected function getTableName() {
		return 'user_event_log';
	}
}

class EventLogFormatter {
	public function format($action, $events){
		$action_log = $this->formatOtherEvents($action, $events);
		return $action_log;
	}
	
	/**
	 * 从缓存中取得与好友相关的事件，包括除草，除虫，浇水等
	 * @return array
	 */
	protected function formatFriendEvent($action, $events) {
		if(empty($events)) return '';
		$log = array();
		foreach($events as $uid => $event){
			if(isset($event['params']) || empty($uid)){
				continue;
			}
			if(empty($event['name']) ){
				$msg = "{$uid}|";
			}
			else{
				$msg = sprintf("%s|%s|",$uid,$event['name']);
			}
			$log[] = array('action' => $action, 'content' => $msg, 'create_time' => $event['time']);
		}
		return $log;
	}

	/*
	 * 从缓存中取得特定事件之外的事件
	 * @return array
	 */
	protected function formatOtherEvents($action, $events) {
		$log = array();
		if(! is_array($events)){
			$log[] = $this->formatOtherEvent($action, $events);
		}
		else{
			foreach($events as $event){
				$log[] = $this->formatOtherEvent($action, $event);
			}
		}
		return $log;
	}
	
	/*
	 * 从缓存中取得一件特定事件之外的事件
	 * @return array
	 */
	protected function formatOtherEvent($action, $event) {
		if($action == ACTION_LITERAL_EVENT){
			$msg = addslashes($event['params']);
		}
		elseif(empty($event['params'])){
			$msg = '';
		}
		elseif(is_array($event['params'])){
			$msg = '';
			if(! empty($event['params'][0])){
				$msg = $event['params'][0] . '|';
			}
		}
		else{
			$msg = $event['params'] . '|';
		}
		return array('action' => $action, 'content' => $msg, 'create_time' => $event['time']);
	}
	
	/**
	 * 从缓存中取得与item操作想关的事件，比如买卖物品等
	 * @return array
	 */
	protected function formatItemEvent($action, $events){
		$time = $events['time'];
		unset($events['time']);
		$msg = $this->formatItemMsg($events);
		if(! empty($msg)){
			return array(array('action' => $action, 'content' => $msg, 'create_time' => $time));
		}
		else{
			return false;
		}
	}
	
	private function formatItemMsg($items) {
		$item_msg = '';
		foreach($items as $item_id => $item_count){
			if( $item_count > 0 && $item_id != 'name'){
				$item_msg .= "$item_id:$item_count,";
			}
		}
		return rtrim($item_msg, ',');
	}
	
}
?>