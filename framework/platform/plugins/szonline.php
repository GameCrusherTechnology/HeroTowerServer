<?php
include_once FRAMEWORK.'/osapi/osapi.php';

//opensocial设置

class szonline_api {
	public $osapi;
	public $provider;
	function __construct($sig_api_key,$sig_secret,$userId){
		$osapi = false;
		$strictMode = true;
		session_start();
		$localUserId = session_id();
		
		$url = array();
	  	$url['requestTokenUrl'] = "http://partuza.gzonline.net/oauth/request_token";
	  	$url['authorizeUrl'] = "http://partuza.gzonline.net/oauth/authorize";
	  	$url['accessTokenUrl'] = "http://partuza.gzonline.net/oauth/access_token";
	  	$url['restEndpoint'] = "http://shindig.gzonline.net/social/rest";
	  	$url['rpcEndpoint'] = "http://shindig.gzonline.net/social/rpc";
	  	
		$provider = new osapiPartuzaProvider(null,$url);
		$login_sig = new osapiOAuth2Legged($sig_api_key,$sig_secret,$userId);
		$this->osapi = new osapi($provider, $login_sig);
	}
}

?>