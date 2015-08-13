<?php
class IDSequenceTable{
	protected $table_name;
	protected $id_field;
	protected $data_exists = false;
	protected $step = 1000;
	/**
	 * 数据库操作类
	 *
	 * @var DBHelper
	 */
	protected $dbhelper;
	public function __construct($table_name,$id_field = null,$data_exist = false){
		$this->setParams($table_name,$id_field,$data_exist);
	}
	/**
	 * 设置配置项
	 * @param $config 包含一个key为dbo的DBHelper或者DBHelper2的对象实例
	 * @return void
	 */
	public function setConfig($config){
		if(isset($config['dbo']) &&
		$config['dbo'] instanceof DBHelper ||
		$config['dbo'] instanceof DBHelper2){
			$this->dbhelper = $config['dbo'];
		}else{
			throw new Exception('config error:dbo not set or type error');
		}
	}
	
	protected function setParams($table_name,$id_field=null,$data_exists = false){
		if(empty($table_name)){
			throw new Exception("Table name can't be empty.",1);
		}
		$this->table_name = $table_name;
		$this->id_field = $id_field;
		$this->data_exists = $data_exists;
	}
	
	/**
	 * @see IDSequence::getCurrentId()
	 *
	 * @return int
	 */
	public function getCurrentId() {
		$sql = "select current_value from id_sequence where id_key='%s'";
		return $this->dbhelper->getOne($sql,$this->table_name);
	}
	
	/**
	 * @see IDSequence::getNextId()
	 *
	 * @return int
	 */
	public function getNextId() {
		return $this->increaseId(1);
	}
	
	/**
	 * @see IDSequence::increaseId()
	 *
	 * @param int $inc
	 * @return int
	 */
	public function increaseId($inc = 1) {
		try{
			$this->dbhelper->startTransaction();
			$sql = "select current_value from id_sequence where id_key='%s' for update";
			$current_value = $this->dbhelper->getOne($sql,$this->table_name);
			if(empty($current_value)){
				$sql = "insert into id_sequence (id_key,current_value)values('%s',%d)";
				$this->dbhelper->execute($sql,$this->table_name,$inc);
				$next_id = $inc;
			}else{
				$sql = "update id_sequence set current_value = current_value + %d where id_key = '%s'";
				$this->dbhelper->execute($sql,$inc,$this->table_name);
				$next_id = $current_value + $inc;
			}
			$this->dbhelper->commit();
		}catch(Exception $e){
			$this->dbhelper->rollback();
			throw $e;
		}
		return $next_id;
	}
	
	/**
	 * @see IDSequence::initSeq()
	 *
	 * @param string $id_field
	 * @return int
	 */
	public function initSeq($id_field) {
		// 如果不存在数据则从id sequence表中取下一次开始的值
		if( $this->data_exists){
			// 如果数据存在，则从数据库中取得最大值
			$sql = sprintf("select ifnull(max(%s),0) max_value from %s",$id_field,$this->table_name);
			$value = $this->dbhelper->resultFirst($sql);
		}
		else{
			// 如果没有初始化id sequence表，则设置开始值为0
			$value = 0;
		}
		$sql = "insert into id_sequence (id_key,current_value,next_value) values('%s',%d,%d)
		 on duplicate key update current_value=%d,next_value=%d";
		$params =array($this->table_name,$value,$value + $this->step,
		$value,$value + $this->step);
		$this->dbhelper->execute($sql,$params);
		return $value;
	}

}

?>