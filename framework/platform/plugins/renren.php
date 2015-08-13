<?php
require_once FRAMEWORK . '/platform/plugins/XiaoneifulAPI.class.php';

class API_Renren extends XiaoneifulAPI {
	public function __construct($api_key, $secret, $session_key, $v = '1.0') {
		parent::__construct($api_key, $secret, $session_key, $v);
		$this->server_addr = 'http://api.renren.com/restserver.do';
		$this->method_prefix = 'xiaonei';
	}
}
