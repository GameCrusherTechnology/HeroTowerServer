<?php
class TesterControl {
	/**
	 * 数据库操作类
	 *
	 * @var DBHelper2
	 */
	private $dbhelper;
	/**
	 * 存储测试用户的表名
	 *
	 * @var test_user
	 */
	private $table_name = 'test_user';
	
	public function __construct($config=null){
		$this->setConfig($config);
	}
	/**
	 * 设置数据库操作类
	 * @param array $config
	 * 数据库操作类的key是dbo
	 */
	public function setConfig($config){
		if(is_array($config) 
			&& isset($config['dbo'])){
			if($config['dbo'] instanceof DBHelper ||
				$config['dbo'] instanceof DBHelper2){
				$this->dbhelper = $config['dbo'];
			} else {
				throw new Exception('dbo config error: type must be DBHelper or DBHelper2');
			}
		} else {
			$appConfigInstance = get_app_config();
        	$this->dbhelper = $appConfigInstance->getTableServer($this->table_name)->getDBHelperInstance();
       	 	if (empty($this->dbhelper)) {
       	 		throw new Exception('config error: dbo not set');
       	 	}
		}
	}
	public function getTester($uid){
		if (empty($uid)) return false;
		$sql = "select * from %s where uid = '%s'";
		$sql = sprintf($sql, $this->table_name, $uid);
		return $this->dbhelper->fetchOne($sql);
	}
	public function addTester($uids){
		if (empty($uids)) return false;
		$sql = "insert ignore into ".$this->table_name." (uid) values ";
		$uids = explode(',', $uids);
		foreach ($uids as $uid){
			$sql .= "('$uid'),";
		}
		$sql = trim($sql,',');
		return $this->dbhelper->execute($sql);
	}
	public function deleteTester($uid){
		if (empty($uid)) return false;
		$sql = "delete from %s where uid in (%s)";
		$sql = sprintf($sql, $this->table_name, $uid);
		return $this->dbhelper->execute($sql);
	}
	private function throwException($message,$code){
		throw new GameException($message,$code);
	}
}
?>