<?php
include_once FRAMEWORK.'/osapi/osapi.php';

//opensocial设置

class szonline_api {
	public $osapi;
	public $provider;
	function __construct($sig_api_key,$sig_secret,$userId){
		$osapi = false;
		$strictMode = true;
		$provider = new osapiOrkutProvider();
		$provider->rpcEndpoint = null;
		$sig_login = new osapiOAuth2Legged("orkut.com:$sig_api_key", "$sig_secret", $userId);
		$this->osapi = new osapi($provider, $sig_login);
	}
}

?>