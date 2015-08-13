<?php
require_once GAMELIB . '/common.func.php';
class GameBase {
	/**
	 * 抛出一个异常
	 * @param $message
	 * @param $code
	 * @param $uid
	 * @param $gameuid
	 * @return void
	 */
	protected function throwException($message,$code=GameStatusCode::DATA_ERROR){
		throw new GameException($message,$code);
	}
	
	/**
	 * @return bool the $debug
	 */
	public function isDebug($gameuid) {
		return get_app_config($gameuid)->isDebugMode();
	}

	/**
	 * 是否运行在性能测试模式下
	 * @return the $perf_test_mode
	 */
	public function isPerfTestMode($gameuid = null) {
		return get_app_config($gameuid)->isPerfTestMode();
	}

	/**
	 * 是否关闭了服务
	 * @return bool
	 */
	public function isServiceClosed($gameuid = null) {
		return get_app_config($gameuid)->isServiceClosed();
	}
	
	

}
