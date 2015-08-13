<?php
/**
 * Memcached 缓存的操作类
 *
 */
class Cache {
	protected $cache = null;
	/**
	 * 缓存的后备数据源
	 * @var Cache
	 */
	protected $super_server = null;
	/**
	 * 设置如果数据是从后备数据源取得的数据，是否删除源数据
	 * @var bool
	 */
	protected $delete_super_server_data = true;

	protected $host = "localhost";

	protected $key_prefix = '';
	/**
	 * 设置set时候的标志位
	 * @var int
	 */
	protected $flag = MEMCACHE_COMPRESSED;
	//todo 热扩容，需要支持读取ini文件里面配置的expire time
	protected $default_expire_time = 1209600;
	/**
	 * 日志操作对象
	 *
	 * @var ILogger
	 */
	protected $logger = null;

	public function __construct($host = null) {
		$this->host = $host;
		$this->logger = $GLOBALS['cache_logger'];
	}
	/**
	 * 获取配置的服务器列表
	 * @return array
	 */
	public function getServers(){
		return $this->host;
	}
	
	public function setConfig($host) {
		$this->host = $host;
	}
	/**
	 * 设置当前服务器的cache源服务器
	 * @param Cache $super_cache_server
	 * @return void
	 */
	public function setSuperServer(Cache $super_cache_server){
		$this->super_server = $super_cache_server;
	}
	/**
	 * 设置如果数据是从后备数据源取得的数据，是否删除源数据
	 * @param $delete_super_server_data the $delete_super_server_data to set
	 */
	public function setDeleteSuperServerData($value = true) {
		$this->delete_super_server_data = $value;
	}

	/**
	 * @return the $delete_super_server_data
	 */
	public function getDeleteSuperServerData() {
		return $this->delete_super_server_data;
	}

	/**
	 * @param $use_compressed the $use_compressed to set
	 */
	public function setFlag($flag) {
		$this->flag = $flag;
	}

	/**
	 * @return the $use_compressed
	 */
	public function getFlag() {
		return $this->flag;
	}
	/**
	 * 设置memcached客户端的option，只有在使用了memcached的客户端之后，该函数才起效
	 * @param int $option
	 * @param mixed $value
	 * @return bool
	 */
	public function setOption($option,$value){
		return false;
	}

	/**
	 * 设置缓存的前缀，以后所有的缓存key都会加上该key
	 * @param string $prefix
	 */
	public function setKeyPrefix($prefix = ''){
		$this->key_prefix = $prefix;
	}

	/**
	 * 获取key prefix
	 * @return string
	 */
	public function getKeyPrefix() {
		return $this->key_prefix;
	}
	/**
	 * 连接到缓存服务
	 * @return bool
	 */
	public function connect() {
		if ($this->cache === null) {
			$this->cache = $this->getMemcache();
			if (!is_array($this->host)) $this->host = array($this->host);
			foreach ($this->host as $server_address) {
				if(strpos($server_address,':') === false){
					$host = $server_address;
					$port = 11211;
				}
				else{
					$strs = explode ( ':', $server_address );
					$host = $strs [0];
					$port = $strs [1] ? $strs [1] : 11211;
				}
				$re = $this->cache->addServer($host, $port);
				if ($re === false) {
					$this->logger->writeFatal("failed to add memcache server $server_address");
				}
			}
			return $re;
		}
		return true;
	}
	/**
	 * 创建memcached客户端的对象
	 * @return Memcache
	 */
	protected function getMemcache(){
		if($this->cache === null){
			if(!extension_loaded('Memcache')){
				throw new Exception('Memcache extension not loaded.');
			}
			$this->cache = new Memcache();
		}
		return $this->cache;
	}
	
	public function close() {
		if ($this->cache != null) {
			$res = $this->cache->close ();
			if ($res === false) {
				$this->logger->writeError("failed to close memcache[".implode(',',$this->host)."].");
			}
			$this->cache = null;
		}
	}
	/**
	 * 往缓存中设置一个值
	 * @param $key 缓存key
	 * @param $value 对应的值
	 * @param $expire 过期时间
	 * @param $compressed 该参数已经不起作用，请使用setFlag指定
	 * @return bool
	 */
	public function add($key, $value, $expire = 60, $compressed = MEMCACHE_COMPRESSED) {
		$this->connect ();
		$res = $this->cache->add ( $this->key_prefix . $key, $value, $compressed, $expire );
		if ($res === false) {
			$this->logger->writeError("can not add value[key=$key,value=$value,expire=$expire] to memcache server[".implode(',',$this->host)."].");
		}
		if ($this->logger->isDebugEnabled()) {
			$this->logger->writeDebug("[add]key=$key,value=".var_export($value, true).",expire=$expire");
		}
		return $res;
	}
	
	/**
	 * 往缓存中设置一个值
	 * @param $key 缓存key
	 * @param $value 对应的值
	 * @param $expire 过期时间
	 * @param $compressed 该参数已经不起作用，请使用setFlag指定
	 * @return bool
	 */
	public function set($key, $value, $expire = 60, $compressed = MEMCACHE_COMPRESSED) {
		$this->connect ();
		$this->flag = $compressed;
		return $this->setValue( $this->key_prefix . $key, $value,$expire );
	}
	/**
	 * 替换缓存中的值，如果指定的key不存在，则返回flase
	 * @param $key 缓存key
	 * @param $value 对应的值
	 * @param $expire 过期时间
	 * @param $compressed 该参数已经不起作用，请使用setFlag指定
	 * @return bool
	 */
	public function replace($key, $value, $expire = 60, $compressed = MEMCACHE_COMPRESSED) {
		$this->connect ();
		$res = $this->cache->replace ( $key, $value, $compressed, $expire );
		if ($res === false) {
			$this->logger->writeError("can not replace value[key=$key,value=$value,expire=$expire] to memcache server[".implode(',',$this->host)."].");
		}
		if ($this->logger->isDebugEnabled()) {
			$this->logger->writeDebug("[replace]key=$key,value=".var_export($value, true).",expire=$expire");
		}
		return $res;
	}
	/**
	 * 从缓存中取得相应的key（可以是多个）对应的值
	 * @param $key
	 * @return mixed
	 */
	public function get($key) {
		$this->connect ();
		$key_is_array = is_array($key);
		if(!empty($this->key_prefix)){
			if($key_is_array){
				foreach ($key as &$k) {
					$k = $this->key_prefix . $k;
				}
			}
			else{
				$key = $this->key_prefix . $key;
			}
		}
		$arr = $this->getValue( $key );
		// 如果设定了缓存的源服务器，则从源服务器取得一次数据
		if(!is_null($this->super_server)){
			if($arr === false || $key_is_array && empty($arr)){
				$arr = $this->getFromSuperServer($key);
			}elseif($key_is_array && count($key) != count($arr)){
				$poll_keys = array_diff($key,array_keys($arr));
				$super_arr = $this->getFromSuperServer($poll_keys);
				if(!empty($super_arr)){
					$arr = array_merge($arr,$super_arr);
				}
			}
		}
		return $arr;
	}
	
	protected function getValue($key){
		return $this->cache->get ( $key );
	}
	/**
	 * 从缓存源取得数据
	 * @param mixed $key 一个key或者key的数组
	 * @return mixed 取得数据，如果不存在，则返回false
	 */
	protected function getFromSuperServer($key){
		$arr = $this->super_server->get($key);
		if($arr === false){
			return false;
		}
		// 取到数据，则设置到本地缓存
		if(is_array($key)){
			// 如果是数组的key，返回结果为空说明缓存中不存在指定的数据
			if(empty($arr)){
				return $arr;
			}
			$result = $this->setMulti($arr,$this->default_expire_time,true);
			// 如果需要，从远程服务器删除相应的key
			if($this->delete_super_server_data){
				// 使用memcached客户端，成功后返回true或者false
				if($result === true){
					foreach($key as $k){
						$this->super_server->delete($k);
					}
				}elseif(is_array($result)){
					foreach($result as $k => $v){
						if($v !== false){
							$this->super_server->delete($k);
						}
					}
				}
			}
		}else{
			if($this->setValue($key,$arr,$this->default_expire_time) !== false){
				// 已经设置到新的服务器上
				// 如果需要，从远程服务器删除相应的key
				if($this->delete_super_server_data){
					$this->super_server->delete($key);
				}
			}
		}
		return $arr;
	}
	/**
	 * 具体执行set操作的函数，可以通过重写该函数
	 * @param $key
	 * @param $value
	 * @param $expire
	 * @return bool
	 */
	protected function setValue($key,$value,$expire){
		$res = $this->cache->set($key,$value,$this->flag,$expire);
		if ($res === false) {
			$this->logger->writeError("can not set value[key=$key,value=$value,expire=$expire] to memcache server[".implode(',',$this->host)."].");
		}
		if ($this->logger->isDebugEnabled()) {
			$this->logger->writeDebug("[set]key=$key,value=".var_export($value, true).",expire=$expire");
		}
		return $res;
	}
	
	/**
	 * 设置默认的过期时间
	 * @param $t
	 * @return void
	 */
	public function setDefaultExpireTime($t = 3600){
		$this->default_expire_time = $t;
	}

	/**
	 * 设置多个值
	 * @param array $pairs 包含key-value的键值对
	 * @param $expire_time 这些数据的缓存时间
	 * @param $with_prefix 指定$pairs中的key是否已经包含了prefix，默认不包含
	 * @return array
	 */
	public function setMulti(array $pairs,$expire_time = 60,$with_prefix = true){
		$this->connect();
		$result = array();
		foreach($pairs as $key => $value){
			if($with_prefix){
				$result[$key] = $this->setValue($key,$value,$expire_time);
			}else{
				$result[$key] = $this->set($key,$value,$expire_time);
			}
		}
		return $result;
	}

	/**
	 * 使用原子操作增加一个key的值，如果原来的key不存在，则返回false
	 * @param $key 缓存key
	 * @param $value 需要增加的量
	 * @return bool
	 */
	public function increment($key, $value = 1) {
		$this->connect ();
		$re = $this->cache->increment ( $this->key_prefix . $key, $value );
		if($re === false){
			$re = $this->cache->add( $this->key_prefix . $key, $value, 0);
			if($re !== false){
				return $value;
			} else {
				$this->logger->writeError("failed to increase value[key=$key,value=$value] to memcache server[".implode(',',$this->host)."].");
			}
		}
		if ($this->logger->isDebugEnabled()) {
			$this->logger->writeDebug("[increment]key=$key,value=$value");
		}
		return $re;
	}
	/**
	 * 使用原子操作递减一个key的值，如果原来的key不存在，则返回false
	 * @param $key 缓存key
	 * @param $value 需要增加的量
	 * @return bool
	 */
	public function decrement($key, $value = 1) {
		$this->connect ();
		$re = $this->cache->decrement ($this->key_prefix . $key, $value );
		if($re === false){
			$re = $this->cache->add( $this->key_prefix . $key,$value,0);
			if($re !== false){
				return $value;
			} else {
				$this->logger->writeError("failed to decrease value[key=$key,value=$value] to memcache server[".implode(',',$this->host)."].");
			}
		}
		if ($this->logger->isDebugEnabled()) {
			$this->logger->writeDebug("[increment]key=$key,value=$value");
		}
		return $re;
	}

	/**
	 * 从缓存中删除一个key
	 * @param $key
	 * @return bool 成功返回true，失败返回false
	 */
	public function delete($key) {
		$this->connect ();
		// 如果$key不存在，也是返回false，所以没有必要记录log日志
		if ($this->logger->isDebugEnabled()) {
			$this->logger->writeDebug("[delete]key=$key");
		}
		return $this->cache->delete ($this->key_prefix . $key );
	}

	public function flush() {
		$this->connect ();
		$res = $this->cache->flush ();
		if ($res === false) {
			$this->logger->writeError("failed to flush memcache server[".implode(',',$this->host)."].");
		}
		if ($this->logger->isDebugEnabled()) {
			$this->logger->writeDebug("[flush]");
		}
		return $res;
	}

	public function isConnected() {
		return $this->connect ();
	}

	/**
	 * 检测memcache扩展是否已经加载
	 *
	 * @return boolean
	 */
	public static function test() {
		return class_exists ( "Memcache" );
	}
}
?>