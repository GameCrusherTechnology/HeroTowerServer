<?php
require_once GAMELIB.'/model/UidGameuidMapManager.class.php';
require_once GAMELIB.'/model/CharacterAccountManager.class.php';
class UserLoginCommand extends GameActionBase{
	protected function _exec()
	{
		$platform_uid = $this->getParam('uid','string');
		$mapping_handler = new UidGameuidMapManager();
		$gameuid = $mapping_handler->getGameuid($platform_uid);
		$is_newer = FALSE;
		$characterArr = array();
		if ($gameuid === false) {
			if ($this->logger->isDebugEnabled()) {
				$this->logger->writeDebug("can not map platform uid[$platform_uid], create new user.");
			}
			require_once FRAMEWORK.'/database/FarmIDSequence.class.php';
    		$sequence_handler = new FarmIDSequence();
    		$gameuid = $sequence_handler->creatId();
    		if ($this->logger->isDebugEnabled()) {
    			$this->logger->writeDebug("generated new garmuid[$gameuid]");
    		}
    		$GLOBALS['gameuid'] = $gameuid;
			//创建uid,gameuid的对照关系
			$mapping_handler->createMapping($platform_uid, $gameuid);
			$this->init($gameuid);
			//统计信息
			$is_newer = TRUE;
		}else{
			$characterMgr = new CharacterAccountManager();
			$characterIndex = 0;
			while ($characterIndex<10){
				$characterId = intval($gameuid*10 + $characterIndex);
				$character_info = $characterMgr->getCharacterAccount($characterId);
				if (empty($character_info)){
					break;
				}else{
					array_push($characterArr,$character_info);
				}
				$characterIndex++;
			}
		}
		
		$GLOBALS['gameuid'] = $gameuid;
		$user_account = $this->user_account_mgr->getUserAccount($gameuid);
		if ($user_account['gameuid'] == "" || empty($user_account['gameuid'])){
			$this->logger->writeDebug("ERROR GAMEUID ".$user_account['gameuid']." UID IS $platform_uid");
		}
		$user_account['user_characters'] = $characterArr;
		
		$result['HeroCost'] = StaticFunction::$heroCost;
		$result['is_new'] = $is_newer;
		$result["user_account"]= $user_account;
		return $result;
	}
	
	private function getmicrotime() 
	{ 
	    list($usec, $sec) = explode(" ",microtime()); 
	    return ((float)$usec + (float)$sec); 
	}
	
	protected function init($gameuid){
		
		$new_account=InitUser::$account_arr;
		return $this->user_account_mgr->createUserAccount($gameuid, $new_account);
	}
}
?>