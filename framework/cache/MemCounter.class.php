<?php

class MemCounter {
	/**
	 * @var Cache
	 */
	private $cache = null;
	public function __construct(Cache $cache){
		$this->cache = $cache;
	}
	
	public function increateCount($key,$inc = 1){
		$result = $this->cache->increment($key,$inc);
		if($result === false){
			if( $this->cache->set($key,$inc,0) === false){
				return false;
			}
			else {
				return $inc;
			}
		}
		return $result;
	}
	
	public function decreateCount($key,$inc = 1){
		return $this->cache->decrement($key,$inc);
	}
	
	public function deleteCounter($key){
		return $this->cache->delete($key);
	}
	
	public function getCount($key){
		return $this->cache->get($key);
	}
	
	public function reset($key){
		return $this->cache->set($key,0,0);
	}
}

?>