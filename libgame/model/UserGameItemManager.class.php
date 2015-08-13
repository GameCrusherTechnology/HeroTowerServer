<?php
/**
 * 该类主要是针对用户的存入仓库、工具表、收藏表中的数据
 * 在提交数据库之前，必须在代码中调用检查玩家物品数量的逻辑
 * 在改变玩家的数据的时候，addItem和subItem只是将要进行的数量进行了一次检查，如果要进行加减，必须调用commitToDB的方法
 */
include_once GAMELIB.'/model/UserItemManager.class.php';
class UserGameItemManager extends ManagerBase {
	const METHOD_ADD = MethodType::METHOD_ADD;
	const METHOD_SUB = MethodType::METHOD_SUB;
	protected $gameuid=0;
	//用来保存已经实例化之后的类
	protected $item_mgr=null;
	protected $result=array();
	protected $item_modify=array();
	/**
	 * 日志操作类
	 *
	 * @var ILogger
	 */
	protected $logger = null;

	public function __construct($gameuid) {
		parent::__construct();
		if ($gameuid<=0||empty($gameuid)){
			$this->throwException("gameuid[$gameuid] is error",GameStatusCode::USER_NOT_EXISTS);
		}
		$this->gameuid=$gameuid;
	}
	public function getItemManeger(){
		if (empty($this->item_mgr)){
			$this->item_mgr=new UserItemManager($this->gameuid);
		}
		return $this->item_mgr;
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
	public function addItem($item_id,$count,$logType){
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
	public function subItem($item_id,$count,$logType){
		if($count < 1){
			$this->throwException('subcount error id '.$item_id." c ".$count,GameStatusCode::PARAMETER_ERROR);
		}
		return $this->modify($item_id,$count,self::METHOD_SUB,$logType);
	}
	/*
	 * @将特定item_id的物品初始化为零
	 	 * item_id 35017 和 35018专用
	 	 * param item_id 物品id
	 */
	 public function initItem($item_id){
	 	return $this->getItemManeger()->initItem($item_id);
	 }
	/**
	 * @获取一个物品的数量
	 * param item_id 物品id
	 */
	public function getItem($item_id){
		$item=$this->getItemManeger()->getEntry($item_id);
		return $item;
	}
	/**
	 * @获取一类物品的数量
	 * param table_type:owned表示用户的背包,wareh表示仓库,colct表示收藏
	 */
	public function getItemList(){
		$list=$this->getItemManeger()->getEntryList();
		return $list;
	}
	/*
	 * @检查要减少的物品的数量是否够
	 */
	public function checkReduceItemCount($throwException = true){
		foreach ($this->item_modify[self::METHOD_SUB] as $item_id=>$count){
			$item=$this->getItem($item_id);
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
			$this->getItemManeger()->addItem($item_id,$count);
		}
	}
	/**
	 * @将减少的物品提交数据库
	 */
	protected function commitReduce(){
		foreach ($this->item_modify[self::METHOD_SUB] as $item_id=>$count){
			$this->getItemManeger()->subItem($item_id,$count);
		}
	}
	protected function getTableName(){
		return "user_item";
	}
}
?>