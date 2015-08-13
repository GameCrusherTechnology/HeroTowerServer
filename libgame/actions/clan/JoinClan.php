<?php
require_once GAMELIB.'/model/ClanManager.class.php';
require_once GAMELIB.'/model/UserClanManager.class.php';
require_once GAMELIB.'/model/UserAccountManager.class.php';
class JoinClan extends GameActionBase{
	protected function _exec()
	{
		$heroid = $this->getParam('heroid','int');
		$data_id = $this->getParam('data_id','int');
		
		$clan_mgr = new ClanManager();
		$user_clan_mgr = new UserClanManager();
		$clanInfo = $clan_mgr ->getSimClanInfo($data_id);
		
		$clanmembers= explode(",",$clanInfo['members']);
		
		$total = 20 + 10*$clanInfo['level'];
		
		$canJoin = FALSE;
		$membersArr = array();
		if((count($clanmembers)+1) > $total){
			$canJoin = FALSE;
		}else{
			$newUserClan = $user_clan_mgr->creatUserClan($heroid,$data_id);
			array_push($membersArr,$newUserClan);
			if (!$clanInfo['members'] ||$clanInfo['members']==""){
				$clanInfo['members'] = $heroid;
			}else{
				$clanInfo['members'] = $clanInfo['members'].",".$heroid;
			}
			$clan_mgr->updateClanInfo($data_id,$clanInfo);
			
			$clanInfo['clanUser']=$newUserClan;
			$canJoin = TRUE;
		}
		
		foreach ($clanmembers as $memberId){
			$simclaninfo = $user_clan_mgr->getUserClanInfo($memberId);
			if(!empty($simclaninfo)){
				array_push($membersArr,$simclaninfo);
			}
		}
		
		array_push($membersArr,$user_clan_mgr->getUserClanInfo($clanInfo['adminId']));
			
		$clanInfo['membersArr'] = $membersArr;
		return array('clan'=>$clanInfo,'canJoin'=>$canJoin);
	}
}
?>