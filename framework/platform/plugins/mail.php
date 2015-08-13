<?php
class Mail {
	  public $secret;
	  public $session_key;
	  public $api_key;
	  public $version;
	  public $server_addr = 'http://api.kaixin.com/restserver.do';
	  public $method;
	
	public function __construct($api_key,$secret,$v='1.0') 
	{	
		$this->secret       = $secret;
		$this->api_key  = $api_key;
		$this->v=$v;
		//$this->session_key = MooGetGPC('xn_sig_session_key', 'string');
		
		$this->server_addr='http://api.kaixin.com/restserver.do';

	}
}
?>