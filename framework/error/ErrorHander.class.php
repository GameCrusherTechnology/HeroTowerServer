<?php
class ErrorHandler {
	
	public function __construct($logger = null){
		$this->logger = $logger;
		$this->setErrorHandler();
	}
	
	/**
	 * 设置error handler
	 */
	public function setErrorHandler(){
		set_error_handler(array($this,'customErrorHander'),
		$this->reportErrorLevel);
	}
	/**
	 * @var ILogger
	 */
	protected $logger = null;
	/**
	 * 消息发送器。可以是SMS，Email等
	 *
	 * @var IMessageSender
	 */
	protected $messageSender;
	
	protected $reportErrorLevel = E_ERROR;
	
	/**
	 * 取得需要报告的错误级别。
	 *
	 * @return int PHP错误级别常量
	 */
	public function getReportErrorLevel() {
		return $this->reportErrorLevel;
	}
	
	/**
	 * 设置需要报告的错误级别。
	 *
	 * @param int $reportErrorLevel PHP错误级别常量
	 */
	public function setReportErrorLevel($reportErrorLevel) {
		$this->reportErrorLevel = $reportErrorLevel;
		error_reporting($this->reportErrorLevel);
	}
	
	public function customErrorHander($errno,$errstr,$errfile,$errline){
		$msg = sprintf("%s: \nIn file %s line %s\nMessage:%s",
		$this->getErrorLevel($errno),$errfile,$errline,$errstr);
		if ($this->logger){
			$this->logger->writeError($msg);
		}
	}
	
	public static function getErrorLevel($errno){
		static $errortype = array (
                E_ERROR              => 'Error',
                E_WARNING            => 'Warning',
                E_PARSE              => 'Parsing Error',
                E_NOTICE             => 'Notice',
                E_CORE_ERROR         => 'Core Error',
                E_CORE_WARNING       => 'Core Warning',
                E_COMPILE_ERROR      => 'Compile Error',
                E_COMPILE_WARNING    => 'Compile Warning',
                E_USER_ERROR         => 'User Error',
                E_USER_WARNING       => 'User Warning',
                E_USER_NOTICE        => 'User Notice',
                E_STRICT             => 'Runtime Notice',
                E_RECOVERABLE_ERRROR => 'Catchable Fatal Error'
        );
		return $errortype[$errno];
	}
}

?>