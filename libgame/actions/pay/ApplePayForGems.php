<?php 
include_once GAMELIB.'/model/TradeLogManager.class.php';
include_once GAMELIB.'/model/UserGameItemManager.class.php';
require_once FRAMEWORK . '/net/HttpRequest.class.php';
require_once FRAMEWORK . '/log/LogFactory.class.php';
class ApplePayForGems extends GameActionBase 
{
	protected function _exec()
	{
		$payLog = LogFactory::getLogger(array(
			'prefix' => LogFactory::LOG_MODULE_PLATFORM,
			'log_dir' => APP_ROOT.'/log/ApplePayment/', // 文件所在的目录
			'archive' => ILogger::ARCHIVE_YEAR_MONTH, // 文件存档的方式
			'log_level' => 1
		));
		
		$gameuid = $this->getParam("gameuid",'string');
		$key = $this->getParam("key",'string');
		
		//提交审核的时候 使用 沙箱地址
//		$verify_url = "https://sandbox.itunes.apple.com/verifyReceipt";
		$verify_url = 'https://buy.itunes.apple.com/verifyReceipt';
    	$verify_postfields = json_encode(array("receipt-data"=> $key));
    	$res = HttpRequest::post($verify_url, $verify_postfields);
    	$resData = json_decode($res['data'],true);
    	
    	if($resData !== NULL && $resData["status"] == 0){
    		$tradeManager = new TradeLogManager();
    		$receipt = $resData["receipt"];
    		$id = $receipt['transaction_id'];
	    	$account = $this->user_account_mgr->getUserAccount($gameuid);
			$payLog->writeInfo($gameuid." || ".$account['gem']." || ".json_encode($receipt) );
			
			$cached_orders = $tradeManager->getOrderCache($gameuid);
	    	if (empty($cached_orders)){
				$cached_orders = array();
			}
			
			if (empty($receipt)) {
				return array('status'=>'error');
			}
			
    		if (in_array($id,$cached_orders)){
				return TRUE;
			}
    	
			$rewards = InitUser::$treasure_activity;
			$product_id = $receipt['product_id'];
			if ($product_id == "FAMEGEM01"){
				$change['gem'] = 200;
				$item = $this->addReward($gameuid,$rewards['littleFarmGem']);
			}elseif ($product_id == "FAMEGEM02"){
				$change['gem'] = 1100;
				$item = $this->addReward($gameuid,$rewards['largeFarmGem']);
			}else{
				$this->throwException("wrong product_id :".$product_id,GameStatusCode::PARAMETER_ERROR);
			}
			$this->user_account_mgr->updateUserStatus($gameuid,$change);
    		$tradeinfo = array();
			$tradeinfo['gameuid'] = $gameuid;
			$tradeinfo['product_id'] = $product_id;
			$tradeinfo['platform'] = "apple";
			$tradeinfo['orderId'] = $id;
			$tradeinfo['purchaseState'] = 1;
			$tradeinfo['purchasetime'] = time();
			$tradeinfo['status'] = 1;
			$tradeManager->insert($tradeinfo);
			
	    	$new_account = $this->user_account_mgr->getUserAccount($gameuid);
	    	
	    	array_push($cached_orders,$id);
	    	$tradeManager->setOrderCache($gameuid,$cached_orders);
			$payLog->writeInfo($gameuid." || ".$new_account['gem'] );
			if (empty($item)){
				return array("gem"=>$new_account['gem']);
			}else {
				return array("gem"=>$new_account['gem'],"items"=>$item);
			}
		}
	}
	
	private function addReward($gameuid,$rewards)
	{
		$item_mgr = new UserGameItemManager($gameuid);
		foreach ($rewards as $value){
			if ($value['id'] == 'coin'){
				$this->user_account_mgr->updateUserCoin($gameuid,$value['count']);
			}else if($value['id']== 'exp'){
				$this->user_account_mgr->updateUserExperience($gameuid,$value['count']);
			}else {
				$item_mgr->addItem($value['id'],$value['count']);
			}
		}
		$item_mgr->commitToDB();
		return $rewards;
	}
}
?>
