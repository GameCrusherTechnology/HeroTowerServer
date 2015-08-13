<?php

class ElexConfig {

	/**
	 * 保存单例的 对象实例
	 * @var ElexConfig
	 */
	protected static $instance = null;
	/**
	 * 对象缓存的时候，是否缓存到APC中
	 * @var bool
	 */
	protected static $use_apc_cache = true;
	
	protected $config_file;
	
	protected $config = null;
	/**
	 * 设置为私有的克隆函数，防止因为使用clone而产生另一个实例
	 * @return void
	 */
	private function __clone(){}
	
	protected function __construct($config_file,$options = null) {
		$this->config_file = $config_file;
		$this->init($options);
	}
	
	protected function init($options){
		if(is_array($options) && array_key_exists('process_sections',$options)){
			$this->config = parse_ini_file($this->config_file,$options['process_sections']);
		}else{
			$this->config = parse_ini_file($this->config_file,true);
		}
	}
	
	/**
	 * 抛出一个ConfigException实例
	 * @param $msg
	 * @param $code
	 * @return void
	 */
	protected static function throwException($msg,$code = 1){
		throw new ConfigException($msg,$code);
	}
	/**
	 * 创建ElexConfig的实例
	 * @param $config_file
	 * @param $options
	 * @return ElexConfig
	 */
	public static function getInstance($config_file,$options = null){
		if(! self::$instance instanceof self){
			self::$use_apc_cache = function_exists('apc_fetch');
			// 验证配置文件路径的正确性
			self::checkConfigPath($config_file);
			// 先尝试从缓存中反序列化
			self::$instance = self::getConfigFromCache($config_file);
			if(empty(self::$instance)){
				// 创建一个新的ElexConfig对象
				self::$instance = new self($config_file,$options);
				// 将对象序列化后写入缓存
				self::writeConfigToCache($config_file, self::$instance);
			}
		}
		return self::$instance;
	}

	protected static function checkConfigPath($path){
		if (preg_match('/[^a-z0-9\\/\\\\_.:- ]/i', $path)) {
            self::throwException('Security check: Illegal character in filename');
        }
        if(!file_exists($path)){
        	self::throwException('config path error:' . $path);
		}
	}
	/**
	 * 获取config的值
	 * @param array $config config数组
	 * @param $key
	 * @param $default
	 * @param $type
	 * @return mixed
	 */
	protected function getValue(array $config,$key,$default,$type = 'int'){
		if(isset($config[$key])){
			if($type == 'bool'){
				if(!isset($config[$key]) || $config[$key] == 'false' || !$config[$key]){
					return false;
				}
				return true;
			}elseif($type == 'array'){
				return (array)$config[$key];
			}
			$num = intval($config[$key]);
			if($num < 1){
				$num = $default;
			}
			return $num;
		}
		return $default;
	}
	
	/**
	 * 尝试从缓存中反序列化得到配置实例
	 * @param $cache_file 序列化的缓存文件
	 * @return 相关的配置信息
	 */
	protected static function getConfigFromCache($config_file) {
		$cache_file = $config_file . '.dat';
		// 如果缓存文件存在，并且修改时间比配置文件的修改时间晚
		if(file_exists($cache_file) &&
			filemtime($cache_file) > filemtime($config_file)){
			if(self::$use_apc_cache){
				$key = md5($cache_file);
				// 从apc缓存中取对象的序列化结果
				$sc = apc_fetch($key);
				if($sc !== false){
					return unserialize($sc);
				}
			}
			// 从文件缓存取得序列化的对象
			$sc = file_get_contents($cache_file);
			if($sc !== false){
				if(self::$use_apc_cache){
					apc_store($key,$sc);
				}
				return unserialize($sc);
			}
		}
		return null;
	}
	/**
	 * 将序列化的结果写入到文件和apc缓存中
	 * @param $config_file
	 * @param $config
	 * @return void
	 */
	protected static function writeConfigToCache($config_file, $config) {
		$cache_file = $config_file . '.dat';
		$sc = serialize($config);
		// 把该对象序列化后保存到缓存文件中
		if(file_put_contents($cache_file,$sc,LOCK_EX) === false){
			trigger_error('Write config cache file failure.',E_USER_WARNING);
		}
		if(self::$use_apc_cache){
			// 写入到apc缓存中
			apc_store(md5($cache_file),$sc);
		}
	}
}

class ConfigException extends Exception{
	
}
?>