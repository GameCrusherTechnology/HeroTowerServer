<?php
require_once GAMELIB . '/config/config.php';
require_once FRAMEWORK . '/config/AppConfig.class.php';
class GameConfig extends AppConfig{
	/**
	 * 时区设置的配置key
	 * @var string
	 */
	const GLOBAL_TIME_ZONE = 'timezone';
	/**
	 * 站外邀请的的配置文件key
	 * @var string
	 */
	const GLOBAL_INVITE_OUTSITE = 'invite_out';
	//TODO以后的每个版本都要设定各自的时区
	/**
	 * 默认的时区：中国上海
	 * @var string
	 */
	const DEFAULT_TIMEZONE = TIME_ZONE;
	
	private static $instances = array();
	
	/**
	 * 获取GameConfig的实例
	 * @return GameConfig
	 */
	public static function getInstance($gameuid){
		$config_file_name = self::getConfigFile($gameuid);
		if (empty($config_file_name)) self::throwException("can not find config file by gameuid[".$GLOBALS["gameuid"]."], check the partition config file.");
		if (!isset(self::$instances[$config_file_name]) 
			|| !(self::$instances[$config_file_name] instanceof GameConfig)) {
			self::initConfig($config_file_name);
		}
		return self::$instances[$config_file_name];	
	}
	
	private static function initConfig($config_file_name) {
		self::$use_apc_cache = function_exists('apc_fetch');
		$config_file = APP_ROOT."/etc_all/".self::getPlatForm()."/$config_file_name.ini";
	
		// 验证配置文件路径的正确性
		self::checkConfigPath($config_file);
		// 先尝试从缓存中反序列化
		$instance = self::getConfigFromCache($config_file);
		if(empty($instance)){
			// 创建一个新的GameConfig对象
			$instance = new self($config_file);
			// 将对象序列化后写入缓存
			self::writeConfigToCache($config_file, $instance);
		}
		self::$instances[$config_file_name] = $instance;
	}
	
	/**
	 * 获取配置的时区
	 * @return string
	 */
	public function getTimeZone(){
		$tz = $this->getConfig(AppConfig::CONFIG_GLOBAL,self::GLOBAL_TIME_ZONE);
		return $tz ? $tz : self::DEFAULT_TIMEZONE;
	}
	
	/**
	 * 获取配置的平台信息
	 * @return string
	 */
	public static function getPlatForm(){
		$platform = PLATFORM;
		if($platform == ""){
			$platform = "test";
		}
		return $platform;
	}
	
	private static function getConfigFile($gameuid=null) {
		return 'default';
		if (!isset($gameuid) || intval($gameuid) < 0) return 'default';
		$config_file = APP_ROOT . '/etc_all/'.self::getPlatForm().'/user_partitions.ini';
//		self::checkConfigPath($config_file);
		$partition_config = self::getConfigFromCache($config_file);
		if (empty($partition_config)) {
			$partition_config = parse_ini_file($config_file, false);
			foreach ($partition_config as $k=>$v) {
				$gameuid_range = explode("-", $v);
				$min = intval($gameuid_range[0]);
				$max = intval($gameuid_range[1]);
				$partition_config[$k] = array($min, $max);
			}
			self::writeConfigToCache($config_file, $partition_config);
		}
		foreach ($partition_config as $k=>$v) {
			if ($gameuid >= $v[0] && $gameuid <= $v[1]) {
				return $k;
			}
		}
		return null;
	}
}

?>