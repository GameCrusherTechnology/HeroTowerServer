<?php
define('ELEX_AUTHENTICATE_STATUS_SUCCESS',0);
define('ELEX_AUTHENTICATE_STATUS_FAILURE',1);
define('ELEX_AUTHENTICATE_STATUS_CANCEL',2);

class Authentication {
	/**
	 * @var AuthenticationProvider
	 */
	protected $provider = null;
	
	public function setProvider(AuthenticationProvider $provider){
		$this->provider = $provider;
	}
	
	/**
	 * @return AuthenticationProvider
	 */
	public function getProvider() {
		return $this->provider;
	}

	/**
	 * 进行用户验证
	 * @param array $credentials
	 * @param array $options
	 * @return array
	 */
	public function authenticate($credentials, $options = null){
		$response = $this->provider->onAuthenticate($credentials,$options);
//		if($response->status != ELEX_AUTHENTICATE_STATUS_SUCCESS){
//			throw new Exception($response->error_message,$response->error_code);
//		}
		return $response;
	}
}

?>