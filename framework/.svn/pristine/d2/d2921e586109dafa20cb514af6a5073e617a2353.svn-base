<?php
require_once FRAMEWORK . '/database/common/IDBReader.class.php';

class DBReader implements IDBReader {
	protected $res = null;
	public function __construct($resource){
		$this->res = $resource;
	}
	
	public function __destruct(){
		if(is_resource($this->res)){
			$this->freeResult();
		}
	}
	
	/**
	 * @see IDBReader::fetch()
	 *
	 */
	public function fetch() {
		return false;
	}

	/**
	 * @see IDBReader::fetchAll()
	 *
	 */
	public function fetchAll() {
		return false;
	}

	/**
	 * @see IDBReader::fetchOne()
	 *
	 */
	public function fetchOne() {
		return false;
	}

	/**
	 * @see IDBReader::freeResult()
	 *
	 */
	public function freeResult() {
		return false;
	}

	/**
	 * @see IDBReader::totalRows()
	 *
	 */
	public function totalRows() {
		return false;
	}

}

?>