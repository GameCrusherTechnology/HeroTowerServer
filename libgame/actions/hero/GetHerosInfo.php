<?php
require_once GAMELIB.'/model/CharacterAccountManager.class.php';
require_once GAMELIB.'/model/UserClanManager.class.php';
require_once GAMELIB.'/model/ClanManager.class.php';
require_once GAMELIB.'/model/UserGameItemManager.class.php';
class GetHerosInfo extends GameActionBase{
	protected function _exec()
	{
		$heros = $this->getParam('heros','array');
		$needMore = $this->getParam('more','bool');
		
		$characterMgr = new CharacterAccountManager();
		$user_clan_mgr = new UserClanManager();
		$clan_mgr = new ClanManager();
		$result = array();
		foreach ($heros as $heroId){
			$heroInfo = $characterMgr->getCharacterAccount($heroId);
			
			//item 信息
			if($needMore === TRUE){
				$item_mgr = new UserGameItemManager($heroId);
				$items = $item_mgr->getItemList();
				$heroInfo['items'] = $items;
			}
		
			if(!empty($heroInfo)){
				$user_clan_info = $user_clan_mgr->getUserClanInfo($heroId);
				if (!empty($user_clan_info)){
					
					$clanInfo= $clan_mgr->getClanInfo($user_clan_info['clan_id']);
					$clanInfo['clanUser']=$user_clan_info;
					$heroInfo['clan'] = $clanInfo;
				}
				array_push($result,$heroInfo);
			}
		}
		
		return array("heros"=>$result);
	}
}
?>