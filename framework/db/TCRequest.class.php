<?php
class RequestException extends Exception{}

abstract class TCRequest {
	const CACHE_PRIMARY_KEY = 1;
	const CACHE_KEY_LIST = 2;
	const CACHE_FIELD_ASSOC = 3;
	const CACHE_CUSTOM_KEY = 4;
	// 针对in这样的查询需要特殊处理
	const CACHE_IN_LIST = 5;
	
	/**
	 * 应用程序的配置
	 * @var AppConfig
	 */
	protected $app_config = null;
	
	/**
	 * 日志操作类
	 *
	 * @var ILogger
	 */
	protected $logger = null;
	
	protected $cache_type = 1;
	
	protected $assoc_field = '';
	
	protected $mem_key = '';
	
	protected $limit_offset = 0;
	protected $limit_count = 0;
	
	protected $columns = null;
	protected $commit_fields = null;
		
	protected $no_cache = false;
	protected $no_db = false;
	protected $commit_threshold = 0;
	protected $commit_threshold_expire = 1800;
	protected $keys = array();
	protected $values = array();
	protected $primary_key = array();
	protected $fields_def = array();
	
	protected $key = '';
	protected $key_value;
	
	/**
	 * 用于构造list_cache_key的数组，目前的方式是通过key_value和keys来构造，是一种猜测的被动的方式，应该有开发者自行指定！
	 */
	protected $list_cache_key = null;
	
	protected $insert_id = null;
	protected $affected_rows = 0;
	/**
	 * 缓存的过期时间
	 * @var int
	 */
	protected $cache_expire_time = null;
	/**
	 * 额外的查询条件
	 * @var string
	 */
	protected $extra_cond = '';
	/**
	 *
	 * @var string
	 */
	protected $table = null;
	/**
	 * $table 对应的服务器配置实例
	 * @var TableServer
	 */
	protected $table_server = null;
	/**
	 * 访问memcache的实例
	 * @var Cache
	 */
	protected $cache_instance = null;
	/**
	 *
	 * @var DBHelper2
	 */
	protected $dbhelper_instance = null;
	/**
	 * 复位各个变量
	 * @return void
	 */
	public function reset(){
		$this->key = '';
		$this->key_value = null;
		$this->keys = array();
		$this->values = array();
		$this->primary_key = array();
		$this->table_server = null;
		$this->extra_cond = '';
		$this->columns = null;
		$this->table = null;
		$this->cache_type = self::CACHE_PRIMARY_KEY;
		$this->mem_key = '';
		$this->limit_count = 0;
		$this->limit_offset = 0;
	}
	/**
	 * 增加一个主键
	 * @param string $key 主键的名称
	 * @return void
	 */
	public function addPrimaryKey($key){
		$this->primary_key[$key] = true;
	}
	/**
	 * 一次增加多个主键
	 * @param array $keys 主键的数组
	 * @return void
	 */
	public function addPrimaryKeys(array $keys){
		$this->primary_key = array_merge($this->primary_key,array_flip($keys));
	}
	/**
	 * 设置数据的缓存类型,默认使用主键值作为缓存的key
	 * @param $cache_type 分为以下四种
	 *  TCRequest::CACHE_PRIMARY_KEY 使用主键作为缓存的key
	 *  TCRequest::CACHE_KEY_LIST    使用一个键保存一个列表，使用该列表保存存取需要的数据项
	 *  TCRequest::CACHE_FIELD_ASSOC 使用一个特定的字段为条件，查询得到一个关联数组，数组的key是指定的字段值
	 *  TCRequest::CACHE_CUSTOM_KEY  使用一个自定义的key作为缓存的key
	 */
	public function setCacheType($cache_type) {
		$this->cache_type = $cache_type;
	}

	/**
	 * @return the $cache_type
	 */
	public function getCacheType() {
		return $this->cache_type;
	}

	
	/**
	 * 设置除了缓存的主键之外，额外的where条件
	 * @param $cond
	 * @return void
	 */
	public function setExtraCondition($cond){
		$this->extra_cond = $cond;
	}
	/**
	 * 获取其他的where条件
	 * @return string
	 */
	protected function getWhereExp(){
		$sql = '';
		// 组合所有的key
		if(!empty($this->keys)){
			foreach($this->keys as $field => $val){
				$fields = explode(",", $field);
				if (count($fields) > 1) {
					if (count($val) > 0) {
						//针对WHERE (pk1=v1 AND pk2=v2) OR (pk1=v3 AND pk2=v4) OR (pk1=v5 AND pk2=v6)
						//调用方法addKeyValue("pk1,pk2", array(array(v1,v2),array(v3,v4),array(v5,v6)));
						$or_clauses = '';
						foreach ($val as $v) {
							$or_value = array_combine($fields, $v);
							if ($or_value === false) continue;
							$and_clause = '';
							foreach ($or_value as $or_value_k => $or_value_v) {
								$or_value_v = $this->prepareForSql($or_value_k,$or_value_v);
								if (isset($and_clause[0])) $and_clause .= " AND ";
								$and_clause .= "$or_value_k=$or_value_v";
							}
							if (isset($or_clauses[0])) $or_clauses .= " OR ";
							$or_clauses .= "($and_clause)";
						}
						if (isset($or_clauses[0])) {
							if (count($val) > 1) $sql .= " AND ($or_clauses) ";
							else $sql .= " AND $or_clauses ";
						}
					}
				} else {
					$formatted_value = $this->prepareForSql($field, $val);
					if(is_array($val)){
						$sql .= " AND $field IN ($formatted_value)";
					}else{
						$sql .= " AND $field=$formatted_value";
					}
				}
			}
		}
		// 如果设置额外的条件，则加上
		if(is_array($this->extra_cond)){
			$sql .= DBHelper2::joinPairs($this->extra_cond,' AND ');
		}elseif(!empty($this->extra_cond)){
			$sql .= $this->extra_cond;
		}
		if(!empty($sql)){
				return ' where 1=1 ' . $sql;
		}else{
			return '';
		}
	}
	/**
	 * 设置缓存的过期时间
	 * @param $cache_expire_time the $cache_expire_time to set
	 */
	public function setCacheExpireTime($cache_expire_time) {
		$this->cache_expire_time = intval($cache_expire_time);
	}

	/**
	 * @return the $cache_expire_time
	 */
	public function getCacheExpireTime() {
		if (isset($this->cache_expire_time)) return $this->cache_expire_time;
		return $this->getTableServer()->getCacheExpireTime();
	}
	/**
	 * 用来设置决定分库分表的字段名称和值，这个字段的值还作为各个操作的where条件之一
	 * @param string $key 用来分库分表的字段名称，通常用gameuid
	 * @param int $key_value 分库分表的字段的值
	 * @return void
	 */
	public function setKey($key,$key_value){
		if(!is_scalar($key) || empty($key)){
			throw new InvalidArgumentException('key error');
		}
		$this->key = $key;
		$this->key_value = intval($key_value);
	}
	/**
	 * 如果除了使用setKey之外的键值需要作为查询条件，可以使用该方法增加需要查询的字段和值
	 * @param string $key 用来做查询条件的字段名称
	 * @param mixed $value 该字段的值，可以是单个值，也可以是一个数组
	 * @return void
	 */
	public function addKeyValue($key,$value){
		if(empty($key) && empty($value)){
			return;
		}
		if(!isset($this->keys[$key])){
			$this->keys[$key] = $value;
			return;
		}
		// 如果原来已经设置过该key值，则把两者合并起来
		$old = & $this->keys[$key] ;
		
		if(!is_array($old)){
			$old = array($old);
		}
		if(is_array($value)){
			$old = array_merge($old,$value);
		}else{
			$old[] = $value;
		}
	}
	/**
	 * @return TableServer
	 */
	public function getTableServer(){
		if($this->table_server === null){
			$this->table_server = $this->app_config->getTableServer($this->table,$this->key_value);
		}
		return $this->table_server;
	}
	
	public function __construct(AppConfig $config){
		$this->app_config = $config;
		$this->logger = $GLOBALS['framework_logger'];
	}
	/**
	 * 设置要操作的表的名称和分库分表主键值，表名称与配置文件中定义的名称相同
	 * @param string $table the $table to set 由字母，数字，下划线组成
	 * @param int $key_value 用于分库和分表的主键值
	 */
	public function setTable($table) {
		$this->table = preg_replace('/[^A-Z0-9_]/i','',$table);
	}
	
	public function setAssocField($field){
		$this->assoc_field = preg_replace('/[^A-Z0-9_]/i','',$field);
	}
	/**
	 * 设置缓存时候使用的key
	 * @param $mem_key the $mem_key to set
	 */
	public function setCacheKey($mem_key) {
		$this->mem_key = $mem_key;
	}

	protected function getMemKey(){
		if(!isset($this->mem_key[0])){
			$this->mem_key = $this->table . '_' . $this->key_value;
			if(!empty($this->keys)){
				ksort($this->keys);
				$this->mem_key .= '_' . join('_',$this->keys);
			}
		}
		return $this->mem_key;
	}
	/**
	 * @param $limit the $limit to set
	 */
	public function setLimit($offset = 0,$row_count = 10) {
		$this->limit_offset = intval($offset);
		$this->limit_count = intval($row_count);
		if($this->limit_offset < 0){
			$this->limit_offset = 0;
		}
		if($this->limit_count < 1){
			$this->limit_count = 10;
		}
	}

	/**
	 * @return the $limit
	 */
	public function getLimit() {
		if($this->limit_count > 0){
			return " LIMIT $this->limit_offset,$this->limit_count";
		}
		return '';
	}
	/**
	 * @param $columns the $columns to set
	 */
	public function setColumns($columns) {
		if (empty($columns)) return;
		$columns = preg_replace('/\s+/', '', $columns);
		$this->columns = $columns;
	}

	/**
	 * @return the $columns
	 */
	public function getColumns() {
		if (!isset($this->columns)) {
			$this->columns = implode(',', array_keys($this->getFields()));
		}
		if (empty($this->columns)) $this->columns = '*';
		return $this->columns;
	}

	/**
	 * @param $no_cache the $no_cache to set
	 */
	public function setNoCache($value = true) {
		$this->no_cache = $value;
	}
	
	public function getNoCache() {
		return $this->no_cache;
	}
	
	/**
	 * 设置不持久化到数据库的flag
	 *
	 * @param 是否持久化到数据库，true为持久化，false为不持久化 $value
	 */
	public function setNoDb($value = true) {
		$this->no_db = $value;
	}
	public function getNoDb() {
		return $this->no_db;
	}
	
	/**
	 * 获取缓存提交的临界值，超过这个临界值则会同步到数据库
	 *
	 * @return 缓存提交临界值
	 */
	protected function getCommitThreshold() {
		return $this->getTableServer()->getCommitThreshold();
	}
	
	/**
	 * 获取缓存提交的临界值的缓存时间，单位为秒.
	 *
	 * @return 缓存提交临界值缓存时间
	 */
	protected function getCommitThresholdExpire() {
		return $this->getTableServer()->getCommitThresholdExpire();
	}
	
	/**
	 * 从缓存中取得相应的值
	 * @return mixed
	 */
	protected function getFromCache(){
		switch ($this->cache_type){
			case self::CACHE_KEY_LIST:
				return $this->unpackEntries($this->getCacheList());
			case self::CACHE_PRIMARY_KEY:
				// 没有查询条件，直接查询select * from xxx, 这种情况下是没有缓存的
				if (empty($this->keys)) return false;
				$cache = $this->getCacheInstance();
				$primary_key = $this->getPrimaryKey();
				$val = array_intersect_key($this->keys,$primary_key);
				return $this->unpackEntry($cache->get($this->getTableMemkey($val)));
			case self::CACHE_IN_LIST:
				return $this->unpackEntries($this->getInCacheList());
			default:
				$cache = $this->getCacheInstance();
				return $cache->get($this->getMemKey());
		}
	}
	
	/**
	 * 将一个值保存到缓存中
	 * @param $value
	 * @return bool
	 */
	protected function setToCache($value){
		switch ($this->cache_type){
			case self::CACHE_KEY_LIST:
				$this->setCacheList($value,true);
				break;
			case self::CACHE_IN_LIST:
			case self::CACHE_PRIMARY_KEY:
				$this->setCacheList($value,false);
				break;
			default:
				$cache = $this->getCacheInstance();
				$cache->set($this->getMemKey(),$this->prepareForCache($value),$this->getCacheExpireTime());
				break;
		}
	}
	/**
	 * 将一条数据库记录缓存到缓存里边, 这种情况在于Insert或者InserUpdate，
	 * 需要设置CACHE_KEY_LIST，但是也需要单独从缓存里边插入或者更新一条记录，不能用setToCache
	 * @param $value
	 * @return bool
	 */
	protected function setRowToCache($value) {
		return $this->setCacheList($value,false);
	}
	/**
	 * 将两个 主键=>值 的数组序列合并为一个
	 * 这个主要是在InsertUpdate和Insert的时候，需要更新CACHE_KEY_LIST
	 * 例如:
	 * $list1=["item_id"=>1,"item_id"=>2,"item_id"=>3]
	 * $list2=["item_id"=>1,"item_id"=>3,"item_id"=>5]
	 * 那么合并之后为：["item_id"=>1,"item_id"=>2,"item_id"=>3,"item_id"=>5]
	 *
	 * @param array $list1 主键数组1
	 * @param array $list2 主键数组2
	 * @return array 合并之后的主键数组
	 */
	protected function mergeKeyList($list1, $list2) {
		$ret = array();
		$ret = $list1;
		foreach ($list2 as $v2) {
			$has_duplicate = false;
			foreach ($list1 as $v1) {
				if ($v1 == $v2) {
					$has_duplicate = true;
					break;
				}
			}
			if (!$has_duplicate) $ret[] = $v2;
		}
		return $ret;
	}
	/**
	 * 根据AddKeyValue方法指定的条件，过滤得到的列表
	 * @param array $list 通过引用传递，会改变列表的内容
	 * @return void
	 */
	protected function filterList(&$list){
		if(empty($this->keys)){
			return;
		}
		foreach($list as $key => $val){
			foreach($this->keys as $rk => $rval){
				if(isset($val[$rk]) && $val[$rk] != $rval){
					unset($list[$key]);
					break;
				}
			}
		}
	}
	/**
	 * in 类型的缓存，例如：
	 * SELECT * FROM uid_gameuid_mapping WHERE uid IN ('1','2','3')
	 * 那么先从缓存里边取uid_gameuid_mapping_1,uid_gameuid_mapping_2,uid_gameuid_mapping_3
	 * 如果取不到任何数据，则直接返回false，否则从缓存中取出部分数据，从数据库中将数据补全
	 * @return mixed
	 */
	protected function getInCacheList() {
		//目前对in查询的支持，只支持对primary_key进行in
		if (empty($this->keys)) {
			$this->throwException('CACHE_IN_LIST cache type must specify the primary keys.');
		}
		$combined_values = array();
		foreach ($this->keys as $keys=>$values) {
			if (strpos($keys, ",") > 0) $keys = explode(",", $keys);
			foreach ($values as $value) {
				if (is_array($keys)) {
					$combined_values[] = array_combine($keys, $value);
				} else {
					$combined_values[] = array($keys=>$value);
				}
			}
		}
		return $this->getListFromCache($combined_values);
	}
	/**
	 * key list类型的缓存，则先取key list，
	 * 再根据取得的key list取得相应的缓存值，
	 * 如果缓存中没有全部的数据，则返回false，
	 * 如果缓存中只有部分数据，则从数据库中取得不存在的数据，并设置到缓存中。
	 * @return mixed
	 */
	protected function getCacheList(){
		$cache = $this->getCacheInstance();
		$key = $this->getListCacheKey();
		$list = $cache->get($key);
		if(empty($list)) return false;
		// 根据给定的数据，过滤需要取得的数据，
		// 注意，这一步要在取得所有缓存key之前做,否则后面key和list的index对应会有问题
		$this->filterList($list);
		return $this->getListFromCache($list, true);
	}
	
	private function getListFromCache($list, $update_list=false) {
		$cache = $this->getCacheInstance();
		// 取得根据table名字开始的缓存key:
		// 例如:uid_gameuid_mapping_1,uid_gameuid_mapping_2,uid_gameuid_mapping_3
		$keys = array_map(array($this,'getTableMemkey'),$list);
		$result = $cache->get($keys);
		// 没有一个值在缓存中
		if(empty($result)) return false;
		$ret = array_values($result);
		// 部分在缓存中
		if(count($result) != count($keys)){
			// not all data in the cache, get from database
			$cond = '';
			$primary_key = $this->getPrimaryKey();
			$fields = array_keys($primary_key);
			if(empty($fields)) {
				$this->throwException("get list from cache call must provide the primary key.");
			}
			$db_datas = array();
			$new_list = array();
			// $idx=0, $k=uid_gameuid_mapping_1
			foreach ($keys as $idx => $k) {
				// 表示不在缓存中
				if(!array_key_exists($k,$result)){
					$db_datas[] = $list[$idx];
				} else {
					$new_list[] = $list[$idx];
				}
			}
			if ($this->logger->isDebugEnabled()) {
				$this->logger->writeDebug("[TCRequest.getListFromCache]tring to get expire cache rows from db[".print_r($db_datas, true)."]");
			}
			$cond = '';
			if (count($fields) > 1) {
				$or_clauses = '';
				foreach ($db_datas as $or_value) {
					$and_clause = '';
					foreach ($or_value as $or_value_k => $or_value_v) {
						$or_value_v = $this->prepareForSql($or_value_k, $or_value_v);
						if (isset($and_clause[0])) $and_clause .= " AND ";
						$and_clause .= "$or_value_k=$or_value_v";
					}
					if (isset($or_clauses[0])) $or_clauses .= " OR ";
					$or_clauses .= "($and_clause)";
				}
				if (isset($or_clauses[0])) {
					if (count($db_datas) > 1) $cond .= "($or_clauses)";
					else $cond .= "$or_clauses";
				}
			} else {
				$field = $fields[0];
				if (count($db_datas) == 1) {
					$cond .= "$field=".$this->prepareForSql($field, $db_datas[0][$field]);
				} else {
					$in_values = array();
					foreach ($db_datas as $db_data) {
						$in_values[] = $db_data[$field];
					}
					$cond .= "$field IN (".$this->prepareForSql($field, $in_values).")";
				}
			}
			
			$sql = sprintf('SELECT %s from %s ',$this->getColumns(), $this->getTableServer()->getTableName());
			if(empty($cond)){
				$sql .= "where $this->key = $this->key_value" ;
			}else{
				$sql .= "where $cond";
			}
			$db_result = $this->getDBHelperInstance()->getAll($sql);
			if (is_array($db_result) && count($db_result)>0) {
				$this->setCacheList($this->unpackEntries($db_result),false);
				$ret = array_merge($ret, $db_result);
			}
			if ($update_list) {
				//在有些时候，调用TCDeleteRequest的时候，删除记录，但是list缓存没有被删除，
				//那么这里也会导致请求数据库，而且是每次都会请求数据库。
				//所以在这里判断下，数据库取出的记录和未命中缓存的元素是否相等。不相等就需要将相应的未命中缓存记录删除
				if (is_array($db_result) && count($db_result) > 0) {
					foreach ($db_result as $entry) {
						$new_list[] = array_intersect_key($entry, $primary_key);
					}
				}
				if (count($new_list) != count($list)) {
					$this->logger->writeError("tring to repair cache key list[old_list=".print_r($list, true).",new_list=".print_r($new_list, true)."]");
					$cache->set($this->getListCacheKey(), $new_list, 0);
				}
			}
		}
		return $ret;
	}
	/**
	 * 设置用于构造list cache key的数组.例如：
	 * array("field_name1"=>"field_value1","field_name2"=>"field_value2"...,"field_nameN"=>"field_valueN");
	 *
	 * @param array $list_cache_key
	 */
	public function setListCacheKey($list_cache_key) {
		$this->list_cache_key = $list_cache_key;
	}
	
	protected function getListCacheKey(){
		$key = $this->table.'_list_';
		if (isset($this->list_cache_key) 
			&& is_array($this->list_cache_key) 
			&& count($this->list_cache_key) > 0) {
			ksort($this->list_cache_key);
			$key .= implode('_', $this->list_cache_key);
		} else {
			$key .= $this->key_value;
		}
		return $key;
	}
	
	protected function setCacheList($values,$set_list = true){
		if(empty($values)){
			return;
		}
		$primary_key = $this->getPrimaryKey();
		$cache = $this->getCacheInstance();
		// 如果要缓存的结果不是数组
		if(!is_array($values)){
			$key = $this->getMemKey();
			$cache->set($key,$this->prepareForCache($values),$this->getCacheExpireTime());
		}
		elseif(!is_array(current($values))){
			// 缓存的是一条数据, $val获取到的是 主键=>值 数组
			$val = array_intersect_key($values,$primary_key);
			$key = $this->getTableMemkey($val);
			$cache->set($key,$this->prepareForCache($values),$this->getCacheExpireTime());
		}else{
			// 缓存数据的数组
			$list = array();
			$cache_values = array();
		
			foreach($values as $val){
				$l = array_intersect_key($val,$primary_key);
				if($set_list){
					$list[] = $l;
				}
				$key = $this->getTableMemkey($l);
				$cache_values[$key] = $this->prepareForCache($val);
			}
			if($set_list){
				$list_cache_key = $this->getListCacheKey();
				// the list key cache nerver expired
				if ($this->logger->isDebugEnabled()) {
					$this->logger->writeDebug("[setCacheList]tring to modify list cache[key=$list_cache_key,value=".print_r($list, true)."]");
				}
				$cache->set($list_cache_key,$list,0);
			}
			$cache->setMulti($cache_values,$this->getCacheExpireTime(),true);
		}
	}
	
	protected function getPrimaryKey(){
		if(empty($this->primary_key)){
			$this->primary_key = $this->getTableServer()->getPrimary();
			if(empty($this->primary_key)){
				$this->primary_key = array($this->key => true);
			}
		}
		return $this->primary_key;
	}
	
	protected function getTableMemkey($val){
		if(count($val) > 1){
			ksort($val);
		}
		return $this->table . '_' . join('_',$val);
	}
	
	protected function deleteFromCache(){
		switch ($this->cache_type) {
			case self::CACHE_KEY_LIST:
				$cache = $this->getCacheInstance();
				$cache_list_key = $this->getListCacheKey();
				$cached_list = $cache->get($cache_list_key);
				foreach ($cached_list as $cached_entry) {
					$cache->delete($this->getTableMemkey($cached_entry));
				}
				$cache->delete($cache_list_key);
				break;
			case self::CACHE_IN_LIST:
			case self::CACHE_PRIMARY_KEY:
				$cache = $this->getCacheInstance();
				$deleted_vals = array();
				if ($this->affected_rows <= 1) {
					$primary_key = $this->getPrimaryKey();
					$deleted_vals[] = array_intersect_key($this->keys,$primary_key);
				} else {
					$primary_key = $this->getPrimaryKey();
					foreach ($this->keys as $field => $val) {
						$fields = explode(",", $field);
						if (count($fields) > 1) {
							if ($fields != array_keys($primary_key)) continue;
							foreach ($val as $v) {
								$arr = array_combine($fields, $v);
								if ($arr === false) continue;
								$deleted_vals[] = $arr;
							}
						} else {
							if (array($field) != array_keys($primary_key)) continue;
							if(is_array($val)){
								foreach ($val as $v) {
									$deleted_vals[] = array($field=>$v);
								}
							} else {
								$deleted_vals[] = array($field=>$v);
							}
						}
					}
				}
				foreach ($deleted_vals as $deleted_val) {
					$cache->delete($this->getTableMemkey($deleted_val));
				}
				// 将相应的cache_list里边的元素删除掉
				$cached_list_key = $this->getListCacheKey();
				$cached_list = $cache->get($cached_list_key);
				foreach ($deleted_vals as $deleted_val) {
					foreach ($cached_list as $k=>$v) {
						if ($v == $deleted_val) {
							unset($cached_list[$k]);
						}
					}
				}
				if (empty($cached_list)) {
					if ($this->logger->isDebugEnabled()) {
						$this->logger->writeDebug("[deleteFromCache]tring to delete list cache[key=$cache_list_key]");
					}
					
					$cache->delete($cached_list_key);
				} else {
					if ($this->logger->isDebugEnabled()) {
						$this->logger->writeDebug("tring to delete item in list cache[key=$cached_list_key,value=".print_r($cached_list, true)."]");
					}
					$cache->set($cached_list_key, $cached_list, 0);
				}
				break;
		}
	}
	
	/**
	 *
	 * @return Cache
	 */
	public function getCacheInstance(){
		if($this->cache_instance === null){
			$this->cache_instance = $this->getTableServer()->getCacheInstance();
		}
		return $this->cache_instance;
	}
	/**
	 *
	 * @return DBHelper2
	 */
	protected function getDBHelperInstance(){
		if($this->dbhelper_instance === null){
			$this->dbhelper_instance = $this->getTableServer()->getDBHelperInstance();
		}
		return $this->dbhelper_instance;
	}
	/**
	 * 执行request的方法
	 * @return mixed
	 */
	abstract public function execute();

	/**
	 * 插入操作时候最新插入的auto increament字段的id
	 * @return int
	 */
	public function insertId(){
		return $this->insert_id;
	}
	/**
	 * update，delete等操作的影响行数
	 * @return int
	 */
	public function affectedRows(){
		return $this->affected_rows;
	}
	
	public function addValues($values){
		return $values;
	}
	
	protected function throwException($msg,$code = 1){
		throw new RequestException($msg,$code);
	}
	
	/**
	 * 获取表的字段定义
	 *
	 * @return 表的字段定义
	 */
	public function getFields() {
		if (empty($this->fields_def)) {
			$this->fields_def = $this->getTableServer()->getFields();
		}
		return $this->fields_def;
	}
	
	/**
	 * 获取sql判断语句的值，例如a=b，b的值可能需要根据字段a的类型来选择是否加单引号
	 * @param $field 字段名
	 * @param $value 字段值，可以是数组
	 * @return 根据$field的类型返回相关的字段值，例如'addslashes($value)'
	 */
	protected function prepareForSql($field, $value) {
		$fields_def = $this->getFields();
		$field_info = $fields_def[$field];
		$str_value = strval($value);
		if (!isset($str_value[0])) $value = $field_info['default'];
		if (empty($field_info)) {
			if (is_array($value)) {
				if (is_string($value[0])) {
					return "'".implode("','", array_map("elex_addslashes", $value))."'";
				} else {
					return implode(",", $value);
				}
			} else {
				if (is_string($value)) {
					return "'".elex_addslashes($value)."'";
				} else {
					return $value;
				}
			}
		}
		switch ($field_info['type']) {
			case FieldType::TYPE_STRING:
			case FieldType::TYPE_PACKED:
				if (is_array($value)) {
					return "'".implode("','", array_map("elex_addslashes", $value))."'";
				}
				return "'".elex_addslashes($value)."'";
			default:
				if (is_array($value)) {
					return implode(",", $value);
				}
				return $value;
		}
	}
	
	/**
	 * 将缓存的行数据的所有字段补全，这主要是针对Insert的时候，有些字段是不需要添加到values里边的，
	 * 这样会导致缓存数据的不完整
	 * 对于像reserve_1,reserve_2...这样的保留字段，如果不明确设置其值的话，是不会补全的
	 *
	 * @param array $row 补全之后的数组
	 */
	protected function completeRow(&$row) {
		$fields_def = $this->getFields();
		$table_name = $this->getTableServer()->getTableName();
		// completeRow不光是TCInsertRequest调用,TCInsertUpdateRequest也会调用，
		//如果是Update的话，说明缓存中已经有原来的数据，这时候，不能使用default值，需要将缓存中的值merge才行
		$primary_key = $this->getPrimaryKey();
		$val = array_intersect_key($row,$primary_key);
		$key = $this->getTableMemkey($val);
		$cache = $this->getCacheInstance();
		$cached_row = $cache->get($key);
		if ($cached_row !== false) {
			$row = array_merge($cached_row, $row);
			return;
		}
		foreach ($fields_def as $field_def) {
			if (isset($row[$field_def['name']])) continue;
			$default_value = $field_def['default'];
			if ($default_value === 'function') {
				if (function_exists($field_def['function'])) {
					$default_value = $field_def['function']("$table_name.".$field_def['name']);
				}
			}
			$row[$field_def['name']] = $default_value;
		}
	}
	/**
	 * 在存储到缓存之前需要对记录进行处理：例如将未压缩的记录进行压缩
	 * @param 未处理的应用层的数据记录 $value
	 * @return 进过处理的缓存层的数据记录
	 */
	private function prepareForCache($value) {
		if (empty($value)) return $value;
		// 判断是否需要将数据压缩之后存储，这样可以节省缓存空间
		$pack_fields = $this->getTableServer()->getPackFields();
		if (empty($pack_fields)) return $value;
		return $this->packEntry($value);
	}
	
	protected function unpackEntries($entries) {
		if (empty($entries)) return $entries;
		$pack_fields = $this->getTableServer()->getPackFields();
		if (empty($pack_fields)) return $entries;
		return array_map(array($this, 'unpackEntry'), $entries);
	}
	
	/**
	 * 将压缩的数据解压缩
	 *
	 * @param 压缩之后的数据库记录 $entry
	 * @return 解压缩之后的数据库记录
	 */
	protected function unpackEntry($entry) {
		if (empty($entry)) return $entry;
		$pack_fields = $this->getTableServer()->getPackFields();
		if (empty($pack_fields)) return $entry;
		$unpacked_entry = array();
		foreach ($entry as $field_name => $field_value) {
			$unpacked_entry[$field_name] = $field_value;
			if (!in_array($field_name, array_keys($pack_fields))) {
				continue;
			}
			if (empty($field_value)){
				$unpacked_entry[$field_name] = array();
				continue;
			}
			$packed_def = $pack_fields[$field_name];
			$length = strlen($field_value) / $packed_def['unit_size'];
			$unpacked_arrs = array();
			$associative = true;
			if (strlen($packed_def['format']) == 1) $associative = false;
			for ($i = 0; $i < $length; $i++) {
				$packed_str = substr(
					$field_value, 
					$packed_def['unit_size']*$i, 
					$packed_def['unit_size']);
				$unpacked_arr = unpack($packed_def['format'], $packed_str);
				if (empty($unpacked_arr)) continue;
				if (!$associative) $unpacked_arrs[] = $unpacked_arr[1];
				else $unpacked_arrs[] = $unpacked_arr;
			}
			
			$unpacked_entry[$field_name] = $unpacked_arrs;
		}
		return $unpacked_entry;
	}
	/**
	 * 对数据进行压缩
	 *
	 * @param 未被压缩的数据 $entry
	 * @return 压缩后的数据
	 */
	protected function packEntry($entry) {
		$pack_fields = $this->getTableServer()->getPackFields();
		if (empty($pack_fields)) return $entry;
		$packed_entry = array();
		foreach ($entry as $field_name => $field_value) {
			$packed_entry[$field_name] = $field_value;
			if (!in_array($field_name, array_keys($pack_fields))) continue;
			if (empty($field_value)){
				$packed_entry[$field_name] = '';
				continue;
			}
			$packed_def = $pack_fields[$field_name];
			$sub_fields = $packed_def['sub_fields'];
			$packed_str = $this->packField($sub_fields, $field_value);
			$packed_entry[$field_name] = $packed_str;
		}
		return $packed_entry;
	}
	
	/**
	 * 对某个字段进行压缩.
	 * 压缩字段是由子字段构成的，例如：
	 * user_action_count的data压缩字段，是由action_id:count这样的两个子字段构成的
	 *
	 * @param array $sub_fields 子字段的定义,定义子字段的压缩format
	 * @param array $field_value 子字段的值
	 * @return 压缩后的字符串
	 */
	protected function packField($sub_fields, $field_value) {
		$packed_str = '';
		$associative = true;
		if (count($sub_fields) == 1) $associative = false;
		foreach ($field_value as $unpacked_entry) {
			if (empty($unpacked_entry)) continue;
			if ($associative){
				foreach ($sub_fields as $sub_field_name=>$sub_format) {
					$packed_str .= pack($sub_format, $unpacked_entry[$sub_field_name]);
				}
			}else {
				$packed_str .= pack($sub_fields[0], $unpacked_entry);
			}
		}
		return $packed_str;
	}
}
?>
