<?php
require_once GAMELIB.'/model/ClanManager.class.php';
class RefreshClanMessage extends GameActionBase{
	protected function _exec()
	{
		$heroid = $this->getParam('heroid','int');
		$clanId = $this->getParam('clanId','int');
		
		$clan_mgr = new ClanManager();
		$messages = $clan_mgr->getClanTalk($clanId);
		
		return array("messages"=>$messages);
	}
}
?>