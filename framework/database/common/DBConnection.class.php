<?php
require_once FRAMEWORK . '/database/common/IDBConnection.class.php';

abstract class DBConnection implements IDBConnection {
	protected $host = 'localhost';
	protected $port = 3306;
	protected $database = '';
	protected $username = '';
	protected $password = '';
	protected $charset = 'utf8';
	protected $pconnect = false;
	protected $connection = null;
	
	protected $dsn = null;
	protected $driver = '';
	protected $throwException = true;
	
	public function __construct($config = null){
		$this->setConfig($config);
	}
	
	/**
	 * 设置是否在出错时候抛出exception
	 * @param bool $value
	 */
	public function throwExceptionOnError($value = true){
		$this->throwException = $value;
	}
	
	public function setConfig($config){
		if(empty($config)){
			return;
		}
		if(isset($config['driver'])){
			$this->driver = $config['driver'];
		}
		if(isset($config['host'])){
			$this->host = $config['host'];
		}
		if(isset($config['port'])){
			$this->port = $config['port'];
		}
		if(isset($config['database'])){
			$this->database = $config['database'];
		}
		if(isset($config['username'])){
			$this->username = $config['username'];
		}
		if(isset($config['password'])){
			$this->password = $config['password'];
		}
		if(isset($config['charset'])){
			$this->charset = $config['charset'];
		}
		if(isset($config['pconnect'])){
			$this->pconnect = $config['pconnect'];
		}
	}
	
	abstract public function open();
	
	abstract public function close();
	
	abstract public function startTrans();
	
	abstract public function commit();
	
	abstract public function rollback();
	
	abstract public function createCommand();
	
	/**
	 * @see IDBConnection::getConnectionString()
	 *
	 */
	public function getConnectionString() {
		return $this->dns;
	}
	
	/**
	 * @see IDBConnection::setConnectionString()
	 *
	 */
	public function setConnectionString($dsn) {
		$this->dsn = $dsn;
		return $this->parseDSN();
	}
	
	protected function parseDSN(){
		if(empty($this->dsn)){
			return false;
		}
		$dsn = parse_url($this->dsn);
		if(empty($dsn)){
			return false;
		}
		$this->driver = $dsn['scheme'];
		$this->password = $dsn['pass'];
		$this->username = $dsn['user'];
		$this->database = substr($dsn['path'],1);
		if(isset($dsn['query'])){
			parse_str($dsn['query'],$opt);
			$this->setConfig($opt);
		}
		return true;
	}
	
	abstract public function errno();
	
	abstract public function error();
	
	public function errorMsg($msg = '', $sql = ''){
		$message = '';
		if($msg) {
			$message .=  "ErrorMsg:".$msg."\n";
		}
		if($sql) {
			$message .=  "SQL:".htmlspecialchars($sql)."\n";
		}
		$message .=  "Error:  ".$this->error()."\n";
		$message .=  "Errno: ".$this->errno();
		if($this->throwException){
			throw new exception($message, $this->errno());
		}
		trigger_error($message,E_USER_ERROR);
	}
	
	abstract public function query($sql,$errmsg = '',$silent = false);
}

?>