<?php
interface IMessageSender{
	public function sendMessage($subject,$message);
	public function setMessageReceiver($receivers);
}
?>