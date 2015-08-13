<?php
class UserClanManager extends ManagerBase {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function creatUserClan($gameuid,$data_id)
	{
		$new_change = array("gameuid"=>$gameuid,"clan_id"=>$data_id);
		$this->insertDB($new_change);
		return $new_change;
	}
	
	public  function updateUserClan($gameuid,$change){
		$this->updateDB($gameuid,$change,array('gameuid'=>$gameuid));
	}
	public function getUserClanInfo($gameuid){
		$claninfo=$this->getClanByGameuid($gameuid);
		return $claninfo;
	}
	
	private function getClanByGameuid($gameuid){
		$key=array('gameuid'=>$gameuid);
		return $this->getFromDb($gameuid,$key);
	}
	
	
	public function deleteClanUser($gameuid)
	{
		$this->deleteFromDb($gameuid,array('gameuid'=>$gameuid));
	}
	
	protected function getTableName(){
		return "user_clan";
	}
	protected function  getKeyName()
    {
    	return "gameuid";
    }
}
?>