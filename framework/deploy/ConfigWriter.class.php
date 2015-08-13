<?php
/**
 * php的配置文件的写入类。
 *
 */
class ConfigWriter {
	/**
	 * 把配置新息写入$GLOBALS数组
	 * @param array $config
	 * @param strint $file
	 * @return string
	 */
	public static function writeGlobal($config,$file,$parent_key = ''){
		$text = "<?php\n";
		$text .= self::getGlobalConfigString($config,$parent_key);
		if(file_put_contents($file,$text) === false){
			return sprintf('写入配置文件【%s】失败。请检查该文件是否可写',$file);
		}
		return true;
	}
	
	protected static function getGlobalConfigString($config,$parent_key = ''){
		$str = '';
		if(!empty($config) && is_array($config)){
			foreach ($config as $key => $c) {
				if(!empty($parent_key)){
					$str .= "\$GLOBALS['$parent_key']['$key'] = " . var_export($c,true);
				}else{
					$str .= "\$GLOBALS['$key'] = " . var_export($c,true);
				}
				$str .= ";\n";
			}
		}
		return $str;
	}
	
	/**
	 * 写配置文件，文件中使用return语句返回$config参数的内容
	 * @param $config 配置的内容，通常是一个数组
	 * @param $file 配置文件的路径
	 * @return bool
	 */
	public static function writeConfig($config,$file){
		$text = "<?php\n";
		$text .= self::getConfigString($config);
		if(file_put_contents($file,$text) === false){
			error_log(sprintf('写入配置文件【%s】失败。请检查该文件是否可写',$file));
			return false;
		}
		return true;
	}
	
	protected static function getConfigString($config){
		if(empty($config)){
			return 'return null;';
		}
		$str = 'return ' . var_export($config,true);
		$str .= ";\n";
		return $str;
	}
}

?>