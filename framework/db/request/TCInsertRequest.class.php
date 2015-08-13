<?php
/**
 * 用来执行insert操作的request
 * @author shusl
 *
 */
class TCInsertRequest extends TCRequest{
	public function execute() {
		$this->_exec();
		if ($this->logger->isDebugEnabled()) {
			$this->logger->writeDebug($this->affected_rows."rows were affected by insert operation.");
		}
		$this->updateCache();
		return $this->affected_rows;
	}
	
	protected function updateCache(){
		if ($this->no_cache) return;
		// 将新的item插入到缓存里边
		$columns = explode(',',$this->getColumns());
		foreach($this->values as $val){
			$val = $this->getKeyValue($columns,$val);
			$this->completeRow($val);
			$this->setRowToCache($val);
		}
		// 如果是key list类型的缓存，则更新key list
		if($this->cache_type == self::CACHE_KEY_LIST){
			$this->updateKeyListCache();
		}
	}
	
	protected function _exec(){
		$sql = $this->getSQL();
		if(empty($sql)){
			return false;
		}
		$db = $this->getTableServer()->getDBHelperInstance();
		$db->executeNonQuery($sql);
		$this->affected_rows = $db->affectedRows();
		return true;
	}
	
	protected function getSQL(){
		if(empty($this->values)){
			return false;
		}
		$sql = sprintf('INSERT INTO %s (%s) values', $this->getTableServer()->getTableName(), $this->getColumns());
		$columns_as_array = explode(',',$this->getColumns());
		$packed_fields = $this->getTableServer()->getPackFields();
		foreach ($this->values as $values){
			if(is_array($values)){
				$values_sql = '';
				foreach ($values as $key=>$value) {
					if (isset($values_sql[0])) $values_sql .= ',';
					if (in_array($columns_as_array[$key], array_keys($packed_fields))) {
						$value = $this->packField(
							$packed_fields[$columns_as_array[$key]]['sub_fields'], 
							$value);
					}
					$values_sql .= $this->prepareForSql($columns_as_array[$key], $value);
				}
				$sql .= "($values_sql),";
			}else{
				if (in_array($columns_as_array[0], array_keys($packed_fields))) {
					$value = $this->packField(
						$packed_fields[$columns_as_array[0]]['sub_fields'], 
						$values);
				}
				$sql .= '(' . $this->prepareForSql($columns_as_array[0], $values) . '),';
			}
		}
		return rtrim($sql,',');
	}
	
	protected function updateKeyListCache(){
		$list_key = $this->getListCacheKey();
		$cache = $this->getCacheInstance();
		$value = $cache->get($list_key);
		$new_val = $this->getNewKeyListValue();
		// 如果list_cache没有在缓存里，直接返回了。下次直接从数据库取
		if ($value === false) return;
		if(!empty($value)){
			$value = $this->mergeKeyList($value, $new_val);
		}else{
			$value = $new_val;
		}
		// list的缓存保存为不过期
		if ($this->logger->isDebugEnabled()) {
			$this->logger->writeDebug("[TCInsertRequest.updateKeyListCache]tring to modify list cache[key=$list_key,value=".print_r($value, true)."]");
		}
		$cache->set($list_key, $value, 0);
	}
	
	protected function getNewKeyListValue(){
		$list = array();
		$pk_key = $this->getPrimaryKey();
		$columns = explode(',',$this->getColumns());
		foreach($this->values as $val){
			$val = $this->getKeyValue($columns,$val);
			$l = array_intersect_key($val,$pk_key);
			if(!empty($l)){
				$list[] = $l;
			}
		}
		return $list;
	}
	protected function getKeyValue($columns,$value){
		if(!is_array($value)){
			$value = explode(',',$value);
		}
		return array_combine($columns,$value);
	}
	public function addValues($values){
		if(!empty($values)){
			$this->values[] = $values;
		}
	}
}

?>
