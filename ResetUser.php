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
require_once GAMELIB.'/model/UidGameuidMapManager.class.php';
require_once GAMELIB.'/model/XmlManager.class.php';
require_once GAMELIB.'/model/UserGameItemManager.class.php';
require_once GAMELIB.'/model/UidGameuidMapManager.class.php';

$uid = $_GET['uid'];

$mapping_handler = new UidGameuidMapManager();
$mapping_handler->deleteMapping($uid);



echo "ok";

?>