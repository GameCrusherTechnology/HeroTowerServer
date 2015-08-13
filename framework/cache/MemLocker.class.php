<?php

class MemLocker {
	/**
	 * @var Cache
	 */
	private $cache = null;
	public function __construct(Cache $cache){
		$this->cache = $cache;
	}
	
	public function isLocked($key){
		if($this->cache->get($key) === false){
			return false;
		}
		return true;
	}
	
	public function setLock($key,$expire = 60){
		return $this->cache->set($key,time(),intval($expire));
	}
	
	public function releaseLock($key){
		return $this->cache->delete($key);
	}
}

?>