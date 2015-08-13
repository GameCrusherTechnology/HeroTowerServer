<?php

class ArrayUtil {
	/**
	 * 将一个数字索引的数组，转换成一个使用数组中指定key的元素的值
	 * 为索引的关联数组
	 * @param array $array
	 * @param string 数组中元素的key
	 */
	public static function toAssoc($array,$key_field){
		if(empty($array)){
			return array();
		}
		$result = array();
		foreach ($array as $item){
			if(isset($item[$key_field])){
				$result[$item[$key_field]] = $item;
			}
		}
		return $result;
	}
}

?>