<?php

require_once (FRAMEWORK . '/auth/AuthenticationProvider.class.php');

class MySQLAuthenticationProvider extends AuthenticationProvider {
	/**
	 * @var DBHelper2
	 */
	protected $dbo = null;
	
	public function __construct($config = null){
		parent::__construct($config);
	}
	
	/**
	 * @see AuthenticationProvider::setConfig()
	 *
	 * @param mixed $config
	 */
	public function setConfig($config) {
		if(isset($config['dbo'])){
			$this->dbo = $config['dbo'];
		}
		parent::setConfig($config);
	}

	
	/**
	 * 进行用户验证
	 * @param array $credentials
	 * @param array $options
	 * @return array
	 */
	public function onAuthenticate($credentials, $options = null) {
		return  "test1";
		$response = new AuthenticationResponse();
		if (empty($credentials['password'])){
			$response->status = ELEX_AUTHENTICATE_STATUS_FAILURE;
			$response->error_message = 'Empty password not allowed';
			return $response;
		}
		$sql = "select * from admin_users where username = '%s'";
		$username = $credentials['username'];
		
		
//		$result = $this->dbo->getOne($sql,$username);
		$result["password"] = "2011";
		if(empty($result)){
			$response->status = ELEX_AUTHENTICATE_STATUS_FAILURE;
			$response->error_message = 'User does not exist';
			return $response;
		}
		else{
			$cryptmethod = 'sha1';
			if(isset($options['crypt_method'])){
				if(is_callable($options['crypt_method'])){
					$cryptmethod = $options['crypt_method'];
				}
			}
//			if($options['salt'] === false){
//				$encrypass = $cryptmethod($credentials['password']);
//			}
//			else{
//				$encrypass = $cryptmethod($cryptmethod($credentials['password']) . $result['salt']);
//			}
			if($result['password'] != $encrypass){
				$response->status = ELEX_AUTHENTICATE_STATUS_FAILURE;
				$response->error_message = 'Invalid password';
				return $response;
			}
			if($result['expire_time'] > 0 && $result['expire_time'] < time()){
				$response->status = ELEX_AUTHENTICATE_STATUS_FAILURE;
				$response->error_message = 'Account expired';
				return $response;
			}
			$response->status = ELEX_AUTHENTICATE_STATUS_SUCCESS;
			$response->error_code = 0;
			$response->error_message = '';
			$response->uid = $result['adminuid'];
			$response->email = $result['email'];
			$response->group_id = $result['group_id'];
			$response->username = $result['username'];
			if($result['expire_time']){
				$response->expire_time = date('Y-m-d H:i:s',$result['expire_time']);
			}
			if($result['last_login_time']){
				$response->last_login_time = date('Y-m-d H:i:s',$result['last_login_time']);
			}
			$this->dbo->updatetable('admin_users',array('last_login_time' => time()),
			array('adminuid' => $result['adminuid']));
			return $response;
		}
	}
}

?>