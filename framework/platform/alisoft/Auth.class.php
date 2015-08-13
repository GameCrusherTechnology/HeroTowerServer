<?php
require_once FRAMEWORK . '/platform/alisoft/AlisoftValidateUserService.class.php';
require_once FRAMEWORK . '/session/Session.class.php';

class Auth{
	
	/**
	 * http session manager
	 *
	 * @var Session
	 */
	protected $session;
	
	/**
	 * Automatic login user.
	 *
	 * @return boolean
	 */
	public function autologin() {
		$this->session = getSession();
			
		$user_id = $this->authenticate();
		if($user_id === false){
			return false;
		}
		
		$this->session->set("user_id",$user_id);
		return true;
	}
		
	/**
	 * 验证用户的有效性，如果用户是阿里平台用户，则返回用户的Id，否则返回false。
	 *
	 * @return mixed 如果验证成功，返回用户的Id（string），否则返回false。
	 */
	public function authenticate(){
		$user_id = getRequestParam ( 'user_id' );
		$app_instance_id = getRequestParam ( 'app_instance_id' );
		$token = getRequestParam ( 'token' );
		// validate user by REST service
		$ret_code = AlisoftValidateUserService::validateUser ( $user_id, $app_instance_id, $token );
		// 该用户是应用的订购者,返回用户的Id
		if ($ret_code == '1') {
			return $user_id;
		}
		else{
			return false;
		}
	}
}

?>