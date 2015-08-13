<?php

class StackTrace {
	private $frames;
	public function __construct(Exception $e = null) {
		if($e != null){
			$this->frames = $e->getTrace();
		}
		else{
			$this->frames = debug_backtrace();
		}
	}
	
	public function getFrame($index){
		if(isset($this->frames[$index])){
			return $this->frames[$index];
		}
		else{
			return null;
		}
	}
	
	public function getFrames(){
		return $this->frames;
	}
	
	public function getFrameCount(){
		return count($this->frames);
	}
}

?>