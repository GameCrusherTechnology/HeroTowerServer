<?php
/**
 * 提供数据库和缓存操作的类
 * @author shusl
 *
 */

class DataProvider {
	/**
	 *
	 * @var DBHelper
	 */
	protected $dbhelper = null;
	/**
	 * @var Cache
	 */
	protected $cache = null;
	
	public function __construct($dbhelper = null,Cache $cache = null){
		if($dbhelper){
			$this->setDbhelper($dbhelper);
		}
		if($cache){
			$this->cache = $cache;
		}
	}
	/**
	 * @return DBHelper the $dbhelper
	 */
	public function getDbhelper() {
		return $this->dbhelper;
	}

	/**
	 * @return Cache the $cache
	 */
	public function getCache() {
		return $this->cache;
	}

	/**
	 * @param $dbhelper the $dbhelper to set
	 */
	public function setDbhelper($dbhelper) {
		if($dbhelper instanceof DBHelper || $dbhelper instanceof DBHelper2){
			$this->dbhelper = $dbhelper;
		}else{
			throw new ElexException('dbhelper must be type of DBHelper or DBHelper2');
		}
	}

	/**
	 * @param $cache the $cache to set
	 */
	public function setCache(Cache $cache) {
		$this->cache = $cache;
	}
}

?>