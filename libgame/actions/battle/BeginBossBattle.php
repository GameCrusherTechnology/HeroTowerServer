<?php
require_once GAMELIB.'/model/BattleManager.class.php';
require_once GAMELIB.'/model/UserClanManager.class.php';
require_once GAMELIB.'/model/ClanBossManager.class.php';
require_once GAMELIB.'/model/RatingClanManager.class.php';
require_once GAMELIB.'/model/CharacterAccountManager.class.php';
class BeginBossBattle extends GameActionBase{
	protected function _exec()
	{
		$characterId = $this->getParam('heroid','int');
		$clanID = $this->getParam('clanId','int');
		
		$userClanMgr = new UserClanManager();
		$userclaninfo = $userClanMgr->getUserClanInfo($characterId);
		if (empty($userclaninfo ) || $userclaninfo['bossTime'] > time()){
			$this->throwException("hero has attack boss today ");
		}
		$bossMgr = new ClanBossManager();
		$boosInfo = $bossMgr->getCurClanBoss();
		$curTime = $boosInfo['Date'];
		
		//power
		$hero_mgr = new CharacterAccountManager();
		$hero_info = $hero_mgr->getCharacterAccount($characterId);
		$new_hero_info = StaticFunction::resetCurPower($hero_info);
		$cur_power = $new_hero_info['power'];
		
		if( $cur_power< StaticFunction::$power_battle_cost){
			$this->throwException("power is not enough ,cur power = $cur_power ");
		}else{
			$heroChange = array('power'=> ($cur_power - StaticFunction::$power_battle_cost),'powertime'=>$new_hero_info['powertime']);
			
			$rateMgr = new RatingClanManager();
			$clanRateInfo = $rateMgr->getClanRate($clanID);
			
			if(empty($clanRateInfo)){
				$newclanRate = array('id'=>$clanID,'date'=>$curTime,'level'=>1,'kills'=>0);
				$rateMgr->addNewClanRate($clanID,$newclanRate);
			}else{
				if ($clanRateInfo['date'] == $curTime){
					$newclanRate = $clanRateInfo;
				}else{
					$newclanRate = array('id'=>$clanID,'date'=>$curTime,'level'=>1,'kills'=>0);
					$rateMgr ->updateClanRate($clanID,$newclanRate);
				}
			}
			
			$battleMgr = new BattleManager();
			$battleMgr ->setBattleCache($characterId,$curTime,1,0);
			
			
			$hero_mgr->updateUserStatus($characterId,$heroChange);
			
			//bossTime
			$userclaninfo['bossTime'] = strtotime("next day");
			$userClanMgr->updateUserClan($userclaninfo);
			
			$heroChange['clanRateInfo'] = $newclanRate;
			$heroChange['bossData'] = $boosInfo;
			return $heroChange;
			
		}
		
		
		
			
	}
}
?>