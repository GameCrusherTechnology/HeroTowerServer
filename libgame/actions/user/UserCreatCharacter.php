<?php
require_once GAMELIB.'/model/UserAccountManager.class.php';
require_once GAMELIB.'/model/CharacterAccountManager.class.php';
require_once GAMELIB.'/model/UserGameItemManager.class.php';
class UserCreatCharacter extends GameActionBase{
	protected function _exec()
	{
		$gameuid = $this->getParam('gameuid','int');
		$name = $this->getParam('name','string');
		$itemId =  $this->getParam('itemId','int');
		$characterId = $this->getParam('characterId','int');
		
		$characterDef = get_xml_def($itemId);
		if (empty($characterDef)|| (intval($itemId/10000) != 1))
		{
			$this->throwException("itemId is not character $itemId",GameStatusCode::DATA_ERROR);
		}
		$HeroCost = StaticFunction::$heroCost;
		
		$user_mgr = new UserAccountManager();
		$user_account = $user_mgr->getUserAccount($gameuid);
		
		if ($characterId >= count($HeroCost)){
			$this->throwException("character id not enough characterId: $characterId ",GameStatusCode::DATA_ERROR);
		}
		$costcoin = $HeroCost[$characterId]["coin"];
		if($costcoin > $user_account["coin"]){
			$this->throwException("user  not enough coin: $costcoin ",GameStatusCode::DATA_ERROR);
		}
		$change = array('coin' => -$costcoin);
		
		$costgem = $HeroCost[$characterId]["gem"];
		if($costgem > $user_account["gem"]){
			$this->throwException("user  not enough gem: $costgem ",GameStatusCode::DATA_ERROR);
		}
		$change['gem'] =  -$costgem;
		
		$new_CharacterId = intval($gameuid*10+$characterId);
		$character_mgr = new CharacterAccountManager();
		$character_info = $character_mgr->getCharacterAccount($new_CharacterId);
		if (!empty($character_info)){
			$this->throwException("character id is exist characterId: $new_CharacterId ",GameStatusCode::DATA_ERROR);
		}
		
		$character_info = $character_mgr->createCharacterAccount($new_CharacterId,$name,$itemId);
		
		$itemMgr = new UserGameItemManager($new_CharacterId);
		
		$initItems = InitUser::$item_arr[$itemId];
		foreach ($initItems as $v){
			$itemMgr->addItem($v['item_id'],$v['count']);
		}
		$itemMgr->commitToDB();
		
		$user_mgr->updateUserStatus($gameuid,$change);
		
		return array("character"=>$character_info,"change"=>$change);
		
	}
}
?>