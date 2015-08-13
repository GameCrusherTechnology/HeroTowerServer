<?php
require_once (FRAMEWORK . '/cache/Cache.class.php');
require_once (FRAMEWORK . '/cache/MemLocker.class.php');

class ActionLocker {
	/**
	 * @var MemLocker
	 */
	protected $locker = null;
	protected $action = null;
	protected $key_prefix = 'action_lock_';
	protected $uid = '';
	/**
	 * 构造函数
	 * @param AppConfig $app_config 应用程序的配置文件
	 * @param string $action 要锁定的动作
	 * @param string $unique_id 识别的唯一id
	 * @return ActionLocker
	 */
	public function __construct(AppConfig $app_config,$action,$unique_id = null){
		$this->uid = $unique_id;
		$cache = $app_config->getLockCacheInstance();
		$this->locker = new MemLocker($cache);
		$this->action = $action;
	}
	/**
	 * 设置锁定的缓存key的前缀
	 * @param $key_prefix the $key_prefix to set
	 */
	public function setKeyPrefix($key_prefix) {
		$this->key_prefix = $key_prefix;
	}

	/**
	 * @return the $key_prefix
	 */
	public function getKeyPrefix() {
		return $this->key_prefix;
	}
	
	/**
	 * 设置内存锁
	 * @param $expire
	 * @return bool
	 */
	public function setLocker($expire = 60){
		$key = $this->getKey();
		if($this->locker->isLocked($key)){
			throw new ActionException("Action $this->action locked.",1);
		}
		return $this->locker->setLock($key,$expire);
	}
	/**
	 * 释放设定的锁
	 * @return void
	 */
	public function releaseLocker(){
		$key = $this->getKey();
		$this->locker->releaseLock($key);
	}
	
	protected function getKey(){
		return $this->key_prefix . $this->uid . '_' . $this->action;
	}
}

?>