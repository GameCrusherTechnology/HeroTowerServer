<?php
require_once GAMELIB.'/model/ClanManager.class.php';
class GetClanList extends GameActionBase{
	protected function _exec()
	{
		
		$clan_mgr = new ClanManager();
		$result = $clan_mgr->getClans();
		
		return array('clans'=>$result);
	}
}
?>