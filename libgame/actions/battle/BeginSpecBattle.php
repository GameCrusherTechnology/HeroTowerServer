<?php
require_once GAMELIB.'/model/CharacterAccountManager.class.php';
require_once GAMELIB.'/model/BattleManager.class.php';
require_once GAMELIB.'/model/UserGameItemManager.class.php';
class BeginSpecBattle extends GameActionBase{
	protected function _exec()
	{
		$specId = $this->getParam('specId','string');
		$battletype =  $this->getParam('battletype','string');
		$characterId = $this->getParam('heroid','int');
		$isHelp = $this->getParam('isHelp','bool');
		
		//power
		$hero_mgr = new CharacterAccountManager();
		$hero_info = $hero_mgr->getCharacterAccount($characterId);
		$new_hero_info = StaticFunction::resetCurPower($hero_info);
		$cur_power = $new_hero_info['power'];
		
		if( $cur_power< StaticFunction::$power_battle_cost){
			$this->throwException("power is not enough ,cur power = $cur_power ");
		}else{
			$heroChange = array('power'=> ($cur_power - StaticFunction::$power_battle_cost),'powertime'=>$new_hero_info['powertime']);
			$hero_mgr->updateUserStatus($characterId,$heroChange);
			
			$battleMgr = new BattleManager();
			$battleMgr ->setBattleCache($characterId,$specId,$battletype,0);
			
			if($isHelp === TRUE){
				$item_mgr = new UserGameItemManager($characterId);
				$item_mgr->subItem("80002",1);
				$item_mgr->checkReduceItemCount();
				$item_mgr->commitToDB();
			}
			
			return $heroChange;
		}
	}
}
?>