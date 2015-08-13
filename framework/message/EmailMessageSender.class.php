<?php
require_once FRAMEWORK . '/message/email/phpMailer/phpmailer.class.php';

require_once ( FRAMEWORK . '/message/IMessageSender.class.php');

class EmailMessageSender implements IMessageSender {
	private $mailer;
	public function __construct($config) {
		$this->mailer = new PHPMailer ( );
		$this->setOptions($config);
	}
	
	private function setOptions($config) {
		if (isset ( $config ['type'] )) {
			switch ($config ['type']) {
				case 'smtp' :
					$this->mailer->IsSMTP ();
					break;
				default :
					$this->mailer->IsMail ();
					break;
			}
		}
	}
	
	public function sendMessage($subject,$message) {
		$this->mailer->Subject = $subject;
		$this->mailer->Body = $message;
		return $this->mailer->Send();
	}
	
	public function setMessageReceiver($receivers) {
		if(is_array($receivers)){
			foreach ($receivers as $receiver) {
				$this->mailer->AddAddress($receiver);
			}
		}
		else{
			$this->mailer->AddAddress($receivers);
		}
	}
}

?>