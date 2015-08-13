<?php
require_once GAMELIB.'/model/ClanManager.class.php';
require_once GAMELIB.'/model/UserClanManager.class.php';
class SendClanMessage extends GameActionBase{
	protected function _exec()
	{
		$heroid = $this->getParam('heroid','int');
		$mes = $this->getParam('message','string');
		
		
		$str = $heroid."||".time()."||"."0"."||".$mes;
		$user_clan_mgr = new UserClanManager();
		$userClanInfo = $user_clan_mgr->getUserClanInfo($heroid);
		if (empty($userClanInfo)){
			$this->throwException("hero has no clan $heroid");
		}
		$clanId = $userClanInfo['clan_id'];
		
		$clan_mgr = new ClanManager();
		$clan_mgr->addClanTalk($clanId,$str);
		
		$newMessages = $clan_mgr->getClanTalk($clanId);
		return array("messages"=>$newMessages);
	}
}
?>