<?php

/**
* Custom session storage handler for PHP
*
* @abstract
* @package		Framework
* @subpackage	Session
* @since		1.5
* @see http://www.php.net/manual/en/function.session-set-save-handler.php
*/
abstract class SessionStorage
{
	/**
	 * session有效的时间的秒数,默认是30分钟
	 * @var int
	 */
	protected $max_lifetime = 1800;
	/**
	* Constructor
	*
	* @access protected
	* @param array $options optional parameters
	*/
	public function __construct( $options = array() )
	{
		$this->register($options);
		if(isset($options['max_lifetime'])){
			$this->max_lifetime = intval($options['max_lifetime']);
		}
	}

	/**
	 * Returns a reference to a session storage handler object, only creating it
	 * if it doesn't already exist.
	 *
	 * @access public
	 * @param name 	$name The session store to instantiate
	 * @return database A JSessionStorage object
	 * @since 1.5
	 */
	public static function getInstance($name = 'none', $options = array())
	{
		static $instances;

		if (!isset ($instances)) {
			$instances = array ();
		}

		$name = strtolower($name);
		if (empty ($instances[$name]))
		{
			$class = 'SessionStorage'.ucfirst($name);
			if(!class_exists($class))
			{
				$path = FRAMEWORK . '/session/storage/' . $name . '.php';
				if (file_exists($path)) {
					include($path);
				} else {
					// No call to JError::raiseError here, as it tries to close the non-existing session
					exit('Unable to load session storage class: '.$name);
				}
			}

			$instances[$name] = new $class($options);
		}

		return $instances[$name];
	}

	/**
	* Register the functions of this class with PHP's session handler
	*
	* @access public
	* @param array $options optional parameters
	*/
	public function register( $options = array() )
	{
		// use this object as the session handler
		session_set_save_handler(
			array($this, 'open'),
			array($this, 'close'),
			array($this, 'read'),
			array($this, 'write'),
			array($this, 'destroy'),
			array($this, 'gc')
		);
	}

	/**
	 * Open the SessionHandler backend.
	 *
	 * @abstract
	 * @access public
	 * @param string $save_path     The path to the session object.
	 * @param string $session_name  The name of the session.
	 * @return boolean  True on success, false otherwise.
	 */
	abstract public function open($save_path, $session_name);

	/**
	 * Close the SessionHandler backend.
	 *
	 * @abstract
	 * @access public
	 * @return boolean  True on success, false otherwise.
	 */
	abstract public function close();

 	/**
 	 * Read the data for a particular session identifier from the
 	 * SessionHandler backend.
 	 *
 	 * @abstract
 	 * @access public
 	 * @param string $id  The session identifier.
 	 * @return string  The session data.
 	 */
	abstract public function read($id);

	/**
	 * Write session data to the SessionHandler backend.
	 *
	 * @abstract
	 * @access public
	 * @param string $id            The session identifier.
	 * @param string $session_data  The session data.
	 * @return boolean  True on success, false otherwise.
	 */
	abstract public function write($id, $session_data);

	/**
	  * Destroy the data for a particular session identifier in the
	  * SessionHandler backend.
	  *
	  * @abstract
	  * @access public
	  * @param string $id  The session identifier.
	  * @return boolean  True on success, false otherwise.
	  */
	abstract public function destroy($id);

	/**
	 * Garbage collect stale sessions from the SessionHandler backend.
	 *
	 * @abstract
	 * @access public
	 * @param integer $maxlifetime  The maximum age of a session.
	 * @return boolean  True on success, false otherwise.
	 */
	abstract public function gc($maxlifetime);

	/**
	 * Test to see if the SessionHandler is available.
	 *
	 * @abstract
	 * @static
	 * @access public
	 * @return boolean  True on success, false otherwise.
	 */
	abstract public function test();
}
