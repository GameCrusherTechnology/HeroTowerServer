<?php
define("CACHE_KEY_LOG_DATABASE_STORAGE", "ck_system_log");
class LogDatabaseStorage extends LogStorage {
	const LOG_TABLE_NAME = 'log';
	private static $commit_threshold = 100;
	private $module = null;
	public function __construct($options = null){
		$this->module = $options['prefix'];
	}
	/**
	 * @see LogStorage::write()
	 *
	 * @param string $msg 日志内容 
	 */
	public function write($msg) {
		if (strlen($msg) > 3000) $msg = substr($msg, 0, 3000);
		$level = substr($msg, 1, strpos($msg, ']') - 1);
		if (!in_array($level, array('debug','info','error','fatal'))) return;
		global $gameuid;
		if (!isset($gameuid) || intval($gameuid) === 0) $gameuid = 0;
		$cache_helper = get_system_log_cache_helper();
		$logs = $cache_helper->get(CACHE_KEY_LOG_DATABASE_STORAGE);
		if ($logs === false) $logs = array();
		$logs[] = array($level,$gameuid,$this->module,$msg,time());
		if(count($logs) >= self::$commit_threshold) {
			$sql = '';
			foreach ($logs as $log) {
				if (isset($sql[0])) $sql .= ',';
				$sql .= "('".implode("','", array_map("elex_addslashes", $log))."')";
			}
			$sql = sprintf(
				'INSERT INTO %s (%s) values', 
				self::LOG_TABLE_NAME, 
				'level,gameuid,module,msg,create_time').$sql;
			$conn = get_system_log_db_conn();
			@mysql_query($sql, $conn);
			$affected_rows = @mysql_affected_rows($conn);
			if ($affected_rows > 0) {
				// 将缓存清空
				$logs = array();
				$cache_helper->set(CACHE_KEY_LOG_DATABASE_STORAGE, $logs, 0);
			}
			@mysql_close($conn);
		} else{
			$cache_helper->set(CACHE_KEY_LOG_DATABASE_STORAGE, $logs, 0);
		}
	}
}
?>