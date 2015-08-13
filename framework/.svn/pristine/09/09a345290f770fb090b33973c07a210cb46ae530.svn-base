<?php

class ModelBase {
	/**
	 * 数据库操作类
	 * @var DBHelper
	 */
	protected $dbhelper;
	
	/**
	 * Memcache缓存操作类
	 * @var Cache
	 */
	protected $cache;
	
	/**
	 * 该model所操作的表的名称
	 *
	 * @var string
	 */
	protected $table_name;

	/**
	 * @param DBHelper $dbhelper
	 */
	public function setDBHelper($dbhelper) {
		$this->dbhelper = $dbhelper;
	}
	
	/**
	 * @return DBHelper
	 */
	public function getDbhelper() {
		return $this->dbhelper;
	}
	
	/**
	 * @return Cache
	 */
	public function getCache() {
		return $this->cache;
	}

	/**
	 * @param Cache $cache
	 */
	public function setCache(Cache $cache) {
		$this->cache = $cache;
	}

	/**
	 * 抛出异常
	 */
	protected function throwException($message,$code){
		throw new Exception($message,$code);
	}
	
	/**
	 * 根据指定的key值数值，从一个参数数组中取值
	 * @param array $param_keys
	 * @param array $params
	 * @return array
	 */
	protected function getParams($param_keys,$params){
		$result = array();
		if(is_array($param_keys)){
			foreach ($param_keys as $key) {
				$result[$key] = isset($params[$key]) ? $params[$key] : '';
			}
		}elseif(is_string($param_keys)){
			$result[$key] = $params[$key];
		}
		return $result;
	}
	
	/**
	 * 根据指定的key值，取得在提供的参数中存在的参数
	 * @param array $param_keys
	 * @param array $params
	 * @return array
	 */
	protected function getExistParams($param_keys,$params){
		return array_intersect_key($param_keys,$params);
	}
}

?>