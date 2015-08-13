<?php

class TableServer {
	const WORKLOAD_MASTER = 'master';
	const WORKLOAD_SLAVE = 'slave';
	
	private $db_dsn;
	private $slave_dsn;
	private $cache_server;
	private $cache_super_server = null;
	private $table_name;
	private $workload;
	private $primary = null;
	private $fields = '';
	private $cache_type = 1;
	private $cache_expire_time = 3600;
	private $commit_threshold = 0;
	private $commit_threshold_expire = 1800;
	private $pack_fields = array();
	private $cache_helper = null;
	private $db_helper = null;
	private $read_db_helper = null;
	
	/**
	 * 该表是否只读
	 * @var bool
	 */
	private $readonly = false;
	
	public function __construct($db_dsn,$cache_server,$table_name,
	$slave = null,$cache_super_server = null){
		$this->db_dsn = $db_dsn;
		$this->setCacheServer($cache_server);
		$this->table_name = $table_name;
		$this->slave_dsn = $slave;
		$this->workload = self::WORKLOAD_MASTER;
		$this->setCacheSuperServer($cache_super_server);
	}
	/**
	 * @param $cache_type the $cache_type to set
	 */
	public function setCacheType($cache_type) {
		switch($cache_type){
			// TCRequest:CACHE_PRIMARY_KEY
			case 1:
			// TCRequest:CACHE_KEY_LIST
			case 2:
			// TCRequest:CACHE_FIELD_ASSOC
			case 3:
			// TCRequest:CACHE_CUSTOM_KEY
			case 4:
				$this->cache_type = $cache_type;
				break;
			default:
				$this->cache_type = 1;
		}
		
	}

	/**
	 * @return the $cache_type
	 */
	public function getCacheType() {
		return $this->cache_type;
	}

	/**
	 * 设置表的主键，如果多个主键，使用逗号分隔
	 * @param $primary the $primary to set
	 */
	public function setPrimary($primary) {
		if(isset($primary[0])){
			$primary_keys = explode(',',$primary);
			$this->primary = array_flip($primary_keys);
		}
	}

	/**
	 * @return the $primary
	 */
	public function getPrimary() {
		return $this->primary;
	}
	/**
	 * @param $fields the $fields to set
	 */
	public function setFields($fields) {
		if(!empty($fields)){
			if(is_array($fields)){
				$this->fields = $fields;
			}else{
				$this->fields = explode(',',$fields);
			}
		}
	}

	/**
	 * @return the $fields
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 * @param $readonly the $readonly to set
	 */
	public function setReadonly($readonly) {
		$this->readonly = (bool)$readonly;
	}

	/**
	 * @return the $readonly
	 */
	public function getReadonly() {
		return $this->readonly;
	}

	/**
	 * 设置迁移模式时候缓存的源缓存服务器
	 * @param $cache_server 原来的缓存服务器组
	 * @return void
	 */
	private function setCacheSuperServer($cache_server){
		if(empty($cache_server)){
			return;
		}
		if(is_array($cache_server)){
			$this->cache_super_server = $cache_server;
		}elseif (strpos($cache_server,',') !== false){
			$this->cache_super_server = explode(',',$cache_server);
		}else{
			$this->cache_super_server = $cache_server;
		}
	}

	/**
	 * @param $cache_server the $cache_server to set
	 * @example
	 * $sever-> setCacheServer('localhost,localhost:11212');
	 * or
	 * $server->setCacheServer(array('localhost','localhost:11212'));
	 */
	private function setCacheServer($cache_server) {
		if(is_array($cache_server)){
			$this->cache_server = $cache_server;
		} elseif (!empty($cache_server)){
			if( strpos($cache_server,',') !== false){
				$this->cache_server = explode(',',$cache_server);
			}else{
				$this->cache_server = $cache_server;
			}
		}else{
			throw new ConfigException('cache server error');
		}
		
	}
	
	/**
	 * 获取操作缓存的Cache实例
	 * @return Cache
	 */
	public function getCacheInstance(){
		if (isset($this->cache_helper)) return $this->cache_helper;
		$this->cache_helper = new Cache($this->cache_server);
		// 如果该缓存是处在扩容或者迁移模式，则设置该缓存的源缓存服务器
		if(!empty($this->cache_super_server)){
			$super_cache = new Cache($this->cache_super_server);
			$this->cache_helper->setSuperServer($super_cache);
		}
		return $this->cache_helper;
	}
	/**
	 * 获取操作该表的数据库对象实例
	 * @return DBHelper2
	 */
	public function getDBHelperInstance(){
		if (isset($this->db_helper)) return $this->db_helper;
		require_once FRAMEWORK . '/database/DBHelper2.class.php';
		$this->db_helper = new DBHelper2($this->db_dsn, $this->table_name);
		return $this->db_helper;
	}
	
	/**
	 * 获取该表读取操作的数据库对象实例
	 * @return DBHelper2
	 */
	public function getDBReadHelperInstance(){
		if (isset($this->read_db_helper)) return $this->read_db_helper;
		
		require_once FRAMEWORK . '/database/DBHelper2.class.php';
		$dsn = $this->db_dsn;
		if($this->workload == self::WORKLOAD_SLAVE && !empty($this->slave_dsn)){
			$dsn = $this->slave_dsn;
		}
		$this->read_db_helper = new DBHelper2($dsn, $this->table_name);
		return $this->read_db_helper;
	}

	/**
	 * @return the $table_name
	 */
	public function getTableName() {
		return $this->table_name;
	}

	/**
	 * @return the $cache_server
	 */
	public function getCacheServer() {
		return $this->cache_server;
	}

	/**
	 * @return the $db_dsn
	 */
	public function getDbDsn() {
		return $this->db_dsn;
	}

	/**
	 * @return the $slave_dsn
	 */
	public function getSlaveDsn() {
		return $this->slave_dsn;
	}
	/**
	 * @param $workload the $workload to set
	 */
	public function setWorkload($workload) {
		switch ($workload){
			case self::WORKLOAD_MASTER:
				$this->workload = self::WORKLOAD_MASTER;
				break;
			case self::WORKLOAD_SLAVE:
				$this->workload = self::WORKLOAD_SLAVE;
				break;
			default:
				$this->workload = self::WORKLOAD_MASTER;
				break;
		}
	}

	/**
	 * @return the $workload
	 */
	public function getWorkload() {
		return $this->workload;
	}
	
	/**
	 * 设置表数据的过期时间
	 *
	 * @param int $cache_expire_time 表数据的缓存时间
	 */
	public function setCacheExpireTime($cache_expire_time) {
		$this->cache_expire_time = $cache_expire_time;
	}
	
	/**
	 * 获取表的缓存时间
	 * @return int 该表的缓存时间，单位为秒
	 */
	public function getCacheExpireTime() {
		return $this->cache_expire_time;
	}
	
	/**
	 * 设置缓存提交的临界值，超过这个临界值则会同步到数据库
	 *
	 * @param 需要设置的临界值 $commit_threshold
	 */
	public function setCommitThreshold($commit_threshold = 0) {
		$this->commit_threshold = $commit_threshold;
	}
	
	/**
	 * 获取缓存提交的临界值，超过这个临界值则会同步到数据库
	 *
	 * @return 缓存提交临界值
	 */
	public function getCommitThreshold() {
		return $this->commit_threshold;
	}
	
	/**
	 * 设置缓存提交的临界值的缓存时间，单位为秒.
	 * 缓存提交临界值是保存在缓存里边的，当该缓存失效之后，也是需要提交到数据库的.
	 * 这样做的目的是避免用户数据长时间不同步，造成数据丢失.
	 *
	 * @param 需要设置的临界值缓存时间 $commit_threshold_expire
	 */
	public function setCommitThresholdExpire($commit_threshold_expire = 1800) {
		$this->commit_threshold_expire = $commit_threshold_expire;
	}
	
	/**
	 * 获取缓存提交的临界值的缓存时间，单位为秒.
	 *
	 * @return 缓存提交临界值缓存时间
	 */
	public function getCommitThresholdExpire() {
		return $this->commit_threshold_expire;
	}
	/**
	 * 设置需要进行压缩存储的字段
	 * 
	 * @param 需要进行压缩的字段，为一个字符串数组 $pack_fields
	 */
	public function setPackFields($pack_fields) {
		if (empty($pack_fields)) return;
		$this->pack_fields = $pack_fields;
	}
	/**
	 * 获取需要进行压缩存储的字段
	 * 
	 * @return 需要进行压缩的字段，为一个字符串数组
	 */
	public function getPackFields() {
		return $this->pack_fields;
	}
}

?>