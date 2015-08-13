<?php
/**
 * 获取xml数据库中定义的物品的属性
 *
 * @param 物品的id
 * @param XML数据库类型 $type
 * @return 物品的定义
 */
function get_xml_def($id, $type = XmlDbType::XMLDB_ITEM) {
	static $xml_db_mgr = array();
	if (count($xml_db_mgr) === 0) {
		require_once GAMELIB.'/model/XmlManager.class.php';
		$xml_db_mgr[XmlDbType::XMLDB_ITEM] = new ItemManager();
	}
	if (!isset($xml_db_mgr[$type])) {
		global $logger;
		$logger->writeError("the xml db type[$type] does not exist.");
	}
	return $xml_db_mgr[$type]->getDef($id);
}
/**
 * 获取xml数据库中定义的物品的list
 *
 * @param XML数据库类型 $type
 * @return 物品的定义的list
 */
function get_xml_def_list($type){
	static $xml_dblist_mgr = array();
	if (count($xml_dblist_mgr) === 0) {
		require_once GAMELIB.'/model/XmlManager.class.php';
		$xml_dblist_mgr[XmlDbType::XMLDB_ITEM] = new ItemManager();
	}
	if (!isset($xml_dblist_mgr[$type])) {
		global $logger;
		$logger->writeError("the xml db type[$type] does not exist.");
	}
	
	return $xml_dblist_mgr[$type]->getDefList();
}
/**
 * post data to High score api server
 * @param $array
 * apiServerUrl:要请求的url的地址
 * post_string:要传递的参数
 */
function apiPostWithReturn($array) {
	$useragent = 'Netlog Hight Score ' . phpversion();
	if (function_exists('curl_init')) {
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $array['apiServerUrl']);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $array['post_string']);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
   		curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
   		$result = curl_exec($ch);
    	curl_close($ch);
	} else {
    	$content_type = 'application/x-www-form-urlencoded';
    	$content = $array['post_string'];
	    $content_length = strlen($content);
	    $context = array('http' =>
	                  array('method' => 'POST',
		                    'user_agent' => $useragent,
		                    'header' => 'Content-Type: ' . $content_type . "\r\n" .
		                                'Content-Length: ' . $content_length,
		                    'content' => $content));
	    $context_id = stream_context_create($context);
	    $sock = fopen($array['apiServerUrl'], 'r', false, $context_id);
	    $result = '';
	    if ($sock) {
			while (!feof($sock)) {
	        $result .= fgets($sock, 4096);
	    	}
	    	fclose($sock);
	    }
    }
    return $result;
}
/**
 * post data to High score api server
 * @param $array
 * apiServerUrl:要请求的url的地址
 * post_string:要传递的参数
 */
function apiPostWithoutReturn($array) {
	$useragent = 'Netlog Hight Score ' . phpversion();
	if (function_exists('curl_init')) {
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $array['apiServerUrl']);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $array['post_string']);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
   		curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
   		$result = curl_exec($ch);
    	curl_close($ch);
	} else {
    	$content_type = 'application/x-www-form-urlencoded';
    	$content = $array['post_string'];
	    $content_length = strlen($content);
	    $context = array('http' =>
	                  array('method' => 'POST',
		                    'user_agent' => $useragent,
		                    'header' => 'Content-Type: ' . $content_type . "\r\n" .
		                                'Content-Length: ' . $content_length,
		                    'content' => $content));
	    $context_id = stream_context_create($context);
	    $sock = fopen($array['apiServerUrl'], 'r', false, $context_id);
	    fclose($sock);
    }
    return $result;
}
/**
 * generate url param string
 * @param unknown_type $post_array
 * @return string
 */
function generateUrlString($post_array) {
	foreach ($post_array as $key => &$val) {
		if (is_array($val)) {
	    	$val = json_encode($val);
	    }
	    $post_params[] = $key."=".urlencode($val);
	}
	$urlString = implode('&', $post_params);
	return $urlString;
}



function get_extra_awarad($level, $job){
	$result = array();
	$id = 5000 + $job * 10 + $level;
	switch ($job){
		case JobConst::JOB_PETANIMAL:
		case JobConst::JOB_CAREFIELD:
			break;
		case JobConst::JOB_COLLECTWOOD:
		case JobConst::JOB_COLLECTSTONE:
			//获取额外奖励,随机产出,目前是有一半的几率
			$extra_award_info = CompleteJobData::$extra_award[$id];
			$rand = mt_rand(0,10000);
			$probability = floor($extra_award_info['rate'] * 10000);
			if ($rand < $probability){

				//获取额外奖励的item_id
				$extra_item_id = get_extra_info($extra_award_info['awards_rate']);

				$extra_item_count = get_extra_info($extra_award_info['awards_count']);

				$result = array('item_id'=>$extra_item_id,'count'=>$extra_item_count);
			}
			break;
	}
	return $result;
}

function get_extra_info($awards_rate){
	//将概率转换为整数
	foreach ($awards_rate as $key=>$rate){
		$awards_rate[$key]= $rate * 100;
	}
	return StaticFunction::getOneByRate($awards_rate);
}
function additems($gameuid,$item_id,$uid,$name){
	$item_def=get_xml_def($item_id,XmlDbType::XMLDB_ITEM);
	if(empty($item_def['matchPoint'])){
		return 0;
	}else{
	include_once GAMELIB.'/model/activity/UserMedalManager.class.php';
	include_once GAMELIB.'/model/UserPickUpBonusManager.class.php';
	//每次新大赛都要会实例化第几个表
	$user_medal=new UserMedalManager(8);
	$user_pick=new UserPickUpBonusManager();
	$item=explode("|",$item_def['matchPoint']);
	$rand_arr=array();
	foreach ($item as $v){
		$add_item=explode(":",$v);
		$rand_arr[$add_item[0]]=$add_item[1];
	}
	$count=StaticFunction::getOneByRate($rand_arr);
	if($count>0){
		$user_medal->addMedal($gameuid,$uid,$name,$count);
		$user_pick->setPickUpCache($gameuid,'special',$count);
		}
		return $count;
	}
}

function isofficial($gameuid){
	$result=false;
	if (defined("OFFICIAL_UID")){
		$official_uid=OFFICIAL_UID;
		if (!empty($official_uid)){
			require_once GAMELIB.'/model/UidGameuidMapManager.class.php';
			$map_mgr=new UidGameuidMapManager();
			$official_gameuid=$map_mgr->getGameuid($official_uid);
			if($official_gameuid==$gameuid){
				$result= true;
			}
		}
	}
	return $result;
}



?>