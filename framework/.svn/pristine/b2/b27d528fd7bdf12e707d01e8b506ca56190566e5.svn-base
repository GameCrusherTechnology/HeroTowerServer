<?php

class StringUtil {
	/**
	 * 判断一个id是否在一个以特定字符分隔的列表里面
	 * @param string $id 需要确定的id
	 * @param string $list id查找的目的列表
	 * @param string $sep 列表中id的分隔符
	 * @return mixed 如果id在列表里面,返回在列表中位置，否则返回false
	 */
	public static function isInList($id,$list,$sep = ','){
		if(empty($id) || empty($list)){
			return false;
		}
		if(empty($sep)){
			$sep = ',';
		}
		if($list == $id){
			return 0;
		}
		$pos = strpos($list,$sep . $id . $sep);
		if($pos !== false){
			return $pos + 1;
		}
		$len = strlen($list);
		$pos = strrpos($list,$sep . $id);
		if($pos !== false && $pos == $len - strlen($id) - 1){
			return $pos + 1;
		}
		$pos = strpos($list,$id. $sep);
		if($pos !== false && $pos == 0){
			return $pos;
		}
		return false;
	}
	
	/**
	 * 判断一个id是否在一个以特定字符分隔的列表里面
	 * @param string $id 需要确定的id
	 * @param string $list id查找的目的列表
	 * @param string $sep 列表中id的分隔符
	 * @return mixed 如果id在列表里面,返回在列表中位置，否则返回false
	 */
	public static function isInList2($id,$list,$sep = ','){
		if(empty($id) || empty($list)){
			return false;
		}
		if(empty($sep)){
			$sep = ',';
		}
		return strpos($sep . $list . $sep,$sep . $id . $sep);
	}
	
	/**
	 * 根据指定的key，把数组中的值使用分隔符组成字符串
	 * @param array $list
	 * @param string $key
	 * @param string $seq
	 * @return string
	 */
	public static function joinByKey($list,$key,$sep = ','){
		if(empty($list)){
			return '';
		}
		$result = '';
		foreach ($list as $item) {
			if(!empty($item[$key])){
		  		$result .= $item[$key] . $sep;
			}
		}
		return rtrim($result,$sep);
	}
	/**
	 * 将一个描述数字的字符串扩展为一个数组
	 * @param string $num_range 一个字符串，格式：1,2,3,4-8,20
	 * @return array
	 * @example $s = 1,2,3,4-8,20; $range = StringUtil::expandNumList($s);
	 */
	public static function expandNumList($num_range){
		$num_range = ',' . trim($num_range,', ');
		preg_match_all('/[0-9]+-[0-9]+/',$num_range,$out);
		$range = array();
		if(!empty($out)){
			foreach($out[0] as $m){
				$num_range = str_replace(',' . $m,'',$num_range);
				$r = explode('-',$m);
				$range = array_merge($range,range($r[0],$r[1]));
			}
		}
		if(!empty($num_range)){
			$num_range = trim($num_range,',');
			$range = array_merge($range,explode(',',$num_range));
		}
		return $range;
	}
}

?>