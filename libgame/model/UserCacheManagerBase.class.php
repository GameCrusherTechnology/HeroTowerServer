<?php
include_once GAMELIB.'/model/ManagerBase.class.php';
abstract class UserCacheManagerBase extends ManagerBase {
	/*
	 * 用户的gameuid
	 */
	protected $gameuid=0;
	/*
	 * 缓存处理类
	 */
	protected $cache_helper=null;
	/*
	 * cache_time 是一个时间戳,利用的是缓存的自动过期的功能避免无用数据占用资源
	 */
	protected $cache_time=0;
	public function __construct($gameuid){
		parent::__construct();
		if (empty($gameuid)||$gameuid<=0){
			$this->throwException("gameuid[$gameuid] error",GameStatusCode::USER_NOT_EXISTS);
		}
		$this->gameuid=$gameuid;
		$this->setCacheTime();
		$this->cache_helper=$this->getCacheInstance($this->gameuid);
	}
	/*
	 * 设置缓存时间，默认为0，表示不会过期
	 */
	protected function setCacheTime(){
		$this->cache_time=0;
	}
	/*
	 * 获取缓存中的数据
	 */
	protected function getCacheInfo(){
		$mem_key=$this->getMemKey();
		$value=$this->cache_helper->get($mem_key);
		return $this->dealWithValue($value);
	}
	/*
	 * 向缓存中添加数据
	 * param value:数据在缓存中的存储值
	 */
	protected function setCacheInfo($value){
		$mem_key=$this->getMemKey();
		$this->cache_helper->set($mem_key,$value,$this->cache_time);
	}
	/*
	 * 删除数据
	 */
	protected function deleteCacheInfo(){
		$mem_key=$this->getMemKey();
		$this->cache_helper->delete($mem_key);
	}
	/*
	 * 处理一下缓存中的数据返回给程序使用，需要根据不同的数据进行单独处理，默认为原样返回
	 */
	protected function dealWithValue($value){
		return $value;
	}
	/*
	 * 获取表名,默认为default_cache_table,在配置文件中没有相关配置时使用默认的缓存服务器
	 */
	protected function getTableName(){
		return "default_cache_table";
	}
	/*
	 * 获取缓存key的方法，这个需要每个子类复写之后才能使用
	 */
	abstract protected function getMemKey();
}
?>