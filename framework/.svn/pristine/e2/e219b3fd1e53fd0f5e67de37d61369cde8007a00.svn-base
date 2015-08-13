<?php
/*
 * 雅虎关系开放平台 openapi 客户端接口
 * @author RainX <jingxu@alibaba-inc.com> 
 * @since 2009
 * @license LGPL
 * vim: set ts=4 sw=4 expandtab
 */


/**
 * @brief 雅虎口碑客户端接口类 
 */
define("YK_OPENAPI_REST_SERVER_URL","http://openapi.my.cn.yahoo.com/openapi/openapi.php");
define("YK_OPENAPI_USE_CURL_IF_AVAILABLE",true);
define("YK_EC_PARAM_SESSION_KEY",102);

class YkOpenApi
{
    // 类变量
    public $api_client;
    public $api_key;
    public $secret;
    public $generate_session_secret;
    public $session_expires;
    public $session_key;

    public $yk_params;
    public $user;
    public $profile_user;
    public $canvas_user;

    public $use_curl_if_available;

    /**
     * @brief 创建一个基本的YahooKoubei客户端对象
     *
     * 创建一个对象的例子:
     *
     * $yk = new YkOpenApi($api_key, $secret_key);
     *
     * @param api_key       你的开发者api_key
     * @param secret        你的开发者secret key 和api_key成对出现 
     */
    public function __construct($api_key, $secret_key)
    {
        $this->api_key      = $api_key;
        $this->secret       = $secret_key;
        $this->server_addr  = YK_OPENAPI_REST_SERVER_URL;
        $this->use_curl_if_available = YK_OPENAPI_USE_CURL_IF_AVAILABLE;
        $this->session_key=$_REQUEST['yk_sig_session_key']; //获得session_key 
        #if(empty($this->session_key))
        #{
        #    throw new YkOpenApiException("无法获取session_key", 
        #                             YkOpenApi::YK_EC_PARAM_SESSION_KEY);
        #}
    }

    // -------------- Rest API Functions --------------

    /// 用户相关的操作, 包含 getInfo 和 getLoggedInUser
    public function users($method,$array=array(),$format='xml')
    {
        $params=array();
        switch($method){
        case 'getInfo':
            if(!empty($array['uids']))
                $params['uids']=$array['uids'];
            else $params['uids']=$_POST['yk_sig_user'];
            if(!empty($array['fields']))
                $params['fields']=$array['fields'];

            break;

        case 'getLoggedInUser':
            break;

         }


        $method='users.'.$method;
        $params['format']=$format;

        return $this->post_request($method,$params);
    }

    /// 好友相关的操作，包含 getFriends, get, areFriends
    public function friends($method,$array=array(),$format='XML')
    {
        $params=array();
        switch($method){
            case 'getFriends':
                if(isset($array['page']))
                $params['page']=$array['page'];
                if(isset($array['count']))
                $params['count']=$array['count'];break;
            
            case 'areFriends':
                if(isset($array['uids1']))
                $params['uids1']=$array['uids1'];
                if(isset($array['uids2']))
                $params['uids2']=$array['uids2'];
                
                break;

            case 'get':
                if(isset($array['page']))
                $params['page']=$array['page'];
                if(isset($array['count']))
                $params['count']=$array['count'];break;
                break;


        }

        $method='friends.'.$method;
        $params['format']=$format;

        return $this->post_request($method,$params);
    }
    
    //支付的操作
	public function pay($method,$array=array(),$format='XML')
	{
		$params=array();
		switch($method){
			case 'regOrder':
				if($array['order_id'])
					$params['order_id'] = $array['order_id'];
				if($array['amount'])
					$params['amount'] = $array['amount'];
				if($array['desc'])
					$params['desc'] = $array['desc'];
				break;

			case 'isCompleted':
				if($array['order_id']){
					$params['order_id']=$array['order_id'];
				}
				break;
		}
		
		$method='pay.'.$method;
		$params['format']=$format;

		return $this->post_request($method,$params);
	}
	//通知
	public function sendnotify($method,$array = array(),$format = 'XML'){
		$params = array();
		
		switch ($method){
			case 'send':
				if($array['to_ids'])
					$params['to_ids'] = $array['to_ids'];
				if($array['notification'])
					$params['notification'] = $array['notification'];
				break;
		}
		
		$method = 'notifications.'.$method;
		$params['format'] = $format;
		
		return $this->post_request($method,$params);
	}

    /// 动态相关的操作
    public function feed($method,$array=array(),$format='XML'){ 
        $params=array(); 
        switch($method){ 
            case 'publishTemplatizedAction': 
                if(isset($array['template_id'])) //current user's friends' uids 
                $params['template_id']=$array['template_id']; 
                if(isset($array['body_data'])) 
                $params['body_data']=$array['body_data']; 
                if(isset($array['resource_id'])) 
                $params['resource_id']=$array['resource_id']; 
            break; 
        } 
 
        $method='feed.'.$method; 
        $params['format']=$format; 
 
        return $this->post_request($method,$params); 
    }
 /// invitive
    public function invitations($method, $array=array(), $format='XML'){
        $params=array(); 
        switch($method){ 
	    case 'getUserOsInviteCnt':
                if(isset($array['uids'])) 
                $params['uids']=$array['uids']; 
            break; 

	    case 'getOsInfo':
                if(isset($array['invite_ids'])) 
                $params['invite_ids']=$array['invite_ids']; 
            break; 
        } 

        $method='invitations.'. $method;
        $params['format']=$format;

        return $this->post_request($method, $params);
    }

    // -------------- Private Functions --------------

    /**
     * 获取openapi http rest 接口的签名
     * @param $param_array 待签名俄参数的数组 (FROM FB);
     */
    private function generate_yk_sig($param_array)
    {
        $secret  = $this->secret;
        $str = '';
        ksort($param_array);
        foreach ($param_array as $k=>$v) {
            $str .= "$k=$v";
        }
        $str .= $secret;

        return md5($str);
    }

    /**
     * 用指定的post方法发送参数
     * @param string $method  YkOpenApi的方法
     * @param array $params   一个参数名称的map
     *  
     * @return mixed  openapi api返回的结果
     **/
    private function &call_method($method, $params = array()) 
    {
        $data = $this->post_request($method, $params);
        // 根据格式判断
        if (empty($params['format']) || strtolower($params['format']) != 'json') {
            $result = $this->convert_xml_to_result($data, $method, $params);
        }
        else {
            $result = json_decode($data, true);
        }

        if (is_array($result) && isset($result['error_code'])) {
            throw new YkOpenApiException($result['error_msg'],
                $result['error_code']);
        }
        return $result;
    }

    // 用来生成post的字符 (FROM FB)
    private function create_post_string($method, $params) 
    {
        $post_params = array();
        foreach ($params as $key => &$val) {
            $post_params[] = $key.'='.urlencode($val);
        }
        return implode('&', $post_params);
    }

    // 进行post请求 (FROM FB)
    private function post_request($method, $params) {
        $this->finalize_params($method, $params);
        $post_string = $this->create_post_string($method, $params);

        if ($this->use_curl_if_available && function_exists('curl_init')) {
            $useragent = 'Yahoo Koubei API PHP5 Client 1.1 (curl) ' . phpversion();
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->server_addr);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
            $result = curl_exec($ch);
            curl_close($ch);
        } else {
            $content_type = 'application/x-www-form-urlencoded';
            $content = $post_string;
            $result = $this->run_http_post_transaction($content_type,
                $content,
                $this->server_addr);
        }
		
        if($params['format'] == "JSON"){
			return json_decode($result);
		}else{
			return $this->xml_to_array($result);
		}
    }

    private function finalize_params($method, &$params) {
      $this->add_standard_params($method, $params);
      // we need to do this before signing the params
      $this->convert_array_values_to_csv($params);
      $params['sig'] = $this->generate_yk_sig($params);
    }

    private function convert_array_values_to_csv(&$params) {
      foreach ($params as $key => &$val) {
        if (is_array($val)) {
          $val = implode(',', $val);
        }
      }
    }

    private function add_standard_params($method, &$params) {
      $params['method'] = $method;
      $params['session_key'] = $this->session_key;
      $params['api_key'] = $this->api_key;
      $params['call_id'] = microtime(true);
      if (!isset($params['v'])) {
        $params['v'] = '1.0';
      }
    }

    // 进行post过程， 这个函数仅在curl不能使用的时候被调用 (FROM FB)
    private function run_http_post_transaction($content_type, $content, $server_addr) 
    { 

        $user_agent = 'Yahoo Koubei API PHP5 Client 1.1 (non-curl) ' . phpversion(); 
        $content_length = strlen($content); 
        $context = 
            array('http' => 
            array('method' => 'POST', 
            'user_agent' => $user_agent, 
            'header' => 'Content-Type: ' . $content_type . "\r\n" . 
            'Content-Length: ' . $content_length, 
            'content' => $content)); 
        $context_id = stream_context_create($context); 
        $sock = fopen($server_addr, 'r', false, $context_id); 

        $result = ''; 
        if ($sock) { 
            while (!feof($sock)) { 
                $result .= fgets($sock, 4096); 
            } 
            fclose($sock); 
        } 
        return $result; 
    }

    // 将xml的结果转化为php数组
	private function xml_to_array($xml)
		{
		  if(empty($xml)){
		  	return array();
		  }
		  $array = (array)(simplexml_load_string($xml,null,LIBXML_NOCDATA));
		  foreach ($array as $key=>$item){
		  	$array[$key]  = $this->struct_to_array((array)$item);
		  }
		  return $array;
		}

    private function struct_to_array($item) {
        if(!is_string($item)) {
            $item = (array)$item;
            foreach ($item as $key=>$val){
                $item[$key]  =  self::struct_to_array($val);
            }
        }
        return $item;
    }
}

/**
 * @brief  雅虎口碑rest api 操作异常
 */
class YkOpenApiException extends Exception 
{
}
