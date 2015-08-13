<?php
require_once FRAMEWORK . '/platform/alisoft/AliTribeService.class.php';
require_once FRAMEWORK . '/platform/alisoft/Auth.class.php';

class TribAuth extends Auth {
	
	public function __construct() {
		//parent::_construct();
	}
	/**
	 * Automatic login user.
	 *
	 * @return boolean
	 */
	public function autologin() {
		$trib_info = $this->authenticate();
		writeDebug('trib info:' . print_r($trib_info, true));
		if($trib_info === false){
			return false;
		}
		set_to_session(LOGIN_USER,$trib_info,$trib_info['group_id']);
		return $trib_info;
	}
	/**
	 * 验证群用户的有效性，如果用户是阿里平台用户，则返回用户的Id，否则返回false。
	 *
	 * @return mixed 如果验证成功，返回群的Id（string），否则返回false。
	 */
	public function authenticate(){
		$user_id = getRequestParam ( 'user_id' );
		$app_instance_id = getRequestParam ( 'app_instance_id' );
		$token = getRequestParam ( 'token' );
		// validate user by REST service
		$ret = AliTribeService::validateUser ( $user_id, $app_instance_id, $token );
		if($ret['result'] >= '0'){
			$tribInfo['group_id'] = $ret['tribeId'];
			$tribInfo['user_id'] = $user_id;
			$tribInfo['role'] = $this->getUserRole($ret['result']);
			return $tribInfo;
		}
		else{
			return false;
		}
	}
	
	private function getUserRole($code){
		switch ($code) {
			case '3':
				$role = 'host';
				break;
			case '2':
				$role = 'manager';
				break;
			case '1':
				$role = 'user';
				break;
			case '0':
				$role = 'visitor';
				break;
			default:
				$role = 'unauth';
				break;
		}
		return $role;
	}
}

?>