<?php
class ClanItemManager extends ManagerBase {
	const METHOD_ADD = MethodType::METHOD_ADD;
	const METHOD_SUB = MethodType::METHOD_SUB;
	protected $cache_type=TCRequest::CACHE_KEY_LIST;
	protected $clanId=0;
	//用来保存已经实例化之后的类
	protected $result=array();
	protected $item_modify=array();
	/**
	 * 日志操作类
	 *
	 * @var ILogger
	 */
	protected $logger = null;

	public function __construct($clanId) {
		parent::__construct();
		if ($clanId<=0||empty($clanId)){
			$this->throwException("clanid[$clanId] is error",GameStatusCode::USER_NOT_EXISTS);
		}
		$this->$clanId=$clanId;
	}
	/**
	 * @更改用户的物品数量
	 * item_id：要更改的物品的id
	 * count: 要更改的数量(正数)，会取绝对值
	 * method: 更改的方式,METHOD_ADD为添加,METHOD_SUB为减少,默认为METHOD_ADD
	 *
	 */
	protected function modify($item_id,$count,$method=self::METHOD_ADD,$logType){
		$count=abs($count);
		if ($count==0) return false;
		//先获取物品改变应该存储的位置
		if ($method!=self::METHOD_ADD&&$method!=self::METHOD_SUB){
			$this->throwException("method[$method] not exist in UserGameItemManager",
				GameStatusCode::DATA_ERROR);
		}
// 		if(!empty($logType)){
//			$message=sprintf("gameuid\t%d\titem\t%d\tmethod\t%d\tvar\t%d\tfun\t%s",$this->gameuid,$item_id,$method,$count,$logType);	
// 		}else{
// 			$message=sprintf("gameuid\t%d\titem\t%d\tmethod\t%d\tvar\t%d\tfun\t%s",$this->gameuid,$item_id,$method,$count,"null");
// 		}
// 		$this->logger->writeFatal($message);
		$this->item_modify[$method][$item_id]+=$count;
		return true;
	}
	/**
	 * @添加物品
	 * param item_id 物品id
	 * param count 减少的物品数量
	 */
	public function addClanItem($item_id,$count,$logType){
		if($count < 1){
			$this->throwException('addcount error id '.$item_id." c ".$count,GameStatusCode::DATA_ERROR);
		}
		return $this->modify($item_id,$count,self::METHOD_ADD,$logType);
	}
	/**
	 * @减少物品
	 * param item_id 物品id
	 * param count 减少的物品数量
	 *
	 */
	public function subClanItem($item_id,$count,$logType){
		if($count < 1){
			$this->throwException('subcount error id '.$item_id." c ".$count,GameStatusCode::PARAMETER_ERROR);
		}
		return $this->modify($item_id,$count,self::METHOD_SUB,$logType);
	}
	/**
	 * @获取一个物品的数量
	 * param item_id 物品id
	 */
	public function getClanItem($item_id){
		$item=$this->getEntry($item_id);
		return $item;
	}
	/**
	 * @获取一类物品的数量
	 * param table_type:owned表示用户的背包,wareh表示仓库,colct表示收藏
	 */
	public function getClanItemList(){
		$list=$this->getEntryList();
		return $list;
	}
	/*
	 * @检查要减少的物品的数量是否够
	 */
	public function checkReduceItemCount($throwException = true){
		foreach ($this->item_modify[self::METHOD_SUB] as $item_id=>$count){
			$item=$this->getClanItem($item_id);
			if (empty($item)||$item['count']<$count){
				if($throwException){
					$this->throwException("item[$item_id] of user[$this->gameuid] not enough,need[$count] own[".
					$item['count']."]",GameStatusCode::DATA_ERROR);
				}else{
					return false;
				}
			}
		}
		return true;
	}
	/*
	 * @将用户数据的变化提交数据库
	 * 首先检查要减少的物品的数量是否够
	 * 然后修改物品的数量
	 *
	 */
	public function commitToDB(){
		if (!empty($this->item_modify[self::METHOD_SUB]))
			$this->checkReduceItemCount();
		$this->commitAdd();
		$this->commitReduce();
		//将已经更改的物品信息置空避免重复出现
		$this->item_modify=array();
		return array_values($this->result);
	}
	/**
	 * @将添加的物品提交数据库
	 */
	protected function commitAdd(){
		foreach ($this->item_modify[self::METHOD_ADD] as $item_id=>$count){
			$this->addItem($item_id,$count);
		}
	}
	/**
	 * @将减少的物品提交数据库
	 */
	protected function commitReduce(){
		foreach ($this->item_modify[self::METHOD_SUB] as $item_id=>$count){
			$this->subItem($item_id,$count);
		}
	}
	private function getEntry($item_id){
		$key=array('data_id'=>$this->clanId,'item_id'=>$item_id);
		return $this->getFromDb($this->clanId,$key);
	}
	private function getEntryList(){
		$result=array();
		$list=$this->getListAll();
		return $list;
	}
	private function getListAll(){
		$key=array('data_id'=>$this->clanId);
		return $this->getFromDb($this->clanId,$key,$this->cache_type);
	}
	private function addItem($item_id,$count){
		return $this->modifyEntry($item_id,$count,self::METHOD_ADD);
	}
	private function subItem($item_id,$count){
		return $this->modifyEntry($item_id,$count,self::METHOD_SUB);
	}
	private function modifyEntry($item_id,$count_change,$method=self::METHOD_ADD){
		$count_change=abs($count_change);
		if (empty($item_id)||$item_id<0||$count_change==0) return ;
		$old_item=$this->getEntry($item_id);
		switch ($method){
			case self::METHOD_ADD:
//				$message=sprintf("gameuid\t%d\titem\t%d\tbefore\t%d\tafter\t%d",$this->gameuid,$item_id,intval($old_item['count']),intval($old_item['count'])+$count_change);
				if (!empty($old_item)){
					$this->update($item_id,$old_item['count']+$count_change);
				}else {
					$this->insert($item_id,$count_change);
				}
				break;
			case self::METHOD_SUB:
//				$message=sprintf("gameuid\t%d\titem\t%d\tbefore\t%d\tafter\t%d",$this->gameuid,$item_id,intval($old_item['count']),intval($old_item['count'])-$count_change);
				if (!empty($old_item)){
					$this->update($item_id,$old_item['count']-$count_change);
				}
				break;
		}
//		$this->logger->writeFatal($message);
	}
	protected function update($item_id,$count,$nodb=false){
		if ($count<0) $count=0;
		$key=array('data_id'=>$this->clanId,'item_id'=>$item_id);
		$modify=array('count'=>$count);
		$this->updateDB($this->clanId,$modify,$key,$nodb);
	}
	protected function insert($item_id,$count){
		if ($item_id<0||$count<=0) return false;
		$entry=array();
		$entry['data_id']=$this->clanId;
		$entry['item_id']=$item_id;
		$entry['count']=$count;
		$this->insertDB($entry,$this->cache_type);
		return true;
	}
	
	public function deleteClanItems(){
		$this->deleteFromDb($this->clanId,array('data_id'=>$this->clanId));
	}
	
	protected function getTableName(){
		return "clan_item";
	}
	protected function  getKeyName()
    {
    	return "data_id";
    }
}
?>