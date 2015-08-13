<?php

class Environment {
	public static function checkEnvironment($check_list){
		$result = array();
		if(isset($check_list['modules'])){
			$result['modules'] = self::checkModule($check_list['modules']);
		}
		if(isset($check_list['class'])){
			$result['class'] = self::checkModule($check_list['class']);
		}
		if(isset($check_list['function'])){
			$result['function'] = self::checkModule($check_list['function']);
		}
		return $result;
	}
	
	public static function checkModule($module){
		return self::check('extension_loaded',$module);
	}
	
	public static function checkClass($class){
		return self::check('class_exists',$class);
	}
	
	public static function checkFunction($func){
		return self::check('function_exists',$func);
	}
	
	protected static function check($check_func,$check_list) {
		if(empty($check_list)){
			return null;
		}
		$result = array();
		if(is_array($check_list)){
			foreach ($check_list as $f) {
				if($check_func($f)){
					$result[$f] = true;
				}
				else{
					$result[$f] = false;
				}
			}
			
		}
		else{
			if($check_func($check_list)){
				$result[$check_list] = true;
			}
			else{
				$result[$check_list] = false;
			}
		}
		return $result;
	}
	
}

?>