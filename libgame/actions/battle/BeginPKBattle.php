<?php
require_once GAMELIB.'/model/CharacterAccountManager.class.php';
require_once GAMELIB.'/model/BattleManager.class.php';
class BeginPKBattle extends GameActionBase{
	protected function _exec()
	{
		$enemyId = $this->getParam('enemyId','string');
		$characterId = $this->getParam('heroid','int');
		
		$hero_mgr = new CharacterAccountManager();
		$hero_info = $hero_mgr->getCharacterAccount($characterId);
		
		$vipLevel = StaticFunction::expToVip($hero_info['vip']);
		$vipDATA  = StaticFunction::$VipList[$vipLevel];
		
		$maxPkCOUNT = $vipDATA['pkAddCount'];
		
		$cacheInfo = $hero_mgr->getVipCache($characterId);
		$usedCount = 0;
		
		if (!empty($cacheInfo) && $cacheInfo['time'] >time()){
			$usedCount = $cacheInfo['pkAddCount'];
			$newCacheInfo = $cacheInfo;
		}else{
			$newCacheInfo['time'] = strtotime(date("Y-m-d",strtotime("+1 day")));
		}
		
		$newCacheInfo['pkAddCount'] = $usedCount+1;
		
		if( $maxPkCOUNT<=  $usedCount){
			$this->throwException("pk too much used $usedCount , total = $maxPkCOUNT");
		}else{
			
			$new_hero_info = StaticFunction::resetCurPower($hero_info);
			$cur_power = $new_hero_info['power'];
			
			if( $cur_power< StaticFunction::$power_battle_cost){
				$this->throwException("power is not enough ,cur power = $cur_power ");
			}else{
				$heroChange = array('power'=> ($cur_power - StaticFunction::$power_battle_cost),'powertime'=>$new_hero_info['powertime']);
				$hero_mgr->updateUserStatus($characterId,$heroChange);
			
				$hero_mgr->setVipCache($characterId,$newCacheInfo);
				
				$battleMgr = new BattleManager();
				$battleMgr ->setBattleCache($characterId,$enemyId,2,0);
				
				$heroChange['vipCache'] = $newCacheInfo;
				return $heroChange;
			}
		}
	}
}
?>