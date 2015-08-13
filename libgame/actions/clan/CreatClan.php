<?php
require_once GAMELIB.'/model/ClanManager.class.php';
require_once GAMELIB.'/model/UserClanManager.class.php';
require_once GAMELIB.'/model/UserAccountManager.class.php';
require_once GAMELIB.'/model/CharacterAccountManager.class.php';
class CreatClan extends GameActionBase{
	protected function _exec()
	{
		$gameuid = $this->getParam('gameuid','int');
		$heroid = $this->getParam('heroid','int');
		$name = $this->getParam('name','string');
		
		$user_clan_mgr = new UserClanManager();
		$clanInfo = $user_clan_mgr->getUserClanInfo($heroid);
		if(!empty($clanInfo)){
			$this->throwException("user $heroid already have clan ");
		}
		
		$account_mgr = new UserAccountManager();
		$account_info = $account_mgr ->getUserAccount($gameuid);
		if($account_info['gem'] < 20){
			$this->throwException("user $gameuid not enough gem to buy Clan ");
		}
		$clan_mgr = new ClanManager();
		$result = $clan_mgr->creatClan($heroid,$name);
		
		$user_clan_info = $user_clan_mgr->creatUserClan($heroid,$result['data_id']);
		
		$account_mgr->updateUserMoney($gameuid,20);
		
		$hero_mgr = new CharacterAccountManager();
		$result['admin'] = $hero_mgr->getCharacterAccount($heroid);
		$result['membersArr']=array($user_clan_info);
		
		return array('clan'=>$result);
	}
}
?>