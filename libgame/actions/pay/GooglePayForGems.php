<?php 
include_once GAMELIB.'/model/TradeLogManager.class.php';
include_once GAMELIB.'/model/UserGameItemManager.class.php';
require_once FRAMEWORK . '/log/LogFactory.class.php';
require_once GAMELIB.'/model/CharacterAccountManager.class.php';
class GooglePayForGems extends GameActionBase 
{
	protected function _exec()
	{
		$payLog = LogFactory::getLogger(array(
			'prefix' => LogFactory::LOG_MODULE_PLATFORM,
			'log_dir' => APP_ROOT.'/log/payment/', // 文件所在的目录
			'archive' => ILogger::ARCHIVE_YEAR_MONTH, // 文件存档的方式
			'log_level' => 1
		));

		$gameuid = $this->getParam("gameuid",'int');
		$heroId = $this->getParam("heroid",'int');
		$receipt = $this->getParam("receipt",'array');
		$receipt_str = $this->getParam("receiptStr",'string');
		$buytype = $this->getParam("buytype",'string');
		$projectName = $this->getParam("projectName",'string');
		
		$account = $this->user_account_mgr->getUserAccount($gameuid);
		$payLog->writeInfo($projectName."||".$gameuid." || gem:".$account['gem']." || coin:".$account['coin']." || ".json_encode($receipt) );
		
		if (empty($receipt)) {
			return array('status'=>'error');
		}
		$new_rec = array();
		foreach ($receipt as $key=>$value){
			$new_rec[$key] = $value;
		}
		$signature = $new_rec['signature'];
		$signed_data = $new_rec["signedData"];
		
		$keyStr=  "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAnGq+mkH8cFacOY9UoWyi1tmAxa55pdmTpoexuMVKbOjbpsY8jwzBOxTO3VBsu7HSibYDTrn79t0uFj0YMsQ/wGK1sO/Ab08DlGEYqV7m5+QsqMcAtQ8UNUER+sGnQxnzTmr3Uq9izMkk69NXzkZRaO5lp8f4gbfRx3KT2JweWihjOyFhWdlWmHRBAJE81Wn2iFJzNGNr50XIC4VDOlt+ljcUD3vu9bZmqmgMryKwn4WtxV2o4UwT5RehpyGHAyQ6YX2jmDSfoR6z2UgajCedxGK5bfmnPZXj75DC4P08O+SlBCGhEq62o/I0sDNtdWdSVnb+HM7IcqqaEMEd6taZEwIDAQAB";
		if ($projectName == "sunnyfarm"){
			$keyStr=  "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAhbYkJPFMzSfJIU4GT66e7swkLlTxAuNcwn/fIXTa4MU0qCwq3OAMc8rO4elYXvBNMUqVabV/Qs1uGFQE7okMvyBs2SogwQ/FC4QFMTR6PDsDuVLnzah3tH1eIbUtmQPUX/3q4MW/XuThHGQHLjNV15v6WiwrYqwD7+XOqDl5xtbLkb3vwP9srpQqK/2A3pSvvQInqyJ051Eljwed5BqtgmDN1bA/UJKFEB3oCTEZaaAcTqXZojmzly5VQyP0leXzXsjbqMnm4YD9cgn63NzI7SauG7a/RlIN0YP1DmV9I5nLtKgg16KuKmgVWk0B29z+xONV8RR/PxJ1Zv1v1BsewwIDAQAB";
		}
		$KEY_PREFIX = "-----BEGIN PUBLIC KEY-----\n";
	    $KEY_SUFFIX = '-----END PUBLIC KEY-----';
		$pub_key = $KEY_PREFIX . chunk_split($keyStr, 64, "\n") . $KEY_SUFFIX;
		$pub_k = openssl_get_publickey($pub_key);
		
		$r = openssl_verify($signed_data, base64_decode($signature), $pub_k);
		if ($r !== 1 && $buytype!="localTest") {
			$payLog->writeError($gameuid." || ".$r );
			return array('status'=>'error');
		}
		$signed_data = json_decode($signed_data, true);
		$new_request_order = array();
		foreach ($signed_data as $key=>$value){
			$new_request_order[$key] = $value;
		}
		$tradeManager = new TradeLogManager();
		
		$cached_orders = $tradeManager->getOrderCache($gameuid);
		if (empty($cached_orders)){
			$cached_orders = array();
		}
		
		$purchase_state = $new_request_order['purchaseState'];
		$purchasetime = $new_request_order['purchaseTime'];
		$product_id = $new_request_order['productId'];
		$transactionid = $new_request_order['orderId'];
		
		$notification_id = "t".$new_request_order['orderId'];
		if (empty($transactionid)) {
			return array('status'=>'error');
		}
		if (in_array($notification_id,$cached_orders)){
			return array('status'=>'error');
		}
		$rewards = InitUser::$treasure_activity;
		if ($purchase_state == 0)
		{
			$hero_mgr = new CharacterAccountManager();
			$heroInfo = $hero_mgr->getCharacterAccount($heroId);
			$rewardInfo = $rewards[$product_id];
			
//			if (intval($rewards['time']) > time()){
//				$item = $this->addReward($gameuid,$rewardInfo);
//			}
			$treasuretype = $rewardInfo['type'];
			if(!empty($rewardInfo)){
				$change[$treasuretype] = $rewardInfo['count'];
				$herochange['vip'] = $heroInfo['vip'] + $rewardInfo['vip'];
			}else{
				$this->throwException("wrong product_id :".$product_id ."type : $treasuretype",GameStatusCode::PARAMETER_ERROR);
			}
			$this->user_account_mgr->updateUserStatus($gameuid,$change);
			$hero_mgr->updateUserStatus($heroId,$herochange);
		}
		$tradeinfo = array();
		$tradeinfo['gameuid'] = $gameuid;
		$tradeinfo['product_id'] = $product_id;
		$tradeinfo['platform'] = "andriod";
		$tradeinfo['orderId'] = $transactionid;
		$tradeinfo['purchaseState'] = $purchase_state;
		$tradeinfo['purchasetime'] = $purchasetime;
		$tradeinfo['status'] = 1;
		$tradeManager->insert($tradeinfo);
		array_push($cached_orders,$notification_id);
		
		$tradeManager->setOrderCache($gameuid,$cached_orders);
		$new_account = $this->user_account_mgr->getUserAccount($gameuid);
		
		$payLog->writeInfo($gameuid." || ".$treasuretype." || " .$new_account[$treasuretype] );
		
		return array("boughtName"=>$product_id);
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
