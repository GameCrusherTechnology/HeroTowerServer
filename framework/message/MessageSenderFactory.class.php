<?php

class MessageSenderFactory {
	public static function CreateMessageSender($type = 'sms', $config) {
		switch ($type) {
			case 'email':
				require_once 'EmailMessageSender.class.php';
				$ins = new EmailMessageSender($config);
				break;
			default:
				require_once 'SmsMessageSender.class.php';
				$ins = new SmsMessageSender($config);
				break;
		}
		return $ins;
	}
}

?>