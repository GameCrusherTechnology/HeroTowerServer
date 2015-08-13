<?php
class ClanBossManager extends ManagerBase {
	
	public function __construct(){
		parent::__construct();
	}
	
	
	//clanBoss
	public function getCurClanBoss(){
		$date = strtotime("next Monday");
		$BossInfo = $this->getFromDb($date,array("Date"=>$date));
		if (empty($BossInfo)){
			$lastId = $date - 7*24*3600;
			$LastBossInfo = $this->getFromDb($lastId,array("Date"=>$lastId));
			if (empty($LastBossInfo)){
				$newId = StaticFunction::$MonsterList[0];
			}else{
				$index = array_search($BossInfo['BossId'],StaticFunction::$MonsterList);
				if ($index === FALSE){
					$newId = StaticFunction::$MonsterList[0];
				}else{
					$index ++;
					if ($index >= count(StaticFunction::$MonsterList)){
						$index = 0;
					}
					$newId = StaticFunction::$MonsterList[$index];
				}
			}
			return $this->setClanBossId($date,$newId);
		}else{
			return $BossInfo;
		}
	}
	
	public function setClanBossId($date,$id){
		$arr = array('Date'=>$date,'BossId'=>$id);
		$this->insertDB($arr);
		return $arr;
	}
	
	public function getNewClanBoss(){
		$BossTime = strtotime("next Monday");
		$bossId = StaticFunction::$MonsterList[array_rand(StaticFunction::$MonsterList)];
		return array('time'=>$BossTime,'id'=>$bossId);
	}
	protected function getTableName(){
		return "clanboss";
	}
	protected function  getKeyName()
    {
    	return "Date";
    }
}
?>