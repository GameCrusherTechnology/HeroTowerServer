<?php
require_once GAMELIB.'/model/UserGameItemManager.class.php';
require_once GAMELIB.'/model/UserAccountManager.class.php';
class BuyItem extends GameActionBase{
	protected function _exec()
	{
		$gameuid = $this->getParam('gameuid','int');
		$itemId =  $this->getParam('itemId','int');
		$count =  $this->getParam('count','int');
		$useGem =  $this->getParam('useGem','bool');
		$characterId = $this->getParam('heroid','int');
		
		$itemDef = get_xml_def($itemId);
		if (empty($itemDef))
		{
			$this->throwException("itemId is not item $itemId");
		}
		$cost = 0;
		if ($useGem){
			$cost = $itemDef['gem']*$count;
		}else{
			$cost = $itemDef['coin']*$count;
		}
		if ($cost<=0){
			$this->throwException("itemId is no cost  useGem $useGem : $cost");
		}
		
		$itemMgr = new UserGameItemManager($characterId);
		$itemMgr->addItem($itemId,$count);
		
		
		$user_mgr = new UserAccountManager();
		$user_account = $user_mgr->getUserAccount($gameuid);
		
		if ($useGem){
			if ($user_account['gem']< $cost){
				$this->throwException("character $gameuid not enough gem $cost");
			}
			$user_mgr->updateUserMoney($gameuid,-$cost);
		}else{
			if ($user_account['coin']< $cost){
				$this->throwException("character $gameuid not enough coin $cost");
			}
			$user_mgr->updateUserCoin($gameuid,-$cost);
		}
		$itemMgr->commitToDB();
		
		return array("state"=>TRUE);
	}
}
?>