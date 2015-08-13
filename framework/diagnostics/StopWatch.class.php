<?php
class StopWatch {
	private $running = false;
	private $time_start = 0;
	private $time_end = 0;
	private $total_time = 0;
	public function start(){
		$this->running = true;
		$this->time_start = $this->getTime();
	}
	
	public function stop(){
		$this->running = false;
		$this->time_end = $this->getTime();
		$this->total_time += $this->time_end - $this->time_start;
	}
	
	public function reset() {
		$this->stop();
		$this->total_time = 0;
		$this->start();
	}
	
	public function getElapsed() {
		if($this->running){
			return $this->getTime() - $this->time_start;
		}else{
			return $this->total_time;
		}
	}
	
	public function getElapsedMilliseconds() {
		return $this->getElapsed() * 1000;
	}
	
	public function isRunning(){
		return $this->running;
	}
	
	public static function startNew(){
		$watch = new StopWatch();
		$watch->start();
		return $watch;
	}
	
	private function getTime(){
		return microtime(true);
	}
}

?>