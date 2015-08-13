<?php



class CharacterAccountManager  extends ManagerBase {
	public function __construct(){
		parent::__construct();
	}
	/**
 	 * 返回数据为gameuid对应的数据
 	 *
 	 * @param int $gameuid the gameuid to be retrieved.
 	 * @return array the user info of gameuid.
 	 */
    public function getCharacterAccount($characteruid){
    	if (empty($characteruid) || intval($characteruid) <= 0) return false;
    	
    	//account表中数据
		$account = $this->getFromDb($characteruid,array('characteruid'=>$characteruid));
		return $account;
    }
    
    
	/**
 	 * 更新用户 exp
 	 * @param int $gameuid
 	 * @param int $experience
 	 */
    public function updateUserExperience($characteruid,$experience) {
    	$change = array('exp' => $experience);
    	$this->updateUserStatus($characteruid,$change);
    	return true;
    }
    
    
    
	/**
     * 更新用户状态信息
     *
     * @param array $change 数据库字段为key，改变的值为value的数组
     * 
     * 
     */
	public function updateUserStatus($gameuid, $change, $no_db=true,$is_self=true) {
		if (empty($change))	return false;
		$old_account=$this->getCharacterAccount($gameuid);
		if ($this->logger->isDebugEnabled()) {
			$this->logger->writeDebug("update user status:".print_r($change, true));
		}
		

		
		//获取更新数组
		$modify=array();
		foreach ($change as $field => $value) {
			switch ($field) {
				case 'exp':
					if (intval($value)!=0){
						$modify[$field] = $old_account[$field] + $value;
					}
					break;
				default:
					$modify[$field] = $value;
				    break;
			}
		}
		if (empty($modify)) return false;
		
		if ($this->logger->isDebugEnabled()){
			$this->logger->writeDebug("user[$gameuid] modify:".print_r(array_merge($modify,$modify_extra),true));
		}
		//调用父类中的方法
		if(!empty($modify)){
			$this->updateDB($gameuid,$modify,array('characteruid'=>$gameuid),$no_db);
		}
		
		return true;
	}
	
	
	public static function getCharacterLevel($exp){
		return StaticFunction::expToGrade($exp);	
	}
	
	public function createCharacterAccount($characteruid,$name,$item_id) {
		$new_account = array('characteruid'=>$characteruid,"exp"=>0,"name"=>$name,"item_id"=>$item_id);
		$this->insertDB($new_account);
		return $new_account;
	}
	
	public function getVipCache($heroId){
		return $this->getFromCache($this->getVipCacheKeyName($heroId));
	}
	
	public function setVipCache($heroId,$data){
		return $this->setToCache($this->getVipCacheKeyName($heroId),$data);
	}
	
	protected function getVipCacheKeyName($heroId)
	{
		return "HeroVip_"+$heroId;
	}
	
	protected function  getKeyName()
    {
    	return "characteruid";
    }
	protected function getTableName(){
    	return  "character_account";
    } 
}
?>