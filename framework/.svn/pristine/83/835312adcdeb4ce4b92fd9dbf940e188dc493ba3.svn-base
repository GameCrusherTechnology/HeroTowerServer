<?php

abstract class DbAdapterAbstract {
	/**
	 * 剖析类实例
	 * @var DbProfiler
	 */
	protected $profiler = null;
	
	protected $connection = null;
	
	protected $fetchMode;
	/**
	 * @return DbProfiler the $profiler
	 */
	public function getProfiler() {
		return $this->profiler;
	}

	/**
	 * @param $profiler the $profiler to set
	 */
	public function setProfiler($profiler) {
		$this->profiler = $profiler;
	}
	/**
	 * @param $connection the $connection to set
	 */
	public function setConnection($connection) {
		$this->connection = $connection;
	}

	/**
	 * @return the $connection
	 */
	public function getConnection() {
		return $this->connection;
	}

    /**
     * Get the fetch mode.
     *
     * @return int
     */
    public function getFetchMode()
    {
        return $this->fetchMode;
    }
	
	public function fetchAll(){
		
	}
	
	public function fetchAssoc(){
		
	}
	
	public function fetchPairs(){
		
	}
	
	public function fetchRow(){
		
	}
	
	public function fetchColumn(){
		
	}
	public function insert(){
		
	}
	
	public function update(){
		
	}
	
	public function delete(){
		
	}
	
	public function select(){
		
	}
	
	/**
     * Creates a connection to the database.
     *
     * @return void
     */
    abstract protected function _connect();

    /**
     * Test if a connection is active
     *
     * @return boolean
     */
    abstract public function isConnected();

    /**
     * Force the connection to close.
     *
     * @return void
     */
    abstract public function closeConnection();
	
	abstract public function prepare($sql);
	
	/**
     * Retrieve server version in PHP style
     *
     * @return string
     */
    abstract public function getServerVersion();
}

?>