<?php

class StackFrame {
	private $stack_info;
	
	public function __construct($stack){
		$this->stack_info = $stack;
	}
	
	public function getFileName(){
		return $this->stack_info['file'];
	}
	
	public function getFileLine(){
		return $this->stack_info['line'];
	}
	
	public function getFunction(){
		return $this->stack_info['function'];
	}
	
	public function getObject(){
		return $this->stack_info['object'];
	}
	
	public function getType(){
		return $this->stack_info['type'];
	}
	public function getArgs(){
		return $this->stack_info['args'];
	}
	public function getClass(){
		return $this->stack_info['class'];
	}
}

?>