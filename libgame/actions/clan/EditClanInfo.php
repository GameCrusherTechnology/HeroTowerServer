<?php
require_once GAMELIB.'/model/ClanManager.class.php';
class EditClanInfo extends GameActionBase{
	protected function _exec()
	{
		$data_id = $this->getParam('data_id','int');
		$newMessage = $this->getParam('clanInfo','string');
		$heroid = $this->getParam('heroid','int');
		
		$clan_mgr = new ClanManager();
		$clanInfo = $clan_mgr->getSimClanInfo($data_id);
		if (empty($clanInfo)){
			$this->throwException("there is no this clan $data_id");
		}
		
		if($clanInfo['adminId'] != $heroid){
			$this->throwException("user $heroid do not have clan $data_id");
		}
		$clanInfo['clanMessage'] = $newMessage;
		$clan_mgr ->updateClanInfo($data_id,$clanInfo);
		
		return array('result'=>TRUE);
	}
}
?>