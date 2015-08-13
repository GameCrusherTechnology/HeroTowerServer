<?php
require_once GAMELIB.'/model/RatingHeroManager.class.php';
class GetRateHeroReward extends GameActionBase{
	protected function _exec()
	{
		$heroId = $this->getParam('heroid','int');
		$rank = $this->getParam('rank','int');
		
		$rateHeroMgr = new RatingHeroManager();
		$cachetime = $rateHeroMgr->getRatingRewardTime($heroId);
		$newTime = strtotime("next Monday");
		
		if(empty($cachetime)){
			$rateHeroMgr->setRatingRewardTime($heroId,$newTime);
		}else{
			if ( $cachetime > time()){
				$this->throwException("hero $heroId has recieve hero reward");
			}else{
				$rateHeroMgr->setRatingRewardTime($heroId,$newTime);
			}
		}
		if ($rank >0 && $rank<=100){
					
		}
		
		
		
		return array('rateHeroTime'=>$newTime);
	}
}
?>