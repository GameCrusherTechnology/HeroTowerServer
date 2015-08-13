<?php
/**
 * 处理玩家的物品的类，只是针对大场景功能修改后的逻辑添加的，该处理逻辑中消耗的物品不可逆
 * 针对user_warehouse表中的物品进行处理的
 * 在这个表中只是关心表中的gameuid,item_id,count这三个属性，其中gameuid和item_id为主键
 * 其中的house_id设置成默认的0,为以后的逻辑扩展使用
 */
class UserItemManager extends ManagerBase {
	const METHOD_ADD=MethodType::METHOD_ADD;
	const METHOD_SUB=MethodType::METHOD_SUB;
	protected $gameuid=0;
	protected $cache_type=TCRequest::CACHE_KEY_LIST;
	public function __construct($gameuid){
		parent::__construct();
		if (empty($gameuid)||intval($gameuid)<1){
			$this->throwException("gameuid[$gameuid] error in item mgr",GameStatusCode::USER_NOT_EXISTS);
		}
		$this->gameuid=$gameuid;
	}
	public function getEntry($item_id){
		$key=array('gameuid'=>$this->gameuid,'item_id'=>$item_id);
		return $this->getFromDb($this->gameuid,$key);
	}
	public function getEntryList(){
		$result=array();
		$list=$this->getListAll();
		return $list;
	}
	public function getListAll(){
		$key=array('gameuid'=>$this->gameuid);
		return $this->getFromDb($this->gameuid,$key,$this->cache_type);
	}
	public function addItem($item_id,$count){
		return $this->modifyEntry($item_id,$count,self::METHOD_ADD);
	}
	public function subItem($item_id,$count){
		return $this->modifyEntry($item_id,$count,self::METHOD_SUB);
	}
	public function initItem($item_id){
		if (empty($item_id)||$item_id<0){
			return;
		}
		$old_item=$this->getEntry($item_id);
		if(empty($old_item)){
			$entry=array();
			$entry['gameuid']=$this->gameuid;
			$entry['item_id']=$item_id;
			$entry['count']=0;
			$this->insertDB($entry,$this->cache_type);
			return true;
		}
	}
	protected function modifyEntry($item_id,$count_change,$method=self::METHOD_ADD){
		$count_change=abs($count_change);
		if (empty($item_id)||$item_id<0||$count_change==0) return ;
		$old_item=$this->getEntry($item_id);
		switch ($method){
			case self::METHOD_ADD:
				$message=sprintf("gameuid\t%d\titem\t%d\tbefore\t%d\tafter\t%d",$this->gameuid,$item_id,intval($old_item['count']),intval($old_item['count'])+$count_change);
				if (!empty($old_item)){
					$this->update($item_id,$old_item['count']+$count_change);
				}else {
					$this->insert($item_id,$count_change);
				}
				break;
			case self::METHOD_SUB:
				$message=sprintf("gameuid\t%d\titem\t%d\tbefore\t%d\tafter\t%d",$this->gameuid,$item_id,intval($old_item['count']),intval($old_item['count'])-$count_change);
				if (!empty($old_item)){
					$this->update($item_id,$old_item['count']-$count_change);
				}
				break;
		}
//		$this->logger->writeFatal($message);
	}
	protected function getTableName(){
		return "user_item";
	}
	protected function update($item_id,$count,$nodb=false){
		if ($count<0) $count=0;
		$key=array('gameuid'=>$this->gameuid,'item_id'=>$item_id);
		$modify=array('count'=>$count);
		$this->updateDB($this->gameuid,$modify,$key,$nodb);
	}
	protected function insert($item_id,$count){
		if ($item_id<0||$count<=0) return false;
		$entry=array();
		$entry['gameuid']=$this->gameuid;
		$entry['item_id']=$item_id;
		$entry['count']=$count;
		$this->insertDB($entry,$this->cache_type);
		return true;
	}
}
?>