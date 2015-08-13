<?php
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
class IDSequence{
	const STORAGE_TYPE_DATABASE = "database";
	const STORAGE_TYPE_CACHE = "cache";
	private $table_name;
	private $id_field;
	private $data_exists = false;
	private $step = 1;
	private $key_prefix = 'id_sequence_';
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
	
	public function __construct($table_name,$id_field,$config = null){
		$this->logger = $GLOBALS['framework_logger'];
		$this->setParam($table_name,$id_field);
		$this->setConfig($config);
	}
		
	/**
	 * 设置数据库操作类和缓存操作类
	 * @param array $config
	 * 数据库操作类的key是dbo
	 * 缓存操作类的key是cache
	 */
	public function setConfig($config){
		if(is_array($config) 
			&& isset($config['dbo']) 
			&& isset($config['cache'])){
			if($config['dbo'] instanceof DBHelper ||
				$config['dbo'] instanceof DBHelper2){
				$this->dbhelper = $config['dbo'];
			} else {
				throw new Exception('dbo config error: type must be DBHelper or DBHelper2');
			}
			if($config['cache'] instanceof Cache){
				$this->cache = $config['cache'];
			} else {
				throw new Exception('cache config error: type must be Cache');
			}
			$this->storage_type = "cache";
		} else {
			$appConfigInstance = get_app_config();
			$this->cache = $appConfigInstance->getTableServer("id_sequence")->getCacheInstance();
        	$this->dbhelper = $appConfigInstance->getTableServer("id_sequence")->getDBHelperInstance();
       	 	if (empty($this->cache) || empty($this->dbhelper)) {
       	 		throw new Exception('config error: dbo or cache not set');
       	 	}
       	 	$this->storage_type = $appConfigInstance->getGlobalConfig("id_sequence_storage");
		}
		if (empty($this->storage_type) || 
			!in_array($this->storage_type, 
				array(self::STORAGE_TYPE_CACHE, self::STORAGE_TYPE_DATABASE))) {
			$this->storage_type = self::STORAGE_TYPE_CACHE;
		}
	}
	
	/**
	 * 设置当缓存失效时候，取得值的递增步长，缓存值增加了步长值之后，会提交到数据库。
	 * 该值是为了防止缓存服务器出现问题的容错处理，防止出现重复的id
	 * @param int $step
	 */
	public function setStep($step = 1){
		$step = intval($step);
		if(!empty($step)){
			$this->step = $step;
		}
	}
	
	public function getCurrentId(){
		return $this->initSeq($this->id_field);
	}
	
	public function getNextId(){
		return $this->updateCache();
	}
	
	private function getNextIdFromDb() {
		try {
			$next_id = $this->dbhelper->executeInsert("INSERT INTO ".$this->key_prefix.$this->table_name." VALUES (null)");
		} catch (DBException $e) {
			$next_id = 0;
			$this->logger->writeError("db exception happens while generate sequence id.".$e->getMessage());
		}
		return $next_id;
	}
	
	/**
	 * 递增id的值
	 * @param int $inc 递增的量
	 * @return int 增加后的id值
	 */
	public function increaseId($inc = 1){
		if ($this->storage_type == self::STORAGE_TYPE_CACHE) {
			return $this->updateCache($inc);
		} else {
			$this->throwException("the increase id by $inc is not supprot for database storage type. try to use for iteration.", ELEX_ERR_ID_SEQUENCE_OPERATION_NOT_SUPPORT);
		}
	}
	/**
	 * 初始化一个序列，如果已经初始化，则什么也不做
	 *
	 * @param string $id_field 用于初始化的table中的id字段
	 * @return int
	 */
	public function initSeq($id_field){
		$key = $this->getMemKey();
		$seq = $this->cache->get($key);
		if ($seq === false) {
				$sql = sprintf("select next_value from id_sequence where id_key='%s'",$this->table_name);
				$value = $this->dbhelper->resultFirst($sql);
				if(empty($value)){
					if( $this->data_exists){
						// 如果数据存在，则从数据库中取得最大值
						$sql = sprintf("select ifnull(max(%s),0) max_value from %s",$id_field,$this->table_name);
						$res = $this->dbhelper->getOne($sql);
						$value = $res['max_value'];
					}
					else{
						// 如果没有初始化id sequence表，则设置开始值为0
						$value = 2;
					}
				}
				$this->cache->setMulti(
					array($key => $value,
						  $key . '_next_value' => $value + $this->step),0);
						  
				$sql = "insert into id_sequence (id_key,current_value,next_value) values('%s',%d,%d)
				 on duplicate key update next_value=next_value + %d";
				
				$params =array($this->table_name,$value,$value + $this->step,$this->step);
				$this->dbhelper->execute($sql,$params);
				return  $value;
			}
		return $seq;
	}
	
	public function setParam($table_name,$id_field,$data_exists = false){
		if(empty($table_name)){
			throw new Exception("Table name can't be empty.",1);
		}
		$this->table_name = $table_name;
		$this->id_field = $id_field;
		$this->data_exists = $data_exists;
	}
	
	private function updateCache($inc = 1){
		$key = $this->getMemKey();
		$key_next = $key . '_next_value';
		$this->initSeq($this->id_field);
		$seq = $this->cache->increment($key, $inc);
		$next_value = $this->cache->get($key_next);
		if($seq >= $next_value){
			$this->commitData($seq);
			$this->cache->increment($key_next, $this->step);
		}
		return $seq;
	}
	
	private function commitData($value){
		$sql = "update id_sequence set current_value=%d,next_value=next_value+%d where id_key='%s'";
		$this->dbhelper->execute($sql,
			array($value, $this->step, $this->table_name));
	}
	private function getMemKey(){
		return $this->key_prefix . $this->table_name;
	}
	private function throwException($message,$code){
		throw new GameException($message,$code);
	}
}

?>