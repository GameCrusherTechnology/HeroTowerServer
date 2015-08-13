<?php
require_once GAMELIB.'/model/CharacterAccountManager.class.php';
require_once GAMELIB.'/model/UserGameItemManager.class.php';
class SetSkill extends GameActionBase{
	protected function _exec()
	{
		$characterId = $this->getParam('heroid','int');
		$skillArr = $this->getParam('skill','array');
		
		$itemMgr = new UserGameItemManager($characterId);
		foreach ($skillArr as $skillId){
			$skill = $itemMgr->getItem($skillId);
			if(empty($skill) || $skill['count']<=0){
				$this->throwException("hero $characterId do not have the skill $skillId");
			}
		}
		$new_skill_str = implode(":",$skillArr);
		
		$characterMgr = new CharacterAccountManager();
		$characterMgr->updateUserStatus($characterId,array("skills"=>$new_skill_str));
		
		return array("stats"=>TRUE);
	}
}
?>