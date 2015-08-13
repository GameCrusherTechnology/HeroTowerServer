<?php
require_once 'common.php';
class BaseCommand extends API_Base{
	
// 请求分发
	public function callCommand($request)
	{
		$real_command = $request['commandName'];
		
		$tmp_command = explode('/',$real_command);
		if(!empty($tmp_command[1])){
			$command = $tmp_command[1];
			$route = $tmp_command[0];
			return $this->invoke(__FUNCTION__, $route, $command, $request);
		}else{
			GameException::throwException('command:'.$real_command.' not exist',3,0,0);
		}
	}
}
?>