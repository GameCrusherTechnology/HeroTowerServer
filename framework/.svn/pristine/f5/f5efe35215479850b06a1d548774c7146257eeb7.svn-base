<?php
class GameException extends Exception {
	protected $gameuid;
	public function __construct($message, $code) {
		$this->gameuid = $GLOBALS['gameuid'];
		$message = sprintf(
			"INFO[gameuid=%s,code:%d]\tmessage:%s",
			$this->gameuid,$code,$message);
		parent::__construct ( $message, $code );
	}
	
	public function getGameuid(){
		return $this->gameuid;
	}
	
	public function __toString(){
		return sprintf(
				"INFO[gameuid=%s,code:%d]\tmessage:%s",
				$this->gameuid,$this->code,$this->message);
	}
}
?>