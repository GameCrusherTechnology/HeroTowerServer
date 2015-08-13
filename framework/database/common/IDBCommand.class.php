<?php

interface IDBCommand {
	public function prepare($sql,$params = null);
	public function executeNonQuery();
	public function executeReader();
	public function executeScalar();
	public function getCommandText();
	public function setCommandText($text,$type = 'sql');
	public function getConnection();
	public function setConnection(IDBConnection $conn);
}

?>