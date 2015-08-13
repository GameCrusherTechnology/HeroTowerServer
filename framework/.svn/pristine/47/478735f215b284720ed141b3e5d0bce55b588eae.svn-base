<?php

abstract class AuthenticationProvider {
	protected $config = null;
	public function __construct($config = null){
		if(!empty($config)){
			$this->setConfig($config);
		}
	}
	/**
	 * 设置验证所需的设置
	 * @package framework.auth
	 * @param $config mixed 可以是一个数组，或者是设置数据库连接的dsn
	 */
	public function setConfig($config){
		$this->config = $config;
	}
	/**
	 * do user authentication
	 *
	 * @param $credentials array   username,password information
	 * @param $options array
	 * @return AuthenticationResponse
	 */
	abstract public function onAuthenticate($credentials, $options = null);
}

class AuthenticationResponse
{
	/**
	 * Response status (see status codes)
	 *
	 * @var type string
	 * @access public
	 */
	public $status 		= ELEX_AUTHENTICATE_STATUS_FAILURE;

	/**
	 * The type of authentication that was successful
	 *
	 * @var type string
	 * @access public
	 */
	public $type 		= '';

	/**
	 *  The error message
	 *
	 * @var error_message string
	 * @access public
	 */
	public $error_message 	= '';
	
	/**
	 *  The error code
	 *
	 * @var error_code string
	 * @access public
	 */
	public $error_code 	= '0';

	/**
	 * Any UTF-8 string that the End User wants to use as a username.
	 *
	 * @var fullname string
	 * @access public
	 */
	public $username 		= '';

	/**
	 * Any UTF-8 string that the End User wants to use as a password.
	 *
	 * @var password string
	 * @access public
	 */
	public $password 		= '';

	/**
	 * The email address of the End User as specified in section 3.4.1 of [RFC2822]
	 *
	 * @var email string
	 * @access public
	 */
	public $email			= '';

	/**
	 * UTF-8 string free text representation of the End User's full name.
	 *
	 * @var fullname string
	 * @access public
	 */
	public $fullname 		= '';

	/**
	 * The End User's date of birth as YYYY-MM-DD. Any values whose representation uses
	 * fewer than the specified number of digits should be zero-padded. The length of this
	 * value MUST always be 10. If the End User user does not want to reveal any particular
	 * component of this value, it MUST be set to zero.
	 *
	 * For instance, if a End User wants to specify that his date of birth is in 1980, but
	 * not the month or day, the value returned SHALL be "1980-00-00".
	 *
	 * @var fullname string
	 * @access public
	 */
	public $birthdate	 	= '';

	/**
	 * The End User's gender, "M" for male, "F" for female.
	 *
	 * @var fullname string
	 * @access public
	 */
	public $gender 		= '';

	/**
	 * UTF-8 string free text that SHOULD conform to the End User's country's postal system.
	 *
	 * @var fullname string
	 * @access public
	 */
	public $postcode 		= '';

	/**
	 * The End User's country of residence as specified by ISO3166.
	 *
	 * @var fullname string
	 * @access public
	 */
	public $country 		= '';

	/**
	 * End User's preferred language as specified by ISO639.
	 *
	 * @var fullname string
	 * @access public
	 */
	public $language 		= '';

	/**
	 * ASCII string from TimeZone database
	 *
	 * @var fullname string
	 * @access public
	 */
	public $timezone 		= '';
	
	public $expire_time = '';
	
	public $last_login_time = '';
	
	public $uid = '';
	
	public $group_id = '';
	
	public $fail_times = 0;

}
?>