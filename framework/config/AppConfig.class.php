<?php
require_once FRAMEWORK . '/db/FieldType.class.php';
require_once FRAMEWORK . '/config/ElexConfig.class.php';
require_once FRAMEWORK . '/cache/Cache.class.php';
require_once FRAMEWORK . '/common.func.php';
require_once FRAMEWORK . '/GameConstants.php';
require_once FRAMEWORK . '/exception/GameException.class.php';

class AppConfig extends ElexConfig {
	/**
	 * 部署时候的分库和分表策略常量定义
	 * 0 不分库，不分表
	 * 1 只分库
	 * 2 只分表
	 * 3 既分库，又分表
	 * @var int
	 */
	const DEPLOY_PART_NONE = 0;
	const DEPLOY_PART_DB = 1;
	const DEPLOY_PART_TABLE = 2;
	const DEPLOY_PART_DB_TABLE = 3;
	const DEPLOY_PART_TIME_WEEK = 4;
	
	const DEFAULT_CACHE_SERVER = 'default_cache_server';
	const DEFAULT_CACHE_SUPER_SERVER = 'default_cache_super_server';
	const DEFAULT_DB_SERVER = 'default_db_server';
	const DEFAULT_DB_SLAVE = 'default_db_slave';
	const DEFAULT_DB_WORKLOAD = 'default_db_workload';
	
	const LOCK_CACHE_SERVER = 'lock_cache_server';
	const STATUS_CACHE_SERVER = 'status_cache_server';
	
	const SERVICE_CLOSED = 'service_is_closed';
	
	const CONFIG_GLOBAL = 'global';
	const DEBUG_MODE = 'debug_mode';
	const PERF_TEST_MODE = 'perf_test_mode';
	const MEMCACHED_CLIENT = 'cache_client';
	const SIGNATURE_KEY = 'sig_key';
	
	const TABLE_MAX_DB_NUM = 'max_db_num';
	const TABLE_MAX_TABLE_NUM = 'max_table_num';
	const TABLE_DB_MACHINE_NUM = 'db_machine_num';
	const TABLE_DB_SERVER_LIST = 'db_server_list';
	const TABLE_DB_SERVER_CONFIG = 'db_server_config';
	const TABLE_CACHE_SERVER = 'cache_server';
	const TABLE_CACHE_SUPER_SERVER = 'cache_super_server';
	const TABLE_DEPLOY = 'deploy';
	const TABLE_DB_NAME = 'db_name';
	const TABLE_DB_SERVER_INDEX = '_index';
	const TABLE_PRIMARY_KEY = 'primary';
	const TABLE_FIELDS = 'fields';
	const TABLE_CACHE_COMMIT_THRESHOLD = 'cache_commit_threshold';
	const TABLE_PACK_FIELDS = 'pack_fields';
	const TABLE_READONLY = 'readonly';
	const TABLE_CACHE_TYPE = 'cache_type';
	const TABLE_CACHE_EXPIRE_TIME = 'cache_expire_time';
	
	const DB_MASTER_DSN = 'dsn';
	const DB_SLAVE_DSN = 'slave_dsn';
	const DB_WORKLOAD = 'workload';
	
	protected $debug_mode = false;
	protected $perf_test_mode = false;
	protected $service_is_closed = false;
	
	protected function init($options){
		parent::init($options);
		// 处理包含"|"的section，将其扩展为多个section
		if(is_array($this->config) && !empty($this->config)){
			foreach ($this->config as $section => $values){
				if(strpos($section,'|') !== false){
					$sections = explode('|',$section);
					foreach ($sections as $sec){
						$this->config[trim($sec)] = $values;
					}
					unset($this->config[$section]);
				}
			}
		}
		// 针对fields进行特殊处理，因为fields字段属于比较费时间来进行解析的字段，所以最好将其缓存到APC缓存中去
		$fields_defs_as_str = $this->config[self::TABLE_FIELDS];
		if (isset($fields_defs_as_str)) {
			$fields_defs = array();
			foreach ($fields_defs_as_str as $table_name=>$fields_def_as_str) {
				$fields_def = preg_split('/[:+]/', $fields_def_as_str);
				for ($i = 0, $length=count($fields_def)-1; $i < $length; $i=$i+3) {
					$field_name = $fields_def[$i];
					$field_type = $fields_def[$i+1];
					$default_value = $fields_def[$i+2];
					if (!in_array($field_type, 
							array(FieldType::TYPE_INTEGER,
								FieldType::TYPE_STRING,
								FieldType::TYPE_FLOAT,
								FieldType::TYPE_PACKED))) {
						$this->throwException("fields definition error:unknown type[".$fields_def[$i+1]."]", ELEX_ERR_CONFIG_ERROR);
					}
					$field_props = array("name"=>$field_name, "type"=>$field_type);
					if (strpos($default_value, "function<>") !== false) {
						$arr = explode("<>", $default_value);
						$field_props['default'] = "function";
						$field_props['function'] = $arr[1];
					} else {
						$field_props['default'] = $default_value;
					}
					$fields_defs[$table_name][$field_props['name']] = $field_props;
				}
			}
			$this->config[self::TABLE_FIELDS] = $fields_defs;
		}
		
		// 缓存提交数据库阈值cache_commit_threshold
		$cache_commit_thresholds_as_str = $this->config[self::TABLE_CACHE_COMMIT_THRESHOLD];
		if (isset($cache_commit_thresholds_as_str)) {
			$cache_commit_threshold_defs = array();
			foreach ($cache_commit_thresholds_as_str as $table_name=>$cache_commit_threshold_as_str) {
				$parts = explode(":", $cache_commit_threshold_as_str);
				$cache_commit_threshold_defs[$table_name]['threshold'] = isset($parts[0])?intval($parts[0]):0;
				$cache_commit_threshold_defs[$table_name]['expire'] = isset($parts[1])?intval($parts[1]):1800;
			}
			$this->config[self::TABLE_CACHE_COMMIT_THRESHOLD] = $cache_commit_threshold_defs;
		}
		
		// 压缩数据处理pack_fields
		$pack_fields_as_str = $this->config[self::TABLE_PACK_FIELDS];
		if (isset($pack_fields_as_str)) {
			$pack_fields_defs = array();
			foreach ($pack_fields_as_str as $table_name=>$pack_field_as_str) {
				$packed_fields_def = array();
				$parts = explode(",", $pack_field_as_str);
				foreach ($parts as $part) {
					$preg_parts = preg_split('/[:\/]/', $part);
					$sub_fields = array();
					$unit_size = 0;
					$format = substr($part, strpos($part, ":") + 1);
					for($i = 1; $i < count($preg_parts); $i++) {
						$preg_part = $preg_parts[$i];
						$sub_field_name = substr($preg_part, 1);
						$sub_fields[$sub_field_name] = $preg_part[0];
						switch ($preg_part[0]) {
							case "C":
								$unit_size += 1; break;
							case "S":
								$unit_size += 2; break;
							case "L":
								$unit_size += 4; break;
						}
					}
					$packed_fields_def[$preg_parts[0]] = array(
						"unit_size"=>$unit_size,
						"sub_fields"=>$sub_fields,
						"format"=>$format
					);
				}
				$pack_fields_defs[$table_name] = $packed_fields_def;
			}
			$this->config[self::TABLE_PACK_FIELDS] = $pack_fields_defs;
		}
		
		$global_config = $this->config[self::CONFIG_GLOBAL];
		$this->service_is_closed = $this->getValue($global_config,self::SERVICE_CLOSED,false,'bool');
		$this->debug_mode = $this->getValue($global_config,self::DEBUG_MODE,false,'bool');
		$this->perf_test_mode = $this->getValue($global_config,self::PERF_TEST_MODE,false,'bool');
	}
	
	/**
	 * 创建一个AppConfig对象实例
	 * @param $config_file
	 * @param $options
	 * @return AppConfig
	 */
	public static function getInstance($config_file,$options = null){
		if(is_null(self::$instance)){
			self::$use_apc_cache = function_exists('apc_fetch');
			// 验证配置文件路径的正确性
			self::checkConfigPath($config_file);
			// 先尝试从缓存中反序列化
			self::$instance = self::getConfigFromCache($config_file);
			if(empty(self::$instance)){
				// 创建一个新的AppConfig对象
				self::$instance = new self($config_file,$options);
				// 将对象序列化后写入缓存
				self::writeConfigToCache($config_file, self::$instance);
			}
		}
		return self::$instance;
	}
	
	/**
	 * 是否运行在debug模式下
	 * @return boolean
	 */
	public function isDebugMode(){
		return $this->debug_mode;
	}
	
	/**
	 * 是否运行在性能测试模式下
	 * @return boolean
	 */
	public function isPerfTestMode(){
		return $this->perf_test_mode;
	}
	/**
	 * 判断整个站点的服务是否已经关闭了
	 * @return bool
	 */
	public function isServiceClosed(){
		return $this->service_is_closed;
	}
	/**
	 * 获取一个section的配置
	 * @param $section_name
	 * @return array 如果不存在该section，则返回null
	 */
	public function getSection($section_name){
		if(is_null($this->config) || empty($section_name)){
			return false;
		}
		if(array_key_exists($section_name,$this->config)){
			return $this->config[$section_name];
		}
		return null;
	}
	/**
	 * 返回一条指定section和key的配置
	 * @param $section
	 * @param $config_key
	 * @return mixed
	 */
	public function getConfig($section,$config_key){
		if(empty($config_key)){
			return false;
		}
		if(!empty($section)){
			$sec_val = $this->getSection($section);
			if(empty($sec_val)){
				return false;
			}
			if(array_key_exists($config_key,$sec_val)){
				return $sec_val[$config_key];
			}
		}elseif(array_key_exists($config_key,$this->config)){
			return $this->config[$config_key];
		}
		return null;
	}
	/**
	 * 根据指定key，获取全局的配置项
	 * @param $key
	 * @return string
	 */
	public function getGlobalConfig($key){
		static $global = null;
		if(empty($global)){
			$global = $this->getSection(self::CONFIG_GLOBAL);
		}
		if(array_key_exists($key,$global)){
			return $global[$key];
		}
		return false;
	}
	
	/**
	 * 获取锁缓存服务器客户端实例
	 * @return Cache
	 */
	public function getLockCacheInstance(){
		static $cache = null;
		if($cache !== null){
			return $cache;
		}
		$cache = new Cache($this->getCacheConfig(self::LOCK_CACHE_SERVER));
		return $cache;
	}
	/**
	 * 获取用户状态缓存客户端实例
	 * @return Cache
	 */
	public function getStatusCacheInstance(){
		static $cache = null;
		if($cache !== null){
			return $cache;
		}
		$cache = new Cache($this->getCacheConfig(self::STATUS_CACHE_SERVER));
		return $cache;
	}
	
	public function getDefaultCacheInstance(){
		static $cache = null;
		if($cache === null){
			$cache = new Cache($this->getCacheConfig(self::DEFAULT_CACHE_SERVER));
		}
		return $cache;
	}
	
	protected function getCacheConfig($key){
		$cache_server = $this->getGlobalConfig($key);
		if(empty($cache_server)){
			// 如果没有配置所需的缓存服务器，则使用默认的cache服务器
			$cache_server = $this->getGlobalConfig(AppConfig::DEFAULT_CACHE_SERVER);
		}
		// 如果两者都没有配置，则应用配置错误
		if(empty($cache_server)){
			throw new ConfigException('cache server config error key :'.$key, ELEX_ERR_CONFIG_ERROR);
		}
		if(strpos($cache_server,',') !== false){
			$cache_server = explode(',',$cache_server);
		}
		return $cache_server;
	}
	
	/**
	 * 获取一个表的配置服务器信息
	 * @param $table 表的名称
	 * @param $key_value 主键的值,如果不需要分库分表，可以不设置该值
	 * @throws InvalidArgumentException if $table is empty
	 * @return TableServer
	 */
	public function getTableServer($table,$key_value = null){
		if(!isset($table[0])){
			throw new InvalidArgumentException('table value empty');
		}
		if(array_key_exists($table,$this->config)){
			return $this->getConfigExistTableServer($table,$key_value);
		}else{
			// 返回默认的配置
			return $this->getDefaultTableServer($table);
		}
	}
	/**
	 * 获得用于id_sequence的数据库dbo和缓存的cache操作类实例
	 * @return array 包含 dbo 和cache为key的数组
	 */
	public function getIDSequenceConfig(){
		static $config = null;
		if($config === null){
			$table_server = $this->getDefaultTableServer('id_sequence');
			$config = array('dbo' => $table_server->getDBHelperInstance(),
			'cache' => $table_server->getCacheInstance());
		}
		return $config;
	}
	/**
	 * 取得存在配置节的表的TableServer实例
	 * @param $table
	 * @param $key_value
	 * @return TableServer
	 */
	protected function getConfigExistTableServer($table,$key_value){
		static $table_servers = array();
		$table_config = $this->getSection($table);
		$db_table_name = $this->getTableName($table,$table_config,$key_value);
		// 将表的配置缓存起来
		if(isset($table_servers[$db_table_name])){
			return $table_servers[$db_table_name];
		}
		// 获取数据库配置
		$db_config_key = $table_config[self::TABLE_DB_SERVER_CONFIG];
		if (empty($db_config_key)) {
			$db_index = $this->getDbIndex($table_config,$key_value);
			$db_config_key = $this->getDbConfigKeyByIndex($table_config,$db_index);
		}
		if(empty($db_config_key)){
			$db_config = $this->getDefaultDbConfig();
		}else{
			$db_config = $this->getSection($db_config_key);
		}
		// 将数据库名字添加到配置项中
		$db_name = "";
		$table_name = $db_table_name;
		$arr = explode(".", $db_table_name);
		if (count($arr) == 2) {
			$db_name = $arr[0];
			$table_name = $arr[1];
		}
		$db_config[AppConfig::DB_MASTER_DSN] = sprintf($db_config[AppConfig::DB_MASTER_DSN], $db_name);
		$db_config[AppConfig::DB_SLAVE_DSN] = sprintf($db_config[AppConfig::DB_SLAVE_DSN], $db_name);
		require_once FRAMEWORK . '/db/TableServer.class.php';
		if (empty($table_config[AppConfig::TABLE_CACHE_SERVER])) {
			$table_config[AppConfig::TABLE_CACHE_SERVER] = $this->getGlobalConfig(AppConfig::DEFAULT_CACHE_SERVER);
		}
		$svr = new TableServer(
			$db_config[AppConfig::DB_MASTER_DSN],
			$table_config[AppConfig::TABLE_CACHE_SERVER],
			$table_name,
			$db_config[AppConfig::DB_SLAVE_DSN],
			$table_config[AppConfig::TABLE_CACHE_SUPER_SERVER]);
			$svr->setWorkload($db_config[AppConfig::DB_WORKLOAD]);
		if ($GLOBALS['database_logger']->isDebugEnabled()) {
			$GLOBALS['database_logger']->writeDebug("initialized table server $db_table_name");
		}
		// 设置表的主键
		$primary_keys = $this->getSection(AppConfig::TABLE_PRIMARY_KEY);
		if(isset($primary_keys[$table])){
			$svr->setPrimary($primary_keys[$table]);
		}
		// 设置表是否只读
		if(isset($table_config[AppConfig::TABLE_READONLY])){
			$svr->setReadonly($this->getValue($table_config,AppConfig::TABLE_READONLY,false,'bool'));
		}
		// 设置表的缓存类型
		if(isset($table_config[AppConfig::TABLE_CACHE_TYPE])){
			$svr->setCacheType($table_config[AppConfig::TABLE_CACHE_TYPE]);
		}
		// 设置表的字段
		$fields_defs = $this->getSection(AppConfig::TABLE_FIELDS);
		if(isset($fields_defs[$table])){
			$svr->setFields($fields_defs[$table]);
		}
		// 设置表的缓存时间
		$cache_expire_times = $this->getSection(AppConfig::TABLE_CACHE_EXPIRE_TIME);
		if (isset($cache_expire_times[$table])) {
			$svr->setCacheExpireTime($cache_expire_times[$table]);
		}
		// 设置提交阈值
		$cache_commit_threshold = $this->getSection(AppConfig::TABLE_CACHE_COMMIT_THRESHOLD);
		if (isset($cache_commit_threshold[$table])) {
			$svr->setCommitThreshold($cache_commit_threshold[$table]['threshold']);
			$svr->setCommitThresholdExpire($cache_commit_threshold[$table]['expire']);
		}
		// 设置压缩字段
		$pack_fields = $this->getSection(AppConfig::TABLE_PACK_FIELDS);
		if (isset($pack_fields[$table])) {
			$svr->setPackFields($pack_fields[$table]);
		}
		
		$table_servers[$db_table_name] = $svr;
		return $svr;
	}
	/**
	 * 取得默认的配置信息
	 * @param $table
	 * @return TableServer
	 */
	protected function getDefaultTableServer($table){
		static $default_table_server = null;
		if(isset($default_table_server)) {
			return $default_table_server;
		}
		$global = $this->getSection(AppConfig::CONFIG_GLOBAL);
		require_once FRAMEWORK . '/db/TableServer.class.php';
		$default_table_server = new TableServer(
			$global[AppConfig::DEFAULT_DB_SERVER], // 默认的数据库服务器dsn
			$global[AppConfig::DEFAULT_CACHE_SERVER], // 默认的缓存服务器
			$table,	// 表名称
			$global[AppConfig::DEFAULT_DB_SLAVE], // 默认的从数据库服务器
			// 迁移或者扩容时候，当前缓存服务器的源缓存服务器组
			$global[AppConfig::DEFAULT_CACHE_SUPER_SERVER]
		);
		$default_table_server->setWorkload($global[AppConfig::DEFAULT_DB_WORKLOAD]);
		return $default_table_server;
	}
	
	protected function getDefaultDbConfig(){
		$global = $this->getSection(self::CONFIG_GLOBAL);
		$config = array();
		$config[AppConfig::DB_MASTER_DSN] = $global[AppConfig::DEFAULT_DB_SERVER];
		$config[AppConfig::DB_SLAVE_DSN] = $global[AppConfig::DEFAULT_DB_SLAVE];
		$config[AppConfig::DB_WORKLOAD] = $global[AppConfig::DEFAULT_DB_WORKLOAD];
		return $config;
	}
	
	/**
	 * 根据表的配置文件，确定需要访问的数据库配置的key
	 * @param $table_config
	 * @param $index
	 * @return string
	 */
	protected function getDbConfigKeyByIndex($table_config,$index){
		if(is_null($index)){
			return null;
		}
		$svr_list_str  = $table_config[AppConfig::TABLE_DB_SERVER_LIST];
		$svr_list = explode(',',$svr_list_str);
		require_once FRAMEWORK . '/utility/StringUtil.class.php';
		
		foreach($svr_list as $svr){
			$svr = trim($svr);
			$nums = StringUtil::expandNumList($table_config[$svr . AppConfig::TABLE_DB_SERVER_INDEX]);
			if(in_array($index,$nums)){
				return $svr;
			}
		}
		$this->throwException('db config not found', ELEX_ERR_CONFIG_ERROR);
	}
	
	/**
	 * 根据配置文件中的分库，分表策略，确定要执行的表的名称
	 * @param $table
	 * @param $table_config
	 * @param $key_value
	 * @return string
	 */
	protected function getTableName($table,$table_config,$key_value){
		$db_name = $table_config[AppConfig::TABLE_DB_NAME];
		$db_max_num = intval($table_config[AppConfig::TABLE_MAX_DB_NUM]);
		if($db_max_num < 1){
			$db_max_num = 1;
		}
		$table_max_num = intval($table_config[AppConfig::TABLE_MAX_TABLE_NUM]);
		if($table_max_num < 1){
			$table_max_num = 1;
		}
		switch ($table_config[AppConfig::TABLE_DEPLOY]){
			case AppConfig::DEPLOY_PART_DB:
				// 只分库
				$idx = $key_value % $db_max_num;
				$table_name =  sprintf('%s%d.%s',$db_name,$idx,$table);
				break;
			case AppConfig::DEPLOY_PART_TABLE:
				// 只分表
				$idx = intval($key_value % $table_max_num);
				$table_name =  sprintf('%s.%s_%d',$db_name,$table,$idx);
				break;
			case AppConfig::DEPLOY_PART_DB_TABLE:
				// 既分库，又分表
				$db_idx = intval($key_value / $table_max_num) % $db_max_num;
				$table_idx = intval($key_value % $table_max_num);
				$table_name =  sprintf('%s%d.%s_%d',$db_name,$db_idx,$table,$table_idx);
				break;
			case AppConfig::DEPLOY_PART_TIME_WEEK:
				// 按照周进行分表
				$table_name =  sprintf('%s.%s_%s',$db_name,$table,date('W'));
				break;
			default:
				// 既不分库，也不分表
				$table_name = sprintf('%s.%s',$db_name,$table);
				break;
		}
		return $table_name;
	}
	
	protected function getDbIndex($table_config,$key_value){
		$idx = null;
		$db_max_num = intval($table_config[AppConfig::TABLE_MAX_DB_NUM]);
		if($db_max_num < 1){
			$db_max_num = 1;
		}
		switch ($table_config[AppConfig::TABLE_DEPLOY]){
			case AppConfig::DEPLOY_PART_DB:
				// 只分库
				$idx = intval($key_value % $db_max_num);
				break;
			case AppConfig::DEPLOY_PART_DB_TABLE:
				$table_max_num = intval($table_config[AppConfig::TABLE_MAX_TABLE_NUM]);
				if($table_max_num < 1){
					$table_max_num = 1;
				}
				// 既分库，又分表
				$idx = intval($key_value / $table_max_num) % $db_max_num;
				break;
		}
		return $idx;
	}
	
	/**
	 * 根据指定的模块，获取日志的级别
	 * 可以记录log日志的模块有：amf_entry, model, actions, web_entry, framework
	 * @param $key
	 * @return string
	 */
	public function getLogLevel($mod) {
		$log_level = $this->getGlobalConfig("log_level_$mod");
		if (empty($log_level)) $log_level = ELEX_LOG_OFF;
		return $log_level;
	}
}
?>