<?php
require_once GAMELIB.'/model/RatingHeroManager.class.php';
class FindPKEnemy extends GameActionBase{
	protected function _exec()
	{
		$characterId = $this->getParam('heroid','int');
		$score = $this->getParam('score','int');
		
		$hero_rate_mgr = new RatingHeroManager();
		$heros = $hero_rate_mgr->getHeros($score);
		return array("enemys"=>$heros);
	}
}
?>