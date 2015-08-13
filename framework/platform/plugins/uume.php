<?php
require_once FRAMEWORK . '/platform/plugins/XiaoneifulAPI.class.php';

class API_Xiaonei extends XiaoneifulAPI{

	public function __construct($api_key,$secret,$session_key,$v='1.0')
	{
		parent::__construct($api_key, $secret, $session_key, $v);
		$this->server_addr='http://api.uume.jp/restserver.do';
		$this->method_prefix = 'xiaonei';
	}
}
