<?php
require_once GAMELIB.'/model/ClanManager.class.php';
require_once GAMELIB.'/model/UserClanManager.class.php';
class SignOutClan extends GameActionBase{
	protected function _exec()
	{
		$data_id = $this->getParam('data_id','int');
		$heroid = $this->getParam('heroid','int');
		
		$clan_mgr = new ClanManager();
		$clanInfo = $clan_mgr->getSimClanInfo($data_id);
		if (empty($clanInfo)){
			$this->throwException("there is no this clan $data_id");
		}
		
		if($clanInfo['adminId'] == $heroid){
			$this->throwException("user $heroid can not sign out clan $data_id");
		}
		
		$user_mgr = new UserClanManager();
		$user_mgr->deleteClanUser($heroid);
		
		$memberArr = explode(",",$clanInfo['members']);
		foreach ($memberArr as $key=>$memberId){
			if($memberId == $heroid){
				unset($memberArr[$key]);
			}
		}
		$clanInfo['members'] = implode(",",$memberArr);
		$clan_mgr->updateClanInfo($data_id,$clanInfo);
		
		
		return array('result'=>TRUE);
	}
}
?>