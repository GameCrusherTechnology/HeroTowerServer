<?php
require_once ( FRAMEWORK .'/database/DBConnection.class.php');

class MySQLDBConnection extends DBConnection {

	/**
	 * @see DBConnection::errno()
	 *
	 */
	public function errno() {
		return mysql_errno($this->connection);
	}

	/**
	 * @see DBConnection::error()
	 *
	 */
	public function error() {
		return mysql_error($this->connection);
	}
	/**
	 * @see DBConnection::close()
	 *
	 */
	public function close() {
		if(is_resource($this->connection)){
			mysql_close($this->connection);
		}
	}
	
	/**
	 * @see DBConnection::createCommand()
	 *
	 */
	public function createCommand($cmdText = '') {
		$cmd = new MySQLCommand($cmdText,$this);
		return $cmd;
	}
	
	/**
	 * @see DBConnection::open()
	 *
	 */
	public function open() {
		$this->connection = mysql_connect($this->host,$this->username,$this->password);
		if(!$this->connection){
			$this->errorMsg('Connect to MySQL server failure');
		}
		if($this->charset){
			$this->query('set names ' . $this->charset);
		}
		if($this->database){
			if(mysql_select_db($this->database,$this->connection) === false){
				$this->errorMsg("Can't use database $this->database.");
			}
		}
	}
	
	/**
	 * @see DBConnection::startTrans()
	 *
	 */
	public function startTrans() {
		$this->query('start transaction','start transaction failure.');
	}

	/**
	 * @see DBConnection::commit()
	 *
	 */
	public function commit() {
		$this->query('commit','commit error');
	}

	/**
	 * @see DBConnection::rollback()
	 *
	 */
	public function rollback() {
		$this->query('rollback','rollback error');
	}

	/**
	 * 执行一个查询
	 * @param string $sql 将要执行的sql
	 * @param string $errmsg 执行出错的错误消息
	 * @param bool $silent 是吗出错之后不做处理
	 * @return resource mysql query resource
	 */
	public function query($sql,$errmsg = '',$silent = false){
		$re = mysql_query($sql,$this->connection);
		if(!$silent && $re === false){
			$this->errorMsg($errmsg,$sql);
		}
		return $re;
	}
}

?>