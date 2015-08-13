<?php
class ClanManager extends ManagerBase {
	
	public function __construct(){
		parent::__construct();
	}
	
	//创建
	public function creatClan($gameuid,$name){
		$max_id = $this->getMaxClanId() + 1;
		$newclan = $this->initClan($gameuid,$max_id,$name);
		$this->insertDB($newclan);
		$this->setToCache("Max_Clan_id",$max_id);
		return $newclan;
	}
	
	public function getClanInfo($dataid){
		$key=array('data_id'=>$dataid);
		$result =  $this->getFromDb($dataid,$key);
		if (!empty($result)){
			require_once GAMELIB.'/model/CharacterAccountManager.class.php';
			$accout_mgr = new CharacterAccountManager();
			$adminHero = $accout_mgr->getCharacterAccount($result['adminId']);
			$result['admin'] = $adminHero;
		}
		return $result;
	}
	public function getSimClanInfo($dataid)
	{
		$key=array('data_id'=>$dataid);
		return $this->getFromDb($dataid,$key);
	}
	
	public function getClans(){
		$maxid = $this->getMaxClanId();
		if(empty($maxid)||$maxid<=0){
			return null;
		}else{
			$clan_arr = array();
			$cur_id = rand(1000,$maxid);
			$i = 0;
			while ($i <5){
				if($cur_id >$maxid){
					break;
				}
				$clan_info = $this->getClanInfo($cur_id);
				if(!empty($clan_info)){
					array_push($clan_arr,$clan_info);
				}
				$cur_id++;
				$i++;
			}
			return $clan_arr;
		}
	}
	
	public function getMaxClanId(){
		$id = $this->getFromCache("Max_Clan_id");
		if(empty($id)){
			$index = 0;
			while ($index <= 9){
				$sql = sprintf("select ifnull(max(%s),0) max_value from %s%s","data_id","clan_info_",$index);
				$res = $this->getDBHelperInstance()->getOne($sql);
				
				if(!empty($res)){
					$id = max($res['max_value'],$id);
				}
				$index++;
			}
			if(empty($id)){
				$id = 1000;
			}
			$this->setToCache("Max_Clan_id",$id);
		}
		return $id;
	}
	
	public function updateClanInfo($clanId,$newclaninfo)
	{
		$this->updateDB($clanId,$newclaninfo,array('data_id'=>$clanId));
	}
	
	public function deleteClan($data_id)
	{
		$clan_info = $this->getSimClanInfo($data_id);
		
		$this->deleteFromDb($data_id,array('data_id'=>$data_id));
		
		require_once GAMELIB.'/model/UserClanManager.class.php';
		$user_mgr = new UserClanManager();
		
		$members = explode(",",$clan_info['members']);
		foreach ($members as $memberId){
			$user_mgr->deleteClanUser($memberId);
		}
		$user_mgr->deleteClanUser($clan_info['adminId']);
		
		require_once GAMELIB.'/model/ClanItemManager.class.php';
		$itemManager = new ClanItemManager($data_id);
		$itemManager->deleteClanItems();
		
		return true;
	}
	
	private function initClan($gameuid,$data_id,$name){
		return array("data_id"=>$data_id,"name"=>$name,"adminId"=>$gameuid);
	}
	
	public function getClanTalk($clanId){
		$key = $this->getClanMesKeyName($clanId);
		return $this->getFromCache($key);
	}
	
	public function addClanTalk($clanId,$mesStr){
		$clanTalk = $this->getClanTalk($clanId);
		$key = $this->getClanMesKeyName($clanId);
		if (empty($clanTalk)){
			$this->setToCache($key,array($mesStr),NULL,3600*24*3);
		}else{
			array_push($clanTalk,$mesStr);
			if (count($clanTalk)>10){
				array_shift($clanTalk);
			}
			$this->setToCache($key,$clanTalk,NULL,3600*24*3);
		}
	}
	
	protected function getClanMesKeyName($id)
	{
		return "ClanTalk_".$id;
	}
	protected function getTableName(){
		return "clan_info";
	}
	protected function  getKeyName()
    {
    	return "data_id";
    }
}
?>