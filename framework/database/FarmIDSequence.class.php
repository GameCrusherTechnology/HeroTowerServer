<?php
include_once GAMELIB.'/model/UserAccountManager.class.php';
/**
 * 用于生成自增长的id的类。该类依赖于数据库和memcached缓存。
 * @package framework.database
 *
 *  需要的数据库的表结构如下：
 DROP TABLE IF EXISTS `id_sequence`;
 CREATE TABLE  `id_sequence` (
  `id_key` varchar(50) NOT NULL,
  `current_value` int(10) unsigned NOT NULL default '1',
  `next_value` int(10) unsigned NOT NULL default '2',
  PRIMARY KEY  USING BTREE (`id_key`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 */
class FarmIDSequence{
	private $table_name = 'uid_gameuid_mapping';
	private $id_field;
	private $step = 1;
	private $key_prefix = 'farm_idsequence';
	/**
	 * memcache缓存操作类
	 *
	 * @var Cache
	 */
	private $cache;
	/**
	 * 数据库操作类
	 *
	 * @var DBHelper2
	 */
	private $dbhelper;
	/**
	 * 日志操作类
	 *
	 * @var ILogger
	 */
	private $logger = null;
	private $storage_type = null;
	
	public function __construct(){
		$this->logger = $GLOBALS['framework_logger'];
		$this->setConfig();
	}
	
	public function getCurrentId(){
		return $this->initSeq();
	}
	
	public function creatId(){
		$nextId =  $this->getCurrentId() +1;
		$this->setNewId($nextId );
		return $nextId;
	}
	public function getNextId(){
		return $this->getCurrentId() +1;
	}
	
	
	/**
	 * 设置数据库操作类和缓存操作类
	 * @param array $config
	 * 数据库操作类的key是dbo
	 * 缓存操作类的key是cache
	 */
	private function setConfig(){
		$appConfigInstance = get_app_config();
		$this->cache = $appConfigInstance->getTableServer($this->table_name)->getCacheInstance();
        $this->dbhelper = $appConfigInstance->getTableServer($this->table_name)->getDBHelperInstance();
        if (empty($this->cache) || empty($this->dbhelper)) {
        	throw new Exception('config error: dbo or cache not set');
        }
	}
	
	
	/**
	 *
	 * @return int
	 */
	private function initSeq(){
		$key = $this->getMemKey();
		$seq = $this->cache->get($key);
		if ($seq === FALSE) {
			$sql = sprintf("select LAST_INSERT_ID() from %s",$this->table_name);
			$value = $this->dbhelper->resultFirst($sql);
			if (!empty($value)){
				$seq = $value['gameuid'];
			}
		}
		if ($seq != FALSE && $seq >= 1){
			$user_mgr = new UserAccountManager();
			$account = $user_mgr->getUserAccount($seq);
			$nexAccount = $user_mgr->getUserAccount($seq);
		}
		if (empty($account) || !empty($nexAccount) ){
			$sql = sprintf("select ifnull(max(%s),0) max_value from %s","gameuid",$this->table_name);
			$res = $this->dbhelper->getOne($sql);
			$seq = $res['max_value'];
		}
		return $seq;
	}
	
	private function setNewId($id)
	{
		$key = $this->getMemKey();
		$this->cache->set($key,$id,0);
	}
	
	private function getMemKey(){
		return $this->key_prefix;
	}
	private function throwException($message,$code){
		throw new GameException($message,$code);
	}
}

?>