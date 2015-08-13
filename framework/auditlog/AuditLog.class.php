<?php
class AuditLog {
	/**
	 * @var DBHelper2
	 */
	protected $dbo = null;
	protected $table_name = 'admin_log';
	
	public function __construct($config = null){
		$this->setConfig($config);
	}
	
	public function setConfig($config){
		if(isset($config['dbo'])){
			$this->dbo = $config['dbo'];
		}
		if(isset($config['table_name'])){
			$this->table_name = $config['table_name'];
		}
	}
	
	public function write($uid,$action_type,$action_detail,$other_info = null){
		$insert_data = array('uid' => $uid,'action_type' => $action_type,
		'action_detail' => $action_detail);
		if(!empty($other_info)){
			$insert_data = array_merge($insert_data,$other_info);
		}
		$id = $this->dbo->inserttable($this->table_name,$insert_data,true);
		return $id;
	}
}

?>