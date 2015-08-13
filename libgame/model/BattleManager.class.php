<?php
class BattleManager extends ManagerBase {
	
	public function __construct(){
		parent::__construct();
	}
	
	
	public function setBattleCache($heroId,$specId,$type,$battleState){
		$this->setToCache($this->getBattleKeyName().$heroId,$specId.":".$type.":".$battleState,$heroId,86400);
	}
	public function getBattleCache($heroId){
		$this->getFromCache($this->getBattleKeyName().$heroId);
	}
	
	
	
	protected function getTableName(){
		return "clan_info";
	}
	protected function  getKeyName()
    {
    	return "data_id";
    }
    protected function getBattleKeyName(){
    	return "UserBattleId_";
    }
}
?>