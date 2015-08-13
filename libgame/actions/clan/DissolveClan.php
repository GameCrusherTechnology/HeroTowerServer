<?php
require_once GAMELIB.'/model/ClanManager.class.php';
class DissolveClan extends GameActionBase{
	protected function _exec()
	{
		$data_id = $this->getParam('data_id','int');
		$heroid = $this->getParam('heroid','int');
		
		$clan_mgr = new ClanManager();
		$clanInfo = $clan_mgr->getSimClanInfo($data_id);
		
		if (empty($clanInfo)){
			$this->throwException("there is no this clan $data_id");
		}
		
		if($clanInfo['adminId'] != $heroid){
			$this->throwException("user $heroid do not owned clan $data_id");
		}
		$clan_mgr ->deleteClan($data_id);
		
		return array('result'=>TRUE);
	}
}
?>