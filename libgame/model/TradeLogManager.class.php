<?php
/**
 * trade_log表及其字段意义 
 * id            varchar(120)  支付唯一码
 * amount        int(11)	   本次农币变化数量
 * gameuid       int(10) unsigned 
 * status        varchar(1)	   支付是否成功
 * trade_type    varchar(1)    1（写死的）
 * topup_type    varchar(15)	准备用这个来存储玩家储值时的农币数量
 * product_type  varchar(15)	存储额外添加的物品的id（0表示达到添加条件，t*表示添加成功，f表示不成功）
 * product_id    varchar(32)   游戏的app_id
 * ip            varchar(16)   请求调用的ip
 * create_time   int(11)	    支付的时间
 * update_time   int(11)		
 * product_name  varchar(255)
 * token         varchar(50)	加密串
*/
require_once GAMELIB.'/model/ManagerBase.class.php';
class TradeLogManager extends ManagerBase {
	protected function getTableName(){
		return "user_trade";
	}
	public function get($gameuid,$id){
		return $this->getFromDb($gameuid,array('gameuid'=>$gameuid,'data_id'=>$id));
	}
	public function insert($change){
		$this->insertDB($change);
	}
	public function update($gameuid,$id,$modify){
		$this->updateDB($gameuid,$modify,array('gameuid'=>$gameuid,'data_id'=>$id));
	}
	
	
	public function getOrderCache($gameuid)
	{
		return  $this->getFromCache("farm_pay_".$gameuid,0);
	}
	public function setOrderCache($gameuid,$arr)
	{
		$this->setToCache("farm_pay_".$gameuid,$arr,$gameuid,86400);
	}
	public function deleteOrderCache($gameuid)
	{
		$this->delToCache("farm_pay_".$gameuid,0);
	}
	
}
?>