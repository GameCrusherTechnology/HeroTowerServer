<?php
require_once FRAMEWORK . '/cache/Cache.class.php';
/**
 * 用于访问tokyoTyrant的类，需要libmemcached库和php的
 * Memcached扩展
 * @author shu shenglin
 *
 */
class CacheTokyoTyrant extends Cache{
	protected $persistent_id = 'cache_pool';
	/**
	 * 压缩flag 常量
	 * @var int
	 */
	const COMPRESSED = 1;
	/**
	 * 数据的长度大于该值时候，启用压缩
	 * @var int
	 */
	protected $compress_threshold = 100;
	
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
			$this->cache->setOption(Memcached::OPT_COMPRESSION,false);
		}
		return $this->cache;
	}
	/**
	 * @param $option
	 * @param $value
	 */
	public function setOption($option, $value) {
		return $this->getMemcache()->setOption($option,$value);
	}

	/**
	 * @param $key
	 */
	protected function getValue($key) {
		if(is_array($key)){
			$values = $this->cache->getMulti($key);
		}else{
			$values = $this->cache->get($key);
		}
		if(empty($values)){
			return $values;
		}
		$now = time();
		if(is_array($key)){
			$result = array();
			foreach($values as $k => $value){
				$d = $this->getData($value,$now);
				if($d !== false){
					$result[$k] = $d;
				}
			}
			return $result;
		}else{
			return $this->getData($values,$now);
		}
	}

	/**
	 * 取得从缓存中取得的数据
	 * @param $saved_value
	 * @param $now
	 * @return mixed
	 */
	protected function getData($saved_value,$now){
		if(!is_array($saved_value)){
			$saved_value = unserialize($saved_value);
		}
		$expire_time = $saved_value[3];
		if($expire_time < 0){
			return false;
		}elseif($expire_time > 0 && $expire_time <= 2592000){
			// 如果expire time是通过时间片段设置的，则加上操作的时间
			$expire_time += $saved_value[2];
			// 处理数据过期
			if($expire_time < $now){
				return false;
			}
		}
		$data = $saved_value[0];
		// 如果数据被压缩过了
		if($saved_value[1] == self::COMPRESSED){
			$data = gzuncompress($data);
		}
		return unserialize($data);
	}
	/**
	 * @param $key
	 * @param $value
	 * @param $expire
	 * @param $compressed
	 */
	public function replace($key, $value, $expire) {
		$this->connect();
		$data = $this->getSaveData($value,$expire);
		return $this->cache->replace($key,$data,$expire);
	}

	/**
	 * @param $key
	 * @param $value
	 * @param $expire
	 * @param $compressed
	 */
	public function add($key, $value, $expire) {
		$this->connect();
		$data = $this->getSaveData($value,$expire);
		return $this->cache->add($key,$data,$expire);
	}
	
	/**
	 * @param $key
	 * @param $value
	 * @param $expire
	 */
	protected function setValue($key, $value, $expire = 3600) {
		$data = $this->getSaveData($value,$expire);
		return $this->cache->set($key,$data,$expire);
	}
	/**
	 * 获取要保存的数据
	 * @param $value
	 * @param $expire
	 * @return array
	 */
	protected function getSaveData($value,$expire){
		$flag = 0;
		$str = serialize($value);
		// 如果大于一定长度，则进行压缩保存
		if(isset($str[$this->compress_threshold])){
			$str = gzcompress($str,9);
			$flag = self::COMPRESSED;
		}
		return array($str,$flag,time(),$expire);
	}
	/**
	 * @param $pairs
	 * @param $expire_time
	 * @param $with_prefix
	 */
	public function setMulti($pairs, $expire_time, $with_prefix = true) {
		$this->connect();
		if(!$with_prefix && isset($this->key_prefix[0])){
			// key prefix存在，并且需要设置的key没有prefix
			$new_pairs = array();
			foreach ($pairs as $key => $value){
				$new_pairs[$this->key_prefix . $key] = $this->getSaveData($value,$expire_time);
			}
		}else{
			$new_pairs = array();
			foreach ($pairs as $key => $value){
				$new_pairs[$key] = $this->getSaveData($value,$expire_time);
			}
		}
		return $this->cache->setMulti($new_pairs,$expire_time);
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
