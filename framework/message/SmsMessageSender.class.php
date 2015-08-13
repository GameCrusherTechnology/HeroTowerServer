<?php
require_once FRAMEWORK . '/message/fetion/Fetion.class.php';
require_once (FRAMEWORK . '/message/IMessageSender.class.php');

class SmsMessageSender implements IMessageSender {
	private $sender;
	private $receivers;
	public function __construct($config) {
		$this->sender = new Fetion ( );
		//$config['phone'] = '13811922746';
		//$config['password'] = 'ssl-304';
		$this->sender->phone_num = $config ['phone'];
		$this->sender->password = $config ['password'];
		$this->sender->sip_login ();
	}
	
	public function __destruct(){
		$this->sender->sip_logout ();
	}
	
	public function sendMessage($subject,$message) {
		$this->sender->sendSMS_toPhone ( '',$subject . "\n". $message );
	}
	
	public function setMessageReceiver($receivers) {
		$this->receivers = $receivers;
	}
}

?>