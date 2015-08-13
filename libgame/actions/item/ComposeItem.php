<?php
require_once GAMELIB.'/model/UserGameItemManager.class.php';
require_once GAMELIB.'/model/UserAccountManager.class.php';
class ComposeItem extends GameActionBase{
	protected function _exec()
	{
		$gameuid = $this->getParam('gameuid','int');
		$characterId = $this->getParam('heroid','int');
		$pieceItemId =  $this->getParam('pieceItemId','int');
		
		
		$pieceitemDef = get_xml_def($pieceItemId);
		if (empty($pieceitemDef))
		{
			$this->throwException("itemId is not item $pieceItemId");
		}
		$composeId = $pieceitemDef['boundItem'];
		$composeDef = get_xml_def($composeId);
		if (empty($composeDef))
		{
			$this->throwException("composeitemId is not item $composeId");
		}
		
		$itemMgr = new UserGameItemManager($characterId);
		$composeOwnItem=$itemMgr->getItem($composeId);
		$pieceOwnItem = $itemMgr->getItem($pieceItemId);
		
		$curLevel = 0;
		if (!empty($composeOwnItem))
		{
			$curLevel = $composeOwnItem['count'];
		}
		$pieceArr = explode(",",$composeDef['upCount']);
		$needPiece = $pieceArr[$curLevel];
		
		if($pieceitemDef['type']=="skill"){
			$needCoin = StaticFunction::getUpSkillCoin($curLevel);
		}else if($pieceitemDef['type']=="soldier"){
			$needCoin = StaticFunction::getUpSoldierCoin($curLevel);
		}
		
		if (empty($pieceOwnItem)|| $pieceOwnItem['count'] < $needPiece)
		{
			$this->throwException("composeitemId piece is not enough $pieceItemId");
		}
		
		$user_mgr = new UserAccountManager();
		$user_account = $user_mgr->getUserAccount($gameuid);
		
		if ($user_account['coin']< $needCoin){
			$this->throwException("character $gameuid not enough coin $needCoin ");
		}
		$itemMgr->addItem($composeId,1);
		$itemMgr->subItem($pieceItemId,$needPiece);
		
		$user_mgr->updateUserCoin($gameuid,-$needCoin);
		$itemMgr->commitToDB();
		
		return array("state"=>TRUE);
	}
}
?>