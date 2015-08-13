<?php
require_once GAMELIB.'/model/CharacterAccountManager.class.php';
require_once GAMELIB.'/model/UserGameItemManager.class.php';
require_once GAMELIB.'/model/UserAccountManager.class.php';
require_once GAMELIB.'/model/BattleManager.class.php';
class GetBattleRewards extends GameActionBase{
	protected function _exec()
	{
		$gameuid = $this->getParam('gameuid','int');
		$specId = $this->getParam('specId','string');
		$battletype =  $this->getParam('battletype','string');
		$heroId = $this->getParam('heroid','int');
		$rewards = $this->getParam('battlerewards','array');
		$battleStar = $this->getParam('battleStar','int');
		
		$battleMgr = new BattleManager();
		$cacheInfo = $battleMgr->getBattleCache($heroId);
		if (!empty($cacheInfo)){
			$cacheInfoArr = explode(":",$cacheInfo);
			if ($cacheInfoArr[2] == 1){
				$this->throwException("battle has get rewards");
			}
		}
		$battleMgr->setBattleCache($heroId,$specId,$battletype,1);
		
		$heroMgr = new CharacterAccountManager();
		$itemMgr = new UserGameItemManager($heroId);
		
		foreach ($rewards as $oneReward){
			if($oneReward['item_id'] == 'coin'){
				$userMgr = new UserAccountManager();
				$userMgr->updateUserCoin($gameuid,$oneReward['count']);
			}else if($oneReward['item_id'] == 'exp'){
				$heroMgr->updateUserExperience($heroId,$oneReward['count']);
			}else {
				$itemMgr->addItem($oneReward['item_id'],$oneReward['count']);
				$itemMgr->commitToDB();
			}
		}
		//Elite Ordinary
		if ($battleStar >0 && $battleStar <= 3){
			if($battletype == "Ordinary"){
				$newItemId = $specId;
			}else{
				$newItemId = 100000 + intval($specId);
			}
			$itemInfo = $itemMgr->getItem($newItemId);
			if(empty($itemInfo)){
				$itemMgr->addItem($newItemId,$battleStar);
			}else{
				$oldC = $itemInfo['count'];
				if ($oldC <$battleStar){
					$itemMgr->addItem($newItemId,($battleStar-$oldC));
				}
			}
			$itemMgr ->commitToDB();
		}
		return TRUE;
	}
}
?>