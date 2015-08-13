<?php
require_once GAMELIB.'/model/ClanManager.class.php';
require_once GAMELIB.'/model/UserClanManager.class.php';
class UserLoginClan extends GameActionBase{
	protected function _exec()
	{
		$heroid = $this->getParam('heroid','int');
		
		$user_clan_mgr = new UserClanManager();
		$user_clan_info = $user_clan_mgr->getUserClanInfo($heroid);
		if (!empty($user_clan_info)){
			$clan_mgr = new ClanManager();
			$clan_info = $clan_mgr->getClanInfo($user_clan_info['clan_id']);
			$membersArr = array();
			$clanmembers= explode(",",$clan_info['members']);
			foreach ($clanmembers as $memberId){
				$simclaninfo = $user_clan_mgr->getUserClanInfo($memberId);
				if(!empty($simclaninfo)){
					array_push($membersArr,$simclaninfo);
				}
			}
			array_push($membersArr,$user_clan_mgr->getUserClanInfo($clan_info['adminId']));
			
			$clan_info['membersArr'] = $membersArr;
			
			$clanTalk = $clan_mgr->getClanTalk($user_clan_info['clan_id']);
			$clan_info['talk'] = $clanTalk;
		}
			
		return array('clan'=>$clan_info,'clanUser'=>$user_clan_info);
	}
}
?>