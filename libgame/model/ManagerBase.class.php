<?php
abstract class ManagerBase {
	/**
	 * 日志操作类
	 *
	 * @var ILogger
	 */
	protected $logger = null;
	
	public function __construct() {
		$this->logger = LogFactory::getLogger(array(
			'prefix' => LogFactory::LOG_MODULE_MODEL, // 文件名的前缀
			'log_dir' => APP_ROOT.'/log/', // 文件所在的目录
			'archive' => ILogger::ARCHIVE_YEAR_MONTH, // 文件存档的方式
			'log_level' => get_app_config()->getLogLevel(LogFactory::LOG_MODULE_MODEL)
		));
	}
	
    protected function getFromCache($key, $gameuid = null) {
    	$appConfigInstance = get_app_config($gameuid);
        $cache_handler = $appConfigInstance->getTableServer($this->getTableName(), $gameuid)->getCacheInstance();
        return $cache_handler->get($key);
    }
	protected function setToCache($key, $value, $gameuid = null, $expire_time=null) {
    	$appConfigInstance = get_app_config($gameuid);
    	$table_server = $appConfigInstance->getTableServer($this->getTableName(), $gameuid);
        $cache_handler = $table_server->getCacheInstance();
        if ($expire_time === null){
        	$expire_time = $table_server->getCacheExpireTime();
        }
        return $cache_handler->set($key, $value, $expire_time);
    }
    protected function deleteFromCache($key, $gameuid = null) {
    	$appConfigInstance = get_app_config($gameuid);
        $cache_handler = $appConfigInstance->getTableServer($this->getTableName(), $gameuid)->getCacheInstance();
        return $cache_handler->delete($key);
    }
    
    protected function getDBHelperInstance($gameuid = null) {
    	$appConfigInstance = get_app_config($gameuid);
    	return $appConfigInstance->getTableServer($this->getTableName(), $gameuid)->getDBHelperInstance();
    }
    
	protected function getCacheInstance($gameuid = null) {
    	$appConfigInstance = get_app_config($gameuid);
    	return $appConfigInstance->getTableServer($this->getTableName(), $gameuid)->getCacheInstance();
    }
    
	/**
	 * 抛出一个异常
	 * @param $message
	 * @param $code
	 * @param $uid
	 * @param $gameuid
	 * @return void
	 */
	protected function throwException($message,$code){
		throw new GameException($message,$code);
	}
	/**
	 * 修改一条数据
	 *
	 * @param int $gameuid
	 * @param array $new_value，要修改的数据内容，字段名称为key，值为value
	 * @param array $key_value，可以唯一标示一条数据的一个或几个值，字段名称为key，值为value
	 * @param unknown_type $no_db，=false 要修改数据库，=true 不修改数据库；默认为false
	 * @return unknown
	 */
	protected function updateDB($gameuid,$new_value,$key_value,$no_db=false){
		if (empty($new_value)||empty($key_value)) return false;
		$req=RequestFactory::createUpdateRequest(get_app_config($gameuid));
		if ($no_db) $req->setNoDb(true);
		$req->setKey($this->getKeyName(),$gameuid);
		$req->setTable($this->getTableName());
		foreach ($key_value as $key=>$value){
			$req->addKeyValue($key, $value);
		}
		$req->setModify($new_value);
		$req->execute();
		return true;
	}
	/**
	 * 向数据库中插入一条数据
	 *
	 * @param array $change 要插入数据库的一条数据，字段名为key
	 * @param int $cache_type；默认为null，当是cache_key_list时需要显式传递参数
	 * @return unknown
	 */
	protected function insertDB($change, $cache_type = null){
		if (empty($change))	return false;
		$req=RequestFactory::createInsertRequest(get_app_config());
		$req->setKey($this->getKeyName(),$change[$this->getKeyName()]);
		$req->setTable($this->getTableName());
		$req->setColumns(implode(',',array_keys($change)));
		$req->addValues(array_values($change));
		
		if (isset($cache_type)) $req->setCacheType($cache_type);
		$req->execute();
		return true;
	}
	
	protected function insertDBBatch($change,$cache_type = null){
		if(empty($change)) return false;
		foreach($change as $ch){
			$req=RequestFactory::createInsertRequest(get_app_config());
			$req->setTable($this->getTableName());
			$req->setKey($this->getKeyName(),$ch[$this->getKeyName()]);
			$req->setColumns(implode(',',array_keys($ch)));
			break;
		}
		
		foreach($change as $ch){
			$req->addValues(array_values($ch));
		}
		
		if (isset($cache_type)) $req->setCacheType($cache_type);
		$req->execute();
		return true;
	}
	/**
	 * 从数据库获取信息
	 * @param $gameuid
	 * @param $key_values array,名称做key，数值做value
	 * @param $cache_type 当要查的信息是cache_key_list时需要额外设置，默认为CACHE_PRIMARY_KEY
	 * @return array 为cache_key_list时，调用fetchall；CACHE_PRIMARY_KEY调用fetchOne
	 */
 	protected function getFromDb($gameuid, $key_values, $cache_type = TCRequest::CACHE_PRIMARY_KEY){
 		if (empty($key_values))	return array();
    	$req = RequestFactory::createGetRequest(get_app_config($gameuid));
		$req->setKey($this->getKeyName(),$gameuid);
		$req->setTable($this->getTableName());
		$req->setCacheType($cache_type);
		foreach ($key_values as $key=>$value) {
			$req->addKeyValue($key,$value);
		}
		if ($cache_type == TCRequest::CACHE_KEY_LIST){
			return $req->fetchAll();
		}
		return $req->fetchOne();
    }
    /**
	 * 从数据库删除一条数据
	 * @param $gameuid
	 * @param $key_values array,名称做key，数值做value
	 */
 	protected function deleteFromDb($gameuid, $key_values){
    	if (empty($key_values))	return false;
    	
    	$req=RequestFactory::createDeleteRequest(get_app_config($gameuid));
    	
    	$req->setKey($this->getKeyName(),$gameuid);
		$req->setTable($this->getTableName());
		
		foreach ($key_values as $key=>$value) {
			$req->addKeyValue($key,$value);
		}
		$req->execute();
		return true;
    }
    
    protected function  getKeyName()
    {
    	return "gameuid";
    }
    abstract protected function getTableName();
}
?>
