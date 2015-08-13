<?php
class RatingHeroManager extends ManagerBase {
	
	public function __construct(){
		parent::__construct();
	}
	
	
	public function addHeroRate($heroId,$score){
		$old_item = $this->getHeroRate($heroId);
		if(!empty($old_item)){
			$key=array('id'=>$heroId);
			$modify=array('score'=>$old_item['score']+$score);
			$this->updateDB($heroId,$modify,$key);
		}else{
			$modify = array('id'=>$heroId,'score'=>$score);
			$this->insertDB($modify);
		}
	}
	
	public function getTop(){
		$key = $this->getTopCacheKey();
		$topInfo = $this->getFromCache($key);
		if (empty($topInfo)){
			$topInfo = $this->regetTop();
		}else if ($topInfo[0]+3600 < time()){
			$topInfo = $this->regetTop();
		}
		return $topInfo;
	}
	
	public function setTop($data){
		$key = $this->getTopCacheKey();
		$this->setToCache($key,$data);
	}
	
	public function regetTop(){
		$sql = "SELECT *  FROM  `rating_hero` order by `score` desc LIMIT 100";
		$top = $this->getDBHelperInstance()->getAll($sql);
		$topInfo = array(time(),$top);
		$this->setTop($topInfo);
		return $topInfo;
	}
	
	public function getHeroRate($heroId){
		return $this->getFromDb($heroId,array('id'=>$heroId));
	}
	
	//findPK
	public function getHeros($score){
		$sql = "SELECT * FROM  `rating_hero` WHERE  `score` > $score ORDER BY  `score` ASC LIMIT 100";
		$heros = $this->getDBHelperInstance()->getAll($sql);
		if(empty($heros)){
			$sql = "SELECT * FROM  `rating_hero` WHERE  `score` < $score ORDER BY  `score` desc LIMIT 100";
			$heros = $this->getDBHelperInstance()->getAll($sql);
		}
		
		if(empty($heros)){
			$this->throwException("no enemy score $score");
		}
		return $heros;
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
		return $this->setToCache($key,$time,NULL,24*3600);
	}
	
	protected function getTableName(){
		return "rating_hero";
	}
	protected function  getKeyName()
    {
    	return "id";
    }
    
    private function getTopCacheKey()
    {
    	return "rating_hero_top";
    }
    
 	private function getRatingRewardCacheKey($id)
    {
    	return "rating_time_hero_".$id;
    }
}
?>