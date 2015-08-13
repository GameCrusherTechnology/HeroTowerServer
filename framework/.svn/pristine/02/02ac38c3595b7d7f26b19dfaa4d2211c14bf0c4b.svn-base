<?php
/**
 * Get the current time stamp
 * @param $f the format of the date time. if this parameter is not
 * set, the default format is yyyy-mm-dd HH:MM:SS
 *
 * @return Date time
 */
function getTimeStamp($f = null) {
	if(!isset($f)){
		return date ( "Y-m-d H:i:s" );
	}
	else {
		return date($f);
	}
}

/**
 * 从各个外部变量中取值
 *
 * @param string $key 外部变量的key
 * @param string $type
 * int,integer -- 取得的变量作为一个int值返回，默认值是0
 * string      -- 取得的变量作为string返回，默认值是NULL。这是默认的返回方式
 * array       -- 取得的变量作为array返回，默认值是一个空的数组
 * bool        -- 取得的变量作为bool值返回，默认值是false
 *
 * @param string $var 代表需要取值的变量类型
 * R  -  $_REQUEST
 * G  -  $_GET
 * P  -  $_POST
 * C  -  $_COOKIE
 * @return mixed 返回key对应的值
 */
function getGPC($key, $type = 'integer', $var = 'R') {
	switch($var) {
		case 'G': $var = &$_GET; break;
		case 'P': $var = &$_POST; break;
		case 'C': $var = &$_COOKIE; break;
		case 'R': $var = &$_REQUEST; break;
	}
	switch($type) {
		case 'int':
		case 'integer':
			$return = isset($var[$key]) ? intval($var[$key]) : 0;
			break;
		case 'string':
			$return = isset($var[$key]) ? $var[$key] : NULL;
			break;
		case 'array':
			$return = isset($var[$key]) ? $var[$key] : array();
			break;
		case 'bool':
			$return = isset($var[$key]) ? (bool)$var[$key] : false;
			break;
		default:
			$return = isset($var[$key]) ? $var[$key] : NULL;
	}
	return $return;
}
/**
 * 必要的时候给参数加上转义
 *
 * @param mixed $params
 * @return mixed
 */
function elex_addslashes($params){
	if(get_magic_quotes_gpc()){
		return $params;
	}
	if(is_array($params)){
		return array_map('elex_addslashes',$params);
	}else{
		return addslashes($params);
	}
}

function getRandomString($len)
{
    $chars = array(
        "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
        "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
        "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
        "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
        "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
        "3", "4", "5", "6", "7", "8", "9"
    );
    $charsLen = count($chars) - 1;

    shuffle($chars);    // 将数组打乱
    
    $output = "";
    for ($i=0; $i<$len; $i++)
    {
        $output .= $chars[mt_rand(0, $charsLen)];
    }

    return $output;
}
# /**
#  *  将一个字串中含有全角的数字字符、字母、空格或'%+-()'字符转换为相应半角字符
#  *
#  * @access  public
#  * @param   string       $str         待转换字串
#  *
#  * @return  string       $str         处理后字串
#  */
 function make_semiangle($str)
 {
     $arr = array('０' => '0', '１' => '1', '２' => '2', '３' => '3', '４' => '4',
                  '５' => '5', '６' => '6', '７' => '7', '８' => '8', '９' => '9',
                  'Ａ' => 'A', 'Ｂ' => 'B', 'Ｃ' => 'C', 'Ｄ' => 'D', 'Ｅ' => 'E',
                  'Ｆ' => 'F', 'Ｇ' => 'G', 'Ｈ' => 'H', 'Ｉ' => 'I', 'Ｊ' => 'J',
                  'Ｋ' => 'K', 'Ｌ' => 'L', 'Ｍ' => 'M', 'Ｎ' => 'N', 'Ｏ' => 'O',
                  'Ｐ' => 'P', 'Ｑ' => 'Q', 'Ｒ' => 'R', 'Ｓ' => 'S', 'Ｔ' => 'T',
                  'Ｕ' => 'U', 'Ｖ' => 'V', 'Ｗ' => 'W', 'Ｘ' => 'X', 'Ｙ' => 'Y',
                  'Ｚ' => 'Z', 'ａ' => 'a', 'ｂ' => 'b', 'ｃ' => 'c', 'ｄ' => 'd',
                  'ｅ' => 'e', 'ｆ' => 'f', 'ｇ' => 'g', 'ｈ' => 'h', 'ｉ' => 'i',
                  'ｊ' => 'j', 'ｋ' => 'k', 'ｌ' => 'l', 'ｍ' => 'm', 'ｎ' => 'n',
                  'ｏ' => 'o', 'ｐ' => 'p', 'ｑ' => 'q', 'ｒ' => 'r', 'ｓ' => 's',
                  'ｔ' => 't', 'ｕ' => 'u', 'ｖ' => 'v', 'ｗ' => 'w', 'ｘ' => 'x',
                  'ｙ' => 'y', 'ｚ' => 'z',
                  '（' => '(', '）' => ')', '〔' => '[', '〕' => ']', '【' => '[',
                  '】' => ']', '〖' => '[', '〗' => ']', '“' => '[', '”' => ']',
                  '‘' => '[', '’' => ']', '｛' => '{', '｝' => '}', '《' => '<',
                  '》' => '>',
                  '％' => '%', '＋' => '+', '—' => '-', '－' => '-', '～' => '-',
                  '：' => ':', '。' => '.', '、' => ',', '，' => '.', '、' => '.',
                  '；' => ',', '？' => '?', '！' => '!', '…' => '-', '‖' => '|',
                  '”' => '"', '’' => '`', '‘' => '`', '｜' => '|', '〃' => '"',
                  '　' => ' ');
  
    return strtr($str, $arr);
}
/**
 * 取得应用程序的配置
 * @return GameConfig
 */
function get_app_config($gameuid = null){
	return GameConfig::getInstance($gameuid);
}
/**
 * 获取客户端访问的ip地址，即使用户隐藏在代理的后面也能获取到相应的ip地址
 */
function get_ip() {
    if (_valid_ip($_SERVER["HTTP_CLIENT_IP"])) {
        return $_SERVER["HTTP_CLIENT_IP"];
    }
    foreach (explode(",",$_SERVER["HTTP_X_FORWARDED_FOR"]) as $ip) {
        if (_valid_ip(trim($ip))) {
            return $ip;
        }
    }
    if (_valid_ip($_SERVER["HTTP_X_FORWARDED"])) {
        return $_SERVER["HTTP_X_FORWARDED"];
    } elseif (_valid_ip($_SERVER["HTTP_FORWARDED_FOR"])) {
        return $_SERVER["HTTP_FORWARDED_FOR"];
    } elseif (_valid_ip($_SERVER["HTTP_FORWARDED"])) {
        return $_SERVER["HTTP_FORWARDED"];
    } elseif (_valid_ip($_SERVER["HTTP_X_FORWARDED"])) {
        return $_SERVER["HTTP_X_FORWARDED"];
    } else {
        return $_SERVER["REMOTE_ADDR"];
    }
}
function _valid_ip($ip) {
    if (!empty($ip) && ip2long($ip)!=-1) {
        $reserved_ips = array (
	        array('0.0.0.0','2.255.255.255'),
	        array('10.0.0.0','10.255.255.255'),
	        array('127.0.0.0','127.255.255.255'),
	        array('169.254.0.0','169.254.255.255'),
	        array('172.16.0.0','172.31.255.255'),
	        array('192.0.2.0','192.0.2.255'),
	        array('192.168.0.0','192.168.255.255'),
	        array('255.255.255.0','255.255.255.255')
        );
        foreach ($reserved_ips as $r) {
            $min = ip2long($r[0]);
            $max = ip2long($r[1]);
            if ((ip2long($ip) >= $min) && (ip2long($ip) <= $max)) return false;
        }
        return true;
    } else {
        return false;
    }
}
/**
 * 判断在概率probability下，事件是否发生
 *
 * @param 概率 $probability
 * @return 如果发生，则返回true，否则返回false
 */
function can_happen_random_event($probability) {
	list($usec, $sec) = explode(' ', microtime());
    $seed = (float) $sec + ((float) $usec * 100000);
	mt_srand($seed);
	$rand = mt_rand(1,10000);
	$rand_prob = floor(10000 * $probability);
	return $rand <= $rand_prob;
}
/**
 * 删除数组元素
 */
function array_remove(&$array, $value) {
	$array = array_diff($array, array($value));
}
/**
 * 对字符串进行分割
 */
function parse_string($string, $field_names, $pattern = '/[:,|]/', $unit_size = 2) {
	if (empty($string)) return array();
	$parts = preg_split($pattern, $string);
	if ($unit_size == 1 && empty($field_names)) return $parts;
	$ret = array();
	for ($i = 0, $length = count($parts) / $unit_size; $i < $length; $i++) {
		$unit_value = array();
		for ($j = 0; $j < $unit_size; $j++) {
			$unit_value[] = $parts[$i*$unit_size+$j];
		}
		$ret[] = array_combine($field_names, $unit_value);
	}
	return $ret;
}
/**
 * 将数组整合成一个字符串
 */
function implode_string($array, $field_glue = ':', $item_glue = ',') {
	if (empty($array)) return '';
	$ret = '';
	foreach ($array as $item) {
		if (isset($ret[0])) $ret .= $item_glue;
		if (is_array($item)) $ret .= implode($field_glue, $item);
		else $ret .= strval($item);
	}
	return $ret;
}

/**
 * 获取系统日志数据库操作对象，当log_storage=database的时候会调用这个方法
 * @return 返回到指定数据库的连接
 */
function get_system_log_db_conn() {
	$app_config = get_app_config();
	$db_config = parse_dsn(
		$app_config->getGlobalConfig('system_log_db'));
	$db_helper = @mysql_connect($db_config['host'], $db_config['username'], $db_config['password']);
	@mysql_select_db($db_config['database'], $db_helper);
	return $db_helper;
}

/**
 * 获取系统日志缓存操作对象，当log_storage=database的时候会调用这个方法
 * @return Memcache
 */
function get_system_log_cache_helper() {
	$cache_config = get_app_config()->getGlobalConfig('system_log_cache');
	$host_configs = explode(',',$cache_config);
	$cache_helper = new Memcache();
	foreach ($host_configs as $host_config) {
		if(strpos($host_config,':') === false){
			$host = $host_config;
			$port = 11211;
		}
		else{
			$parts = explode(':', $host_config);
			$host = $parts[0];
			$port = $parts[1] ? $parts[1] : 11211;
		}
		$cache_helper->addServer($host, $port);
	}
	return $cache_helper;
}
/**
 * retrieve the database configuration by parsing dsn.
 * @param string $dsn the dsn tring to be parsed. the format of dsn is : driver://username:password@host/database?option=opt_value
 * @return array the configuration of database.it includes host,username,password,charset,newlink,pconnect, etc.
 */
function parse_dsn($dsn){
	if(empty($dsn)) return false;
	$dsn = parse_url($dsn);
	if(empty($dsn)) return false;
	$db_config = array();
	$db_config['driver'] = strtolower($dsn['scheme']);
	$db_config['host'] = $dsn['host'];
	if(!empty($dsn['port'])){
		$db_config['host'] .= ':' . intval($dsn['port']);
	}
	$db_config['password'] = $dsn['pass'];
	$db_config['username'] = $dsn['user'];
	if(isset($dsn['path']{1})){
		$db_config['database'] = substr($dsn['path'],1);
	}else{
		throw new ConfigException("initialize db helper exception. NO DATABASE PROVIDED.");
	}
	$db_config['charset'] = 'utf8';
	$db_config['newlink'] = false;
	$db_config['pconnect'] = false;
	if(isset($dsn['query'])){
		$opt = array();
		parse_str($dsn['query'],$opt);
		$db_config = array_merge($db_config, $opt);
	}
	return $db_config;
}
/**
 * to decide whether user can take an action in a day again.
 * this is very useful to decide the steal action, send notification action, send feed action, etc.
 *
 * @param int $gameuid the user who issue the action
 * @param int $action_id the action id user issued
 * @param int $max_allowed the max allowed time everyday
 * @return boolean if the action can be issued again, return true, or return false
 */
function can_action_happen($gameuid, $action_id, $max_allowed) {
	$cache_helper = get_app_config($gameuid)->getDefaultCacheInstance();
	$flag_key = sprintf(CACHE_KEY_CAN_ACTION_HAPPEN_FLAG, $gameuid, $action_id);
	$action_everyday_count = $cache_helper->get($flag_key);
	$count = 0;
	$now = time();
	$last_update = $now;
	if ($action_everyday_count === false) {
		$count = 1;
	} else {
		$count = $action_everyday_count['count'] + 1;
		$last_update = $action_everyday_count['last_update'];
	}
	if ($count > $max_allowed) return false;
	if (date('z', $last_update) != date('z', $now)) {
		$cache_helper->delete($flag_key);
	} else {
		$date = getdate(strtotime('next day'));
    	$next_day_time = mktime(0,0,0,$date['mon'],$date['mday'],$date['year']);
		$cache_helper->set($flag_key, array("last_update"=>$now, "count"=>$count), $next_day_time - $now);
	}
	return true;
}
/**
 * return file names with specified extension in a directory
 *
 * @param string $dir the path to the directory
 * @param string $ext the extension of file which will returned
 * @return array the file names with specified extension
 */
function list_files($dir, $ext = null) {
	$handle = opendir($dir);
	$ret = array();
	if (!$handle) return $ret;
	if ($handle) {
	   	while (false !== ($file_name = readdir($handle))) {
	    	if (substr($file_name, -4) != '.'.$ext) continue;
	        $ret[] = $file_name;
	    }
	    closedir($handle);
	}
	return $ret;
}
?>