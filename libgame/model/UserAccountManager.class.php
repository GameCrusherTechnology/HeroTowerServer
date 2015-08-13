<?php

class UserAccountManager extends ManagerBase {
	
	public function __construct(){
		parent::__construct();
	}
	
	/**
 	 * 返回数据为gameuid对应的数据
 	 *
 	 * @param int $gameuid the gameuid to be retrieved.
 	 * @return array the user info of gameuid.
 	 */
    public function getUserAccount($gameuid){
    	if (empty($gameuid) || intval($gameuid) <= 0) return false;
    	
    	//account表中数据
		$account = $this->getFromDb($gameuid,array('gameuid'=>$gameuid));
		if (empty($account))	return array();
		return $account;
    }
    
 	/**
 	 * 更新用户 coin
 	 *
 	 * @param int $gameuid
 	 * @param int $coin
 	 */
    public function updateUserCoin($gameuid,$coin) {
    	$change = array('coin' => $coin);
    	$this->updateUserStatus($gameuid,$change);
    	
    	return true;
    }
	/**
 	 * 更新用户 money
 	 *
 	 * @param int $gameuid
 	 * @param int $money
 	 */
    public function updateUserMoney($gameuid,$money) {
    	$change = array('gem' => $money);
    	$this->updateUserStatus($gameuid,$change);
    	
    	return true;
    }
    
	/**
 	 * 更新用户 exp
 	 *
 	 * @param int $gameuid
 	 * @param int $experience
 	 */
    public function updateUserExperience($gameuid,$experience) {
    	$change = array('exp' => $experience);
    	$this->updateUserStatus($gameuid,$change);
    	
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
		$old_account=$this->getUserAccount($gameuid);
		if ($this->logger->isDebugEnabled()) {
			$this->logger->writeDebug("update user status:".print_r($change, true));
		}
		
		$total = array();
		//记录交易金额
		if(!empty($change['coin'])){
			if($change['coin'] < 0){
				$total['expense']= abs($change['coin']);
			}else {
				$total['income'] = $change['coin'];
			}	
		}
		//统计money的花销,本地数据库中还没有相关的文件，故不能够记录
		if(!empty($change['gem'])){
			if($change['gem'] < 0){
				$total['money_expense']= abs($change['gem']);
			}else {
				$total['money_income'] = $change['gem'];
			}	
		}

		
		//设置更新模式
		if ($is_self){
			if (!empty($change['coin']) || !empty($change['exp'])){
				$mem_key = sprintf(CacheKey::CACHE_KEY_ACCUMULATION_FLAG,'user_account',$gameuid);
				$flag = $this->getFromCache($mem_key,$gameuid);
				$flag += abs($change['coin'])+abs($change['exp']);
				if ($flag > 50){
					$no_db = false;
					$this->deleteFromCache($mem_key,$gameuid);
				}else {
					$this->setToCache($mem_key, $flag, null, 0);
				}
			}
			if ($no_db){
				if (!empty($change['gem'])){
					$no_db=false;
				}
			}
		}
		
		//获取更新数组
		$modify=array();
		$modify_extra = array();
		$modify_cache = array();
		foreach ($change as $field => $value) {
			switch ($field) {
				case 'exp':
				case 'coin':
				case 'gem':
					if (intval($value)!=0){
						$modify[$field] = $old_account[$field] + $value;
					}
					break;
				case 'strength':
					$modify[$field]=$value>=0?$value:0;
					break;
				default:
					$modify[$field] = $value;
				    break;
			}
		}
		if (empty($modify) && empty($modify_extra) && empty($modify_cache)) return false;
		
		if ($this->logger->isDebugEnabled()){
			$this->logger->writeDebug("user[$gameuid] modify:".print_r(array_merge($modify,$modify_extra),true));
		}
		//调用父类中的方法
		if(!empty($modify)){
			$this->updateDB($gameuid,$modify,array('gameuid'=>$gameuid),$no_db);
		}
//		if(!empty($total)){
//			require_once GAMELIB.'/model/UserTotalManager.class.php';
//			$UserTotalMgr = new UserTotalManager();
////			error_log(print_r($total,true),3,APP_ROOT."/log/log.log");
//			$UserTotalMgr->updateDate($gameuid,$total);
//		}
		
		return true;
	}
	
	
	public static function getUserLevel($exp){
		return StaticFunction::expToGrade($exp);	
	}
	
	/**
	 * 创建用户
	 *
	 * @param 被创建用户的gameuid $gameuid
	 * @return 返回被创建用户的gameuid
	 */
	public function createUserAccount($gameuid,$new_account) {
		$new_account['gameuid'] = $gameuid;
		$this->insertDB($new_account);
		if ($this->logger->isDebugEnabled()) {
			$this->logger->writeDebug("created new empty user[gameuid=$gameuid]");
		}
		return $gameuid;
	}
	
	
	protected function getKeyName(){
		return "gameuid";
	}
	protected function getTableName(){
    	return  "user_account";
    } 
}
?>
