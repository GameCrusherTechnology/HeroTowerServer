<?php
require_once FRAMEWORK . '/cache/Cache.class.php';
/**
 * 用于访问tokyoTyrant的类，需要libmemcached库和php的
 * Memcached扩展
 * @author shu shenglin
 *
 */
class Cached extends Cache{
	protected $persistent_id = 'cache_pool';

	public function __construct($host = null, $persistent_id = null){
		if(isset($host)){
			$this->host = $host;
		}
		if(isset($persistent_id[0])){
			$this->persistent_id = $persistent_id;
		}
	}
	
	/**
	 * 获取连接缓存使用的客户端实例
	 * @return Memcached
	 */
	protected function getMemcache() {
		if($this->cache === null){
			if(!extension_loaded('Memcached')){
				throw new Exception('Memcached extension not loaded.');
			}
			$this->cache = new Memcached($this->persistent_id);
			$this->cache->setOption(Memcached::OPT_NO_BLOCK,true);
		}
		return $this->cache;
	}
	/**
	 * 设置Memcached的客户端参数
	 * @param $option
	 * @param $value
	 */
	public function setOption($option, $value) {
		return $this->getMemcache()->setOption($option,$value);
	}

	/**
	 * 从缓存中获取一个或者多个key值
	 * @param $key
	 */
	protected function getValue($key) {
		if(is_array($key)){
			return $this->cache->getMulti($key);
		}else{
			return $this->cache->get($key);
		}
	}
	/**
	 * 替换缓存中的一个key值，如果该key不存在，返回false
	 * @param $key
	 * @param $value
	 * @param $expire
	 */
	public function replace($key, $value, $expire) {
		$this->connect();
		return $this->cache->replace($key,$value,$expire);
	}

	/**
	 * 往缓存中添加一个key值，如果该key已经存在，返回false
	 * @param $key
	 * @param $value
	 * @param $expire
	 */
	public function add($key, $value, $expire) {
		$this->connect();
		return $this->cache->add($key,$value,$expire);
	}
	/**
	 * @param $pairs
	 * @param $expire_time
	 * @param $with_prefix
	 */
	public function setMulti(array $pairs, $expire_time, $with_prefix) {
		$this->connect();
		return $this->cache->setMulti($pairs,$expire_time);
	}

	
	/**
	 * 设置一个值到缓存中
	 * @param $key
	 * @param $value
	 * @param $expire
	 */
	protected function setValue($key, $value, $expire = 3600) {
		return $this->cache->set($key,$value,$expire);
	}
	/**
	 * 获取操作的结果代码
	 * @return int
	 */
	public function getResultCode(){
		return $this->getMemcache()->getResultCode();
	}
	/**
	 * 获取操作的消息
	 * @return string
	 */
	public function getResultMessage(){
		return $this->getMemcache()->getResultMessage();
	}
	
	/**
	 * 测试是否加载了Memcached的PECL扩展
	 */
	public static function test() {
		return class_exists ( "Memcached" );
	}
}
