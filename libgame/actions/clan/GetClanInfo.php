<?php
require_once GAMELIB.'/model/ClanManager.class.php';
class GetClanInfo extends GameActionBase{
	protected function _exec()
	{
		$idArr = $this->getParam('idArr','array');
		
		$clan_mgr = new ClanManager();
		$result = array();
		foreach ($idArr as $key =>$clanId){
			$clanInfo = $clan_mgr->getClanInfo($clanId);
			if (!empty($clanInfo) ){
				array_push($result,$clanInfo);
			}
		}
		return array('clans'=>$result);
	}
}
?>