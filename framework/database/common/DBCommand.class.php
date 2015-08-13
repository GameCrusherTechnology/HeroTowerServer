<?php
require_once (FRAMEWORK . '/database/common/IDBCommand.class.php');

abstract class DBCommand implements IDBCommand {
	protected $cmd_text;
	protected $cmd_type ;
	protected $connection;
	
	public function __construct($cmd_text,$conn = null){
		$this->setCommandText($cmd_text);
		if($conn){
			$this->setConnection($conn);
		}
	}
	
	abstract public function executeNonQuery();
	
	abstract public function executeReader();
	
	abstract public function executeScalar() ;
	
	public function getCommandText() {
		return $this->cmd_text;
	}
	
	public function getConnection() {
		return $this->connection;
	}
	
	public function prepare($sql, $params = null) {
		if(empty($params)){
			$this->cmd_text = $sql;
		}
		elseif(is_array($params)){
			$this->cmd_text = vsprintf($sql,$params);
		}
		else{
			$this->cmd_text = sprintf($sql,$params);
		}
	}
	
	public function setCommandText($text, $type = 'sql') {
		$this->cmd_text = $text;
		$this->cmd_type = $type;
	}
	
	public function setConnection(IDBConnection $conn) {
		$this->connection = $conn;
	}
}

?>