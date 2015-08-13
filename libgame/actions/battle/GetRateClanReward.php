<?php
require_once GAMELIB.'/model/RatingClanManager.class.php';
class GetRateClanReward extends GameActionBase{
	protected function _exec()
	{
		$heroId = $this->getParam('heroid','int');
		$rank = $this->getParam('rank','int');
		
		$rateClanMgr = new RatingClanManager();
		$cachetime = $rateClanMgr->getRatingRewardTime($heroId);
		$newTime =  strtotime("next Monday");
		if(empty($cachetime)){
			$rateClanMgr->setRatingRewardTime($heroId,$newTime);
		}else{
			if ( $cachetime > time()){
				$this->throwException("hero $heroId has recieve hero reward");
			}else{
				$rateClanMgr->setRatingRewardTime($heroId,$newTime);
			}
		}
		if ($rank >0 && $rank<=100){
					
		}
		
		
		
		return array('rateClanTime'=>$newTime);
	}
}
?>