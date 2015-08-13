<?php
require_once GAMELIB.'/model/RatingHeroManager.class.php';
require_once GAMELIB.'/model/BattleManager.class.php';
class GetBossBattleReward extends GameActionBase{
	protected function _exec()
	{
		$enemyId = $this->getParam('enemyId','string');
		$battletype =  $this->getParam('battletype','string');
		$heroId = $this->getParam('heroid','int');
		$battleStar = $this->getParam('battleStar','int');
		
		$battleMgr = new BattleManager();
		$cacheInfo = $battleMgr->getBattleCache($heroId);
		if (!empty($cacheInfo)){
			$cacheInfoArr = explode(":",$cacheInfo);
			if ($cacheInfoArr[2] == 1){
				$this->throwException("battle has get rewards");
			}
		}
		$battleMgr->setBattleCache($heroId,$enemyId,$battletype,1);
		
		
		//Elite Ordinary
		if ($battleStar >0 && $battleStar <= 3){
			
			$rateHeroMgr = new RatingHeroManager();
			$enemyScore = $rateHeroMgr->getHeroRate($enemyId);
			$addScore = $battleStar * 10;
			$rateHeroMgr->addHeroRate($heroId,$addScore);
		
			return array("score"=>$addScore);
		}
		return FALSE;
	}
}
?>