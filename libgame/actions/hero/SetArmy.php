<?php
require_once GAMELIB.'/model/CharacterAccountManager.class.php';
require_once GAMELIB.'/model/UserGameItemManager.class.php';
class SetArmy extends GameActionBase{
	protected function _exec()
	{
		$characterId = $this->getParam('heroid','int');
		$armyArr = $this->getParam('army','array');
		
		$itemMgr = new UserGameItemManager($characterId);
		foreach ($armyArr as $soldierId){
			$soldier = $itemMgr->getItem($soldierId);
			if(empty($soldier) || $soldier['count']<=0){
				$this->throwException("hero $characterId do not have the soldier $soldierId");
			}
		}
		$new_soldier_str = implode(":",$armyArr);
		
		$characterMgr = new CharacterAccountManager();
		$characterMgr->updateUserStatus($characterId,array("soldiers"=>$new_soldier_str));
		
		return array("stats"=>TRUE);
	}
}
?>