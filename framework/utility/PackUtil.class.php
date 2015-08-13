<?php
/**
 * 这个类用于把数据pack为二进制字符串
 * @author shusl
 * @since v1.2.1
 */

class PackUtil  {
	/**
	 * 验证一个int值的id是否在一个二进制的字符串中
	 * @param int $id 需要查找的int值id
	 * @param string $bin_list 用于查找的二进制字符窜
	 * @param string $format int值格式时的格式
	 * @return bool
	 */
	public static function isInBinList($id,$bin_list,$format = 'I'){
		if(empty($id) || empty($bin_list)){
			return false;
		}
		$id_bin = pack($format,$id);
		if (strpos($bin_list,$id_bin) === false){
			return false;
		}
		return true;
	}
	/**
	 * 将一个数组中的值，根据指定的格式，把数组的元素格式化成二进制字符串
	 * @param array $list 需要格式化的数组列表，通过引用传递
	 * @param array $format 格式
	 * @return void
	 */
	public static function packArrayList(array &$list,array $format){
		if(empty($list)){
			return;
		}
		array_walk($list,array('PackUtil','packArray'),$format);
	}
	/**
	 * 将一个包含二进制字符的数组，根据给定的格式，反序列化为一个关联数组
	 * @param array $bin_str_array 包含二进制字符的数组，通过引用传递
	 * @param $format
	 * @return void
	 */
	public static function unpackArrayList(array &$bin_str_array,$format){
		if(empty($bin_str_array)){
			return;
		}
		// 如果是二维数组，则返回
		if(is_array(current($bin_str_array))){
			return;
		}
		if(is_array($format)){
			$format = self::getAssocFormat($format);
		}
		array_walk($bin_str_array,array('PackUtil','unpackArray'),$format);
	}
	
	protected static function unpackArray(&$bin_str,$key,$format){
		if(isset($bin_str[0]))
		$bin_str = unpack($format,$bin_str);
	}
	
	protected static function packArray(array &$list,$index,array $format){
		if(empty($list)){
			$list = '';
			return;
		}
		$re = '';
		foreach($format as $key => $f){
			if(isset($list[$key])){
				$re .= pack($f,$list[$key]);
			}else{
				$re .= pack($f,0);
			}
		}
		$list = $re;
	}
	/**
	 * 根据给定的格式，格式一个数组为一个二进制的字符串。
	 * 数组的元素个数和格式个数最好相同，否则将以格式数组的个数为准，不存在的将以0代替
	 * @param array $list
	 * @param array $format
	 * @return string
	 */
	public static function packList(array $list, array $format){
		if(empty($list)){
			return null;
		}
		$re = '';
		foreach($format as $key => $f){
			if(isset($list[$key])){
				$re .= pack($f,$list[$key]);
			}else{
				$re .= pack($f,0);
			}
		}
		return $re;
	}
	/**
	 * 根据给定的格式，将一个二进制字符串解析为一个给定名称的关联数组
	 * @param $bin_str
	 * @param $format 命名的unpack格式，可以使用一个数组来指定每一个结果元素的key
	 * @return array
	 */
	public static function unpackAssoc($bin_str,$format){
		if(empty($bin_str)){
			return array();
		}
		if(is_array($format)){
			$format = self::getAssocFormat($format);
			if($format === false){
				return false;
			}
		}
		
		return unpack($format,$bin_str);
	}
	/**
	 * 把一个列表pack为一个uint的二进制字符串
	 * @param array $list 一个数组，每个元素包含一个uint值
	 * @param string $format pack的格式
	 * @return string
	 */
	public static function packUIntList($list,$format = 'I'){
		if(empty($list) || !is_array($list)){
			return '';
		}
		$re = '';
		foreach ($list as $l){
			$re .= pack($format,$l);
		}
		return $re;
	}
	
	/**
	 * 从一个二进制的字符串中解析出uint的数组。注意：数组下标是从1开始的。
	 * @param string $str
	 * @param string $format
	 * @return array
	 */
	public static function unpackUIntList($str,$format = 'I*'){
		if(empty($str)){
			return array();
		}
		return unpack($format,$str);
	}
	/**
	 * 根据给定的格式，将一个二进制字符串解析为一个给定名称的关联数组
	 * @param $bin_str
	 * @param $format 命名的unpack格式，可以使用一个数组来指定每一个结果元素的key
	 * @return array
	 */
	public static function unpackUIntAssoc($bin_str,$format){
		if(empty($bin_str)){
			return array();
		}
		if(is_array($format)){
			$format = self::getAssocFormat($format);
			if($format === false){
				return false;
			}
		}
		
		return unpack($format,$bin_str);
	}
	/**
	 * 根据一个数组，组成需要的命名unpack格式的字符串
	 * @param array $format
	 * @param string $default_format
	 * @return string
	 */
	public static function getAssocFormat(array $format,$default_format = 'I'){
		if(empty($format)){
			return false;
		}
		$re = '';
		foreach($format as $key => $f){
			if(is_int($key)){
				$re .= $default_format . $f . '/';
			}else{
				$re .= $f. $key . '/';
			}
		}
		return $re;
	}
}
?>