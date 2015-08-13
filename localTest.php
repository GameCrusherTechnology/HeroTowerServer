<?php
error_reporting(100);
set_time_limit(0);
if (!defined('APP_ROOT')) define('APP_ROOT',realpath(dirname(__FILE__)));
if (!defined('GAMELIB')) define('GAMELIB', APP_ROOT . '/libgame');
if (!defined('FRAMEWORK')) define('FRAMEWORK', APP_ROOT . '/framework');

require_once GAMELIB . '/config/GameConfig.class.php';
require_once FRAMEWORK . '/log/LogFactory.class.php';
require_once FRAMEWORK . '/db/RequestFactory.class.php';
require_once GAMELIB . '/GameConstants.php';

require_once GAMELIB.'/model/ManagerBase.class.php';
require_once GAMELIB.'/model/UserAccountManager.class.php';
include_once GAMELIB.'/model/ClanManager.class.php';
date_default_timezone_set('Asia/Shanghai'); 

//echo "今天:".date("Y-m-d")."<br>";  
//$mgr = new ClanManager();
//$listInfo = $mgr ->getNewClanBoss();
//print_r($listInfo);
//echo "今天:".mktime(date("Y-m-d"))."<br>"; 
//echo "今天:".mktime(date("Y-m-d"))."<br>";  
//echo "昨天:".date("Y-m-d",strtotime("-1 day")), "<br>";  
//echo "明天:".date("Y-m-d",strtotime("+1 day")). "<br>";  
//echo "一周后:".date("Y-m-d",strtotime("+1 week")). "<br>";   
//echo "一周零两天四小时两秒后:".date("Y-m-d G:H:s",strtotime("+1 week 2 days 4 hours 2 seconds")). "<br>";     
//echo "下个星期四:".date("Y-m-d",strtotime("next Thursday")). "<br>";     
//echo ":".date("Y-m-d",strtotime("this Monday"))."<br>"; 
//echo ":".strtotime("this Monday")."<br>";
//echo ":".date("Y-m-d",strtotime("next Monday"))."<br>"; 
//echo ":".strtotime("next Monday")."<br>" ;
//echo ":".date("Y-m-d",strtotime("last Monday"))."<br>"; 
//echo ":".strtotime("last Monday")."<br>" ;
//echo ":".date("Y-m-d",strtotime("-1 Monday"))."<br>"; 
//echo ":".strtotime("-1 Monday")."<br>" ;
//echo "一个月前:".date("Y-m-d",strtotime("last month"))."<br>";    
//echo "一个月后:".date("Y-m-d",strtotime("+1 month"))."<br>";    
//echo "十年后:".date("Y-m-d",strtotime("+10 year"))."<br>";   


//$con = mysql_connect("localhost","root","19870530");
//mysql_select_db("herotower", $con);
//$last = time();
//echo '<br />';
//$i = 6428;
//$max = $i+2000;
//while ($i < $max){
//	$id = $i+1000;
//	$c = rand(0,1000000);
//	$sql = "INSERT INTO  rating_hero (heroId ,rate) VALUES ($id,  $c)";
//	if (!mysql_query($sql,$con)){
//		echo	"<br>".mysql_error();
//	}
//	$i++;
//}
//SELECT * FROM `rating_hero` order by `rate` desc ASC
//$sql = "SELECT *  FROM  `rating_hero` order by `rate` desc LIMIT 100";
//$re = mysql_query($sql,$con);
//	if (!$re){
//		echo	"<br>".mysql_error();
//	}else{
//		$rows = array();
//		while($row = mysql_fetch_array($re,MYSQL_ASSOC)) {
//			$rows[] = $row;
//		}
//		print_r($rows
//		);
//		echo '<br />';
//		echo count($rows);
//	}
//echo '<br />';
//echo time() - $last;
//echo '<br />';

//
$last = time();
//
$filename = 'file.txt';
// 写入的字符

$fh = fopen($filename, "w");
//(1003, 1438531200, 5, 10)
$i = 0;
$max = 10000;
$arr = array(1438531200,1438531201,1438531202);
while ($i < $max){
	$id = $i+1004;
	$time = $arr[array_rand($arr)];
	$l = rand(0,10);
	$k = rand(0,100);
	fwrite($fh,"(". $id.",".$time.",".$l.",".$k.")".", " );
	$i ++;
//
}
fclose($fh);
echo time() - $last;

//$db_helper = new DBHelper2("mysql://root:19870530@127.0.0.1/%s?charset=utf8","rating_hero");
//$db_helper->g


//$arr = array(55,66,99,88,77);
//unset($arr[1]);
//print_r($arr);
//$newStr = implode(",",$arr);
//echo '<br />';
//print_r($newStr);
//echo "test";
//
//		$payLog = LogFactory::getLogger(array(
//			'prefix' => LogFactory::LOG_MODULE_PLATFORM,
//			'log_dir' => APP_ROOT.'/log/payment/', // 文件所在的目录
//			'archive' => ILogger::ARCHIVE_YEAR_MONTH, // 文件存档的方式
//			'log_level' => 1
//		));
//		$payLog->writeInfo(" || ". json_encode($receipt) );
//		echo '<br />';
//		if (openssl_verify($signed_data, $signature, $pub_k) !== 1) {
//			print_r( array('status'=>'error'));
//		}



////	$cache->delete("user_message_list_26003");
//	print_r ($cache->get("user_message_list_4"));
//	$log_file = APP_ROOT.'/log/payment/';
//	if(!file_exists($log_file)){
//		mkdir($log_file,0777,true);
//	}
//	$test = file_put_contents($log_file."localtest.log", "dadwewadsas"."\n",FILE_APPEND);
//	print_r("test".$test);
//
//	if(!file_exists($log_file."2014/")){
//		mkdir($log_file."2014/",0777,true);
//	}
//	$test = file_put_contents($log_file."2014/"."localtest1.log", "dadwewadsas"."\n",FILE_APPEND);
//
//	$payLog = LogFactory::getLogger(array(
//			'prefix' => LogFactory::LOG_MODULE_PLATFORM,
//			'log_dir' => APP_ROOT.'/log/payment/', // 文件所在的目录
//			'archive' => 14, // 文件存档的方式
//			'log_level' => 1
//		));
//
//	$a = $payLog->writeInfo(" || ".time());
//	print_r($a);
//	$xml_mgr = new XmlManager();
//	$dom = new DOMDocument();
//	$dom->loadXML($xml_mgr->getList(XmlDbType::XMLDB_ITEM));
//	print_r($dom);
//	print_r(getArray($dom->documentElement));



//$weedArr = array(50013,50014,50015,50016,50017,50018,50002,50003,50004,50005,50006,50007,50008);
//$rateArr = array(100,90,80,70,60,50,50,0,40,35,30,0,20);
//
//		$index =0;
//		while ($index<=100){
//			$key = StaticFunction::getOneByRate($rateArr);
//			print_r($weedArr[$key].'<br />');
//			$index++;
//		}
//

//	$sequence_handler = new IDSequence("farm_account", "gameuid");
//    	$cur_gameuid = $sequence_handler->getCurrentId();
//    	print_r($cur_gameuid);
//$strs = "Hello world";
//echo substr_replace($strs,"earth",4,5).'<br />';
//echo $strs.'<br />';
//
//$str = "00000000000000000000000000000000000000000000000000";
//echo substr_replace($str,"1",6,1);
//刷新 xml
//	$xml_mgr = new ItemManager();
//	print_r($xml_mgr->updateDef());

//	$a = array(123141,1413215415,151354223,512352523);
//	$b = array(512352523);
//	print_r(array_diff($a,$b));
?>