<?php
class UserActionCountManager extends ManagerBase {
	public function __construct(){
		parent::__construct();
	}
	public function updateActionCount($gameuid,$action_id,$count=1){
		$count=abs(intval($count));
		if($count<1) return false;
		$old_entry=$this->getEntry($gameuid,$action_id);
		if (empty($old_entry)){
			$this->insert($gameuid,$action_id,$count);
		}else {
			$new_count=$old_entry['count']+$count;
			$this->update($gameuid,$action_id,array('count'=>$new_count));
		}
		return true;
	}
	public function getCacheEntry($gameuid,$action_id){
		$key = $this->getTableName().'_'.$action_id.'_'.$gameuid;
		return $this->getFromCache($key,$gameuid);
	}
	//统计行为
	public function setAnalyticCacheEntry($gameuid,$action_id,$count){
		$key = $this->getTableName().'_'.'Analytic'.'_'.$action_id.'_'.$gameuid;
		$this->setToCache($key,$count,$gameuid,0);
	}
	public function getAnalyticCacheEntry($gameuid,$action_id){
		$key = $this->getTableName().'_'.'Analytic'.'_'.$action_id.'_'.$gameuid;
		return $this->getFromCache($key,$gameuid);
	}
	public function getEntry($gameuid,$action_id){
		return $this->getFromDb($gameuid,array('gameuid'=>$gameuid,'action_id'=>$action_id));
	}
	public function update($gameuid,$action_id,$data){
		$this->updateDB($gameuid,$data,array('gameuid'=>$gameuid,'action_id'=>$action_id));
	}
	public function insert($gameuid,$action_id,$count){
		$data=array('gameuid'=>$gameuid,"action_id"=>$action_id,"count"=>$count);
		$this->insertDB($data,TCRequest::CACHE_KEY_LIST);
	}
	public function getEntryList($gameuid){
		$list=$this->getFromDb($gameuid,array('gameuid'=>$gameuid),TCRequest::CACHE_KEY_LIST);
		return $list;
	}
	protected function getTableName(){
		return "user_action_count";
	}
}
?>