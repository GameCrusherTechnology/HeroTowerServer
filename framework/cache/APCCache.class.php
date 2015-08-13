<?php

class APCCache {
	/**
	 * 所以key的前缀
	 * @var string
	 */
	protected $prefix = '';

	/**
	 * @return string
	 */
	public function getPrefix() {
		return $this->prefix;
	}

	/**
	 * @param string $prefix
	 */
	public function setPrefix($prefix) {
		$this->prefix = $prefix;
	}

	/**
	 * 测试是否已经加载了APC模块
	 */
	public static function test(){
		return extension_loaded('APC');
	}
	
	/**
	 * 设置值到APC缓存
	 * @param string $key
	 * @param mixed $value
	 * @param int $expire 过期的时间
	 * @return bool
	 */
	public function set($key,$value,$expire = 0){
		return apc_store ($this->prefix . $key,$value,$expire);
	}
	/**
	 * 从缓存中取得一个值
	 * @param string $key
	 * @return mixed
	 */
	public function get($key){
		return apc_fetch ($this->prefix . $key);
	}
	
	/**
	 * 从APC缓存中删除一个key值
	 * @param string $key
	 * @return bool
	 */
	public function delete($key){
		return apc_delete($this->prefix .$key);
	}
	
	/**
	 * 清除user/system的APC缓存
	 * @param string $cache_type
	 * 如果设置为user缓存，那么所有的user缓存将被清除，
	 * 否则，所有的system的缓存将被清除
	 * @return bool
	 */
	public function flush($cache_type = 'user'){
		return apc_clear_cache ($cache_type);
	}
}

?>