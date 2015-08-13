<?php
class RatingClanManager extends ManagerBase {
	
	public function __construct(){
		parent::__construct();
	}
	
	
	public function updateClanRate($clanId,$change){
		$key=array('id'=>$clanId);
		$this->updateDB($clanId,$change,$key);
	}
	
	public function addNewClanRate($clanId,$change){
		$this->insertDB($change);
	}
	
	public function getTop(){
		$key = $this->getTopCacheKey();
		$topInfo = $this->getFromCache($key);
		if (empty($topInfo)){
			$topInfo = $this->regetTop();
		}else if (($topInfo[0]+ 3600) < time()){
			$topInfo = $this->regetTop();
		}
		return $topInfo;
	}
	
	public function setTop($data){
		$key = $this->getTopCacheKey();
		$this->setToCache($key,$data);
	}
	
	public function regetTop(){
		$sql = "SELECT *  FROM  `rating_clan` order by `level` desc , `kills` desc LIMIT 100";
		$top = $this->getDBHelperInstance()->getAll($sql);
		$topInfo = array(time(),$top);
		$this->setTop($topInfo);
		return $topInfo;
	}
	
//reward
	public function getRatingRewardTime($id)
	{
		$key = $this->getRatingRewardCacheKey($id);
		return $this->getFromCache($key);
	}
	
	public function setRatingRewardTime($id,$time)
	{
		$key = $this->getRatingRewardCacheKey($id);
		return $this->setToCache($key,$time,NULL,24*3600*8);
	}
	
	public function getClanRate($clanId){
		return $this->getFromDb($clanId,array('id'=>$clanId));
	}
	
	protected function getTableName(){
		return "rating_clan";
	}
	protected function  getKeyName()
    {
    	return "id";
    }
    
    private function getTopCacheKey()
    {
    	return "rating_clan_top";
    }
	private function getRatingRewardCacheKey($id)
    {
    	return "rating_time_clan_".$id;
    }
}
?>