<?php
require_once GAMELIB.'/model/CharacterAccountManager.class.php';
require_once GAMELIB.'/model/UserGameItemManager.class.php';
require_once GAMELIB.'/model/UserAccountManager.class.php';
class QuickPassBattle extends GameActionBase{
	protected function _exec()
	{
		$gameuid = $this->getParam('gameuid','int');
		$specId = $this->getParam('specId','string');
		$battletype =  $this->getParam('battletype','string');
		$heroId = $this->getParam('heroid','int');
		$rewards = $this->getParam('battlerewards','array');
		
		
		$itemMgr = new UserGameItemManager($heroId);
		
		$hero_mgr = new CharacterAccountManager();
		$hero_info = $hero_mgr->getCharacterAccount($heroId);
		
		$new_hero_info = StaticFunction::resetCurPower($hero_info);
		$totalPower = StaticFunction::getTotalPower(StaticFunction::expToGrade($hero_info['exp']),StaticFunction::expToVip($hero_info['vip']));
		$cur_power = $new_hero_info['power'];
		
		if( $cur_power< StaticFunction::$power_battle_cost){
			$this->throwException("power is not enough ,cur power = $cur_power , totalpower = $totalPower , oldpower = $heroId");
		}else{
			$heroChange = array('power'=> ($cur_power - StaticFunction::$power_battle_cost),'powertime'=>$new_hero_info['powertime']);
			
			if($battletype == "Ordinary"){
				$newItemId = $specId;
			}else{
				$newItemId = 100000 + intval($specId);
			}
			$itemInfo = $itemMgr->getItem($newItemId);
			if ($itemInfo['count']<3){
				$this->throwException("this battle spec id $newItemId do not have 3 star");
			}
			$itemMgr ->subItem("80003",1);
		
			foreach ($rewards as $oneReward){
				if($oneReward['item_id'] == 'coin'){
					$userMgr = new UserAccountManager();
					$userMgr->updateUserCoin($gameuid,$oneReward['count']);
				}else if($oneReward['item_id'] == 'exp'){
					$heroChange['exp'] = $oneReward['count'];
				}else {
					$itemMgr->addItem($oneReward['item_id'],$oneReward['count']);
				}
			}
			
			$itemMgr->commitToDB();
			
			$hero_mgr->updateUserStatus($heroId,$heroChange);
			return TRUE;
		}
		return FALSE;
	}
}
?>