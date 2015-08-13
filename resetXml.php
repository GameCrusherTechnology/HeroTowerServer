<?php
error_reporting(0);
set_time_limit(1800);
if (!defined('APP_ROOT')) define('APP_ROOT',realpath(dirname(__FILE__)));
if (!defined('GAMELIB')) define('GAMELIB', APP_ROOT . '/libgame');
if (!defined('FRAMEWORK')) define('FRAMEWORK', APP_ROOT . '/framework');

require_once GAMELIB . '/config/GameConfig.class.php';
require_once FRAMEWORK . '/log/LogFactory.class.php';
require_once FRAMEWORK . '/db/RequestFactory.class.php';
require_once GAMELIB . '/GameConstants.php';

require_once GAMELIB.'/model/ManagerBase.class.php';
require_once GAMELIB.'/model/UserAccountManager.class.php';
require_once GAMELIB.'/model/UidGameuidMapManager.class.php';
require_once GAMELIB.'/model/XmlManager.class.php';
try {

	//刷新 xml
	$xml_mgr = new ItemManager();
	print_r($xml_mgr->updateDef());
	
	
}catch (Exception $e){
	
}
?>