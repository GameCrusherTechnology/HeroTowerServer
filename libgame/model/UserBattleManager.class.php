<?php
class UserBattleManager  extends ManagerBase {
	public function __construct(){
		parent::__construct();
	}
	
	
	protected function getTableName(){
    	return  "battle_info";
    } 
}
?>