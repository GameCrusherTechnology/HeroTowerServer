<?php
require_once GAMELIB.'/model/UserGameItemManager.class.php';
require_once GAMELIB.'/model/CharacterAccountManager.class.php';
require_once GAMELIB.'/model/RatingHeroManager.class.php';
require_once GAMELIB.'/model/RatingClanManager.class.php';
require_once GAMELIB.'/model/UserClanManager.class.php';
require_once GAMELIB.'/model/ClanBossManager.class.php';
class HeroLoginCommand extends GameActionBase{
	protected function _exec()
	{
		$characteruid = $this->getParam('characteruid','string');
		
		$result = array();
		
		//item 信息
		$item_mgr = new UserGameItemManager($characteruid);
		$items = $item_mgr->getItemList();
		$heroInfo['items'] = $items;
		
		//vip
		$vip_mgr = new CharacterAccountManager();
		$vipInfo = $vip_mgr->getVipCache($characteruid);
		$heroInfo['vipData'] = $vipInfo;
		
		//rate
		$rate_mgr = new RatingHeroManager();
		$heroInfo['heroRate'] = $rate_mgr->getHeroRate($characteruid);
		$result['topHeroRate'] = $rate_mgr->getTop();
		$heroInfo['heroRateTime'] = $rate_mgr->getRatingRewardTime($characteruid);
		
		$clan_rate_mgr = new RatingClanManager();
		$result['topClanRate'] = $clan_rate_mgr->getTop();
		$heroInfo['clanRateTime'] = $clan_rate_mgr->getRatingRewardTime($characteruid);
		
		$bossMgr= new ClanBossManager();
		$heroInfo['bossData']= $bossMgr->getCurClanBoss();
		//clan
		
		$user_clan_mgr = new UserClanManager();
		$user_clan_info = $user_clan_mgr->getUserClanInfo($characteruid);
		if (!empty($user_clan_info)){
			require_once GAMELIB.'/model/ClanManager.class.php';
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
			if(!empty($clanTalk)){
				$clan_info['talk'] = $clanTalk;
			}
			
			
			$heroInfo['clanRate'] = $clan_rate_mgr->getClanRate($user_clan_info['clan_id']);
			
			$clan_info['clanUser']=$user_clan_info;
			$heroInfo['clan']=$clan_info;
		}
			
		$result['hero_info'] = $heroInfo;
		return $result;
	}
}
?>