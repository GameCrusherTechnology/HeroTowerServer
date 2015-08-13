<?php
require_once GAMELIB.'/model/RatingClanManager.class.php';
require_once GAMELIB.'/model/ClanBossManager.class.php';
class RefreshClanBoss extends GameActionBase{
	protected function _exec()
	{
		$gameuid = $this->getParam('gameuid','int');
		$clanID = $this->getParam('clanId','int');
		
		$bossMgr = new ClanBossManager();
		$boosInfo = $bossMgr->getCurClanBoss();
		
		$curTime = $boosInfo['Date'];
		$rateMgr = new RatingClanManager();
		$clanRateInfo = $rateMgr->getClanRate($clanID);
		
		if(empty($clanRateInfo)){
			$result = array('id'=>$clanID,'date'=>$curTime,'level'=>1,'kills'=>0);
			$rateMgr->addNewClanRate($clanID,$result);
		}else{
			if ($clanRateInfo['date'] == $curTime){
				$result = $clanRateInfo;
			}else{
				$result = array('id'=>$clanID,'date'=>$curTime,'level'=>1,'kills'=>0);
				$rateMgr ->updateClanRate($clanID,$result);
			}
		}
		
		return array('clanRateInfo'=>$result,'bossData'=>$boosInfo);
	}
}
?>