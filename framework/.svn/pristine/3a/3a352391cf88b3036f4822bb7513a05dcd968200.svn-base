<?php

/**
 * Memcache session storage handler for PHP
 *
 * -- Inspired in both design and implementation by the Horde memcache handler --
 *
 * @package		Framework
 * @subpackage	Session
 * @since		1.5
 * @see http://www.php.net/manual/en/function.session-set-save-handler.php
 */
class SessionStorageMemcache extends SessionStorage
{
	/**
	 * Resource for the current memcached connection.
	 *
	 * @var resource
	 */
	protected $_db;

	/**
	 * Use compression?
	 *
	 * @var int
	 */
	protected $_compress = null;

	/**
	 * Use persistent connections
	 *
	 * @var boolean
	 */
	protected $_persistent = false;

	/**
	* Constructor
	*
	* @access protected
	* @param array $options optional parameters
	*/
	public function __construct( $options = array() )
	{

		parent::__construct($options);

		$params = $options;
		$this->_compress	= (isset($params['compression'])) ? $params['compression'] : 0;
		$this->_persistent	= (isset($params['persistent'])) ? $params['persistent'] : false;

		// This will be an array of loveliness
		if(array_key_exists('servers',$params)){
			$this->_servers	= $params['servers'];
		}
		if($params['cache']){
			$this->_db = $params['cache'];
		}
	}

	/**
	 * Open the SessionHandler backend.
	 *
	 * @access public
	 * @param string $save_path     The path to the session object.
	 * @param string $session_name  The name of the session.
	 * @return boolean  True on success, false otherwise.
	 */
	public function open($save_path, $session_name)
	{
		if(empty($this->_db)){
			require_once FRAMEWORK . '/cache/Cache.class.php';
			$this->_db = new Cache($this->_servers);
		}
		return true;
	}

	/**
	 * Close the SessionHandler backend.
	 *
	 * @access public
	 * @return boolean  True on success, false otherwise.
	 */
	public function close()
	{
		return $this->_db->close();
	}

 	/**
 	 * Read the data for a particular session identifier from the
 	 * SessionHandler backend.
 	 *
 	 * @access public
 	 * @param string $id  The session identifier.
 	 * @return string  The session data.
 	 */
	public function read($id)
	{
		$sess_id = 'sess_'.$id;
		$this->_setExpire($sess_id);
		return $this->_db->get($sess_id);
	}

	/**
	 * Write session data to the SessionHandler backend.
	 *
	 * @access public
	 * @param string $id            The session identifier.
	 * @param string $session_data  The session data.
	 * @return boolean  True on success, false otherwise.
	 */
	public function write($id, $session_data)
	{
		$sess_id = 'sess_'.$id;
		$this->_db->set($sess_id.'_expire', time(), 0);
		$this->_db->set($sess_id, $session_data,0, $this->_compress);
		return;
	}

	/**
	  * Destroy the data for a particular session identifier in the
	  * SessionHandler backend.
	  *
	  * @access public
	  * @param string $id  The session identifier.
	  * @return boolean  True on success, false otherwise.
	  */
	public function destroy($id)
	{
		$sess_id = 'sess_'.$id;
		$this->_db->delete($sess_id.'_expire');
		return $this->_db->delete($sess_id);
	}

	/**
	 * Garbage collect stale sessions from the SessionHandler backend.
	 *
	 *	-- Not Applicable in memcache --
	 *
	 * @access public
	 * @param integer $maxlifetime  The maximum age of a session.
	 * @return boolean  True on success, false otherwise.
	 */
	public function gc($maxlifetime)
	{
		return $maxlifetime;
	}

	/**
	 * Test to see if the SessionHandler is available.
	 *
	 * @static
	 * @access public
	 * @return boolean  True on success, false otherwise.
	 */
	public function test()
	{
		return (extension_loaded('memcache') && class_exists('Memcache'));
	}

	/**
	 * Set expire time on each call since memcache sets it on cache creation.
	 *
	 * @access private
	 *
	 * @param string  $key   Cache key to expire.
	 * @param integer $lifetime  Lifetime of the data in seconds.
	 */
	public function _setExpire($key)
	{
		$lifetime	= $this->max_lifetime;
		$expire		= $this->_db->get($key.'_expire');

		// set prune period
		if ($expire + $lifetime < time()) {
			$this->_db->delete($key);
			$this->_db->delete($key.'_expire');
		} else {
			$this->_db->set($key.'_expire', time(),0);
		}
	}
}
