<?php
defined('FRAMEWORK') or define('FRAMEWORK',dirname(realpath(__FILE)));
/**
 * 用于include一个库文件。
 * 所有framework中的文件以elex开始，
 * 比如需要引入framework/net/HttpRequest.class.php文件，
 * 则使用下面的语法import('elex.net.HttpRequest');
 * @param $alias 库文件的别名
 * @param $ext 文件的扩展名，如果引入类文件，则默认是".class.php"
 * @return bool
 */
function import($alias,$ext = '.class.php'){
	static $imported = array();
	$alias = preg_replace('/[^A-Z0-9\._-]/i', '',$alias);
	if(!isset($alias[0])){
		return false;
	}
	elseif(isset($imported[$alias])){
		return true;
	}
	$class_path = str_replace('.','/',$alias);
	$re = false;
	if(strpos($class_path,'elex') === 0){
		$file_path = FRAMEWORK . substr($class_path,4) . $ext;
		if(file_exists($file_path)){
			$re = include $file_path;
			$imported[$alias] = $file_path;
		}else{
			error_log("File $file_path not exists.");
		}
	}elseif(file_exists($class_path)){
		$re = include $class_path;
		$imported[$alias] = $class_path;
	}else{
		error_log("File $class_path not exists.");
	}
	return $re;
}

class ElexException extends Exception{}