<?php
require_once GAMELIB.'/model/UserGameItemManager.class.php';
require_once GAMELIB.'/model/CharacterAccountManager.class.php';
class AddEnergy extends GameActionBase{
	protected function _exec()
	{
		$itemId =  $this->getParam('itemId','int');
		$characterId = $this->getParam('heroid','int');
		
		$itemDef = get_xml_def($itemId);
		if (empty($itemDef))
		{
			$this->throwException("itemId is not item $itemId");
		}
		
		$itemMgr = new UserGameItemManager($characterId);
		$itemMgr->subItem($itemId,1);
		
		
		$hero_mgr = new CharacterAccountManager();
		$hero_account = $hero_mgr->getCharacterAccount($characterId);
		
		$new_hero_info = StaticFunction::resetCurPower($hero_account);
		$cur_power = $new_hero_info['power'];
		if ($itemId == "80000"){
			$heroChange = array('power'=> ($cur_power + 50),'powertime'=>$new_hero_info['powertime']);
		}else if($itemId == "80001"){
			$heroChange = array('power'=> StaticFunction::getTotalPower(StaticFunction::expToGrade($new_hero_info['exp']),StaticFunction::expToVip($new_hero_info['vip'])),'powertime'=>$new_hero_info['powertime']);
		}
		$hero_mgr->updateUserStatus($characterId,$heroChange);
		$itemMgr->commitToDB();
		
		$heroChange['item_id'] = $itemId;
		return $heroChange;
	}
}
?>