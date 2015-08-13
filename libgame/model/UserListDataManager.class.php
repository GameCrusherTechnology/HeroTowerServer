<?php
require_once GAMELIB.'/model/ManagerBase.class.php';
///**
// * 这个类主要是针对有data_id来构成主键的数据，每个玩家可能会有多条数据
// * 有user_data、user_data_animal、user_pet等数据
// * 如果要保证data_id在整个游戏中必须每个人的都不一样，请使用这种方式
// */
abstract class UserListDataManager extends ManagerBase {
	public function getList($gameuid) {
		if (empty($gameuid) || intval($gameuid) <= 0) return false;
		$table_name=$this->getTableName();
		//腾讯平台恢复错误key,已经上了很长时间，这次重启缓存之后肯定不会出现了，可以去掉
//		if($table_name == "user_plateform_gift1"){
//			$mem_list_key=sprintf($table_name."_list_%d",$gameuid);
//    		$this->deleteFromCache($mem_list_key,$gameuid);
//		}
		
    	$datas = $this->getFromDb($gameuid,array('gameuid'=>$gameuid),TCRequest::CACHE_KEY_LIST);
        return $datas;
	}
	public function get($gameuid,$data_id) {
		return $this->getFromDb($gameuid,array('data_id'=>$data_id));
	}
	public function update($gameuid, $data_id, $modify, $no_db=true) {
		$this->updateDB($gameuid, $modify, array('data_id'=>$data_id), $no_db);
	}
	public function insert($gameuid, $data) {
		$data['data_id']=$this->getSequenceId($this->getTableName(),'data_id');
		$data['gameuid'] = $gameuid;
		$this->completeInsertData($data);
		$this->insertDB($data,TCRequest::CACHE_KEY_LIST);
		return $data['data_id'];
	}
	public function delete($gameuid, $data_id) {
		$this->deleteFromDb($gameuid,array('data_id'=>$data_id));
	}
	
	protected function completeInsertData(&$data){}
}
/**
 * 这个类用来处理带有data_id但是是以data_id和gameuid为联合主键的玩家数据
 * 当前是这种数据的主要是user_scene和user_plantform_gift1
 * 这种方式主要是保证自己的数据中没有重复的data_id，不需要在整个游戏中都不重复
 * 如果没有必要必须保证data_id在整个游戏中唯一，请使用这种方式
 * 
 * 龙水中，2011-08-09
 */
abstract class UserAssocListDataManager extends ManagerBase {
	public function getList($gameuid) {
		if (empty($gameuid) || intval($gameuid) <= 0) return false;
    	$datas = $this->getFromDb($gameuid,array('gameuid'=>$gameuid),TCRequest::CACHE_KEY_LIST);
        return $datas;
	}
	public function getListCommitCache($gameuid) {
		if (empty($gameuid) || intval($gameuid) <= 0) return false;
		
    	$datas = $this->getFromDb($gameuid,array('gameuid'=>$gameuid),TCRequest::CACHE_KEY_LIST);
	}
	public function get($gameuid,$data_id){
		return $this->getFromDb($gameuid,array('gameuid'=>$gameuid,"data_id"=>$data_id));
	}
	public function delete($gameuid,$data_id){
		return $this->deleteFromDb($gameuid,array('gameuid'=>$gameuid,"data_id"=>$data_id));
	}
	public function update($gameuid, $data_id, $modify, $no_db=false) {
		$this->updateDB($gameuid, $modify, array('gameuid'=>$gameuid,"data_id"=>$data_id), $no_db);
	}
	public function insert($gameuid, $data) {
		$data['data_id']=$this->getSequenceId($gameuid);
		$data['gameuid'] = $gameuid;
		$this->completeInsertData($data);
		$this->insertDB($data,TCRequest::CACHE_KEY_LIST);
		return $data['data_id'];
	}
	
	public function insertField($gameuid, $data) {
		$this->insertDBBatch($data,TCRequest::CACHE_KEY_LIST);
	}
	////调用方法addKeyValue("pk1,pk2", array(array(v1,v2),array(v3,v4),array(v5,v6)));
	
	protected function getSequenceId($gameuid){
		$mem_key=sprintf("ranch_id_sequence_%s_%d",$this->getTableName(),$gameuid);
		$cache_mgr=$this->getCacheInstance($gameuid);
		$value=$cache_mgr->increment($mem_key);
		if ($value<1000){
			$list=$this->getList($gameuid);
			$max_id=0;
			foreach ($list as $scene){
				if ($max_id<$scene['data_id']) $max_id=$scene['data_id'];
			}
			if ($max_id<1000)	$max_id=1000;
			$cache_mgr->set($mem_key,$max_id,0);
			$value=$cache_mgr->increment($mem_key);
		}
		return $value;
	}
	
	protected function completeInsertData(&$data){}
}
?>