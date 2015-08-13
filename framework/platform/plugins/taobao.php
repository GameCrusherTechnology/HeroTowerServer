<?php
/**
	More & Original PHP Framwork
	Copyright (c) 2007 - 2008 IsMole Inc.

	描述:开发基于校内开放平台开发第三方应用
	帮助文档: http://dev.xiaonei.com/wiki/%E9%A6%96%E9%A1%B5

	Author:Apptz团队 roger@apptz.com
	$Id: xiaonei.php 374 2008-07-18 06:39:26Z kimi $
*/

class API_TaoBao {
	  public $secret;
	  public $session_key;
	  public $api_key;
	  public $sign;
	  public $version;
	  public $server_addr;
	  public $method;

	public function __construct($api_key,$sig_secret,$v='1.0') 
	{	
		$this->secret       = $sig_secret;
		$this->api_key  = $api_key;
		$this->v=$v;
//		$this->session_key = MooGetGPC('top_session', 'string');
		
		$this->server_addr='http://gw.api.taobao.com/router/rest';
//		$this->server_addr='http://gw.sandbox.taobao.com/router/rest';
//		$this->server_addr='http://192.168.206.169/top/router/rest';
	}


	public function users($method,$array=array(),$format='json')
	{
		$params=array();
		switch($method){
			case 'get':
				if($array['uids'])
				$params['uid']=$array['uids'];
//				else $params['uids']=MooGetGPC('xn_sig_user', 'string');
//				if($array['fields'])
//				$params['fields']=$array['fields'];

				break;
			
			case 'getLoggedInUser':
				break;

			case 'isAppAdded':
				if($array['uid'])
				$params['uid']=$array['uid'];
				break;
		}
		

		$method='taobao.sns.user.'.$method;
		$params['format']=$format;
		
//		$params['sign'] = md5($this->api_key.$this->sig_secret."format".$params['format']."method"."uid".$params['uid']."v1.0");
		return $this->post_request($method,$params);
	}
	
	public function friends($method,$array=array(),$format='json')
	{
		$params=array();
		switch($method){
			case 'get':
				if($array['uids'])
				$params['uid']=$array['uids'];
		}
		
		$method='taobao.sns.friends.'.$method;
		$params['format']=$format;
		$params['start_row']=0;
		$params['count']=300;
		return $this->post_request($method,$params);
	}
	public function sendnotify($method,$array = array(),$format = 'json'){
		
		$params = array();
		switch ($method){
			case 'send':
				if($array['to_uids'])
					$params['to_uid'] = $array['to_uids'];
				if($array['notification'])
					$params['content'] = $array['notification'];
				if($array['session'])
					$params['session'] = $array['session'];
				break;
			case 'sysSend':
				if($array['to_uids'])
					$params['to_uid'] = $array['to_uids'];
				if($array['notification'])
					$params['content'] = $array['notification'];
				if($array['session'])
					$params['session'] = $array['session'];
				break;
				
		}
		$method = 'taobao.sns.message.'.$method;
		$params['format'] = $format;
		return $this->post_request($method,$params);
	}

//=================================================================================================
	 public static function generate_sig($params_array, $secret) {
			$str = $secret;

			ksort($params_array);
			// Note: make sure that the signature parameter is not already included in
			//       $params_array.
			foreach ($params_array as $k=>$v) {
			  $str .= "$k$v";
			}
//			$str .= $secret;
//return $str;
			return strtoupper(md5($str));
		  }

	 private function create_post_string($method, $params) {
			$params['method'] = $method;
//			$params['session_key'] = $this->session_key;
			
			$params['api_key'] = $this->api_key;
			$params['timestamp'] = date("Y-m-d H:i:s",time());
//			if ($params['timestamp'] <= $this->last_call_id) {
//			  $params['timestamp'] = $this->last_call_id + 0.001;
//			}
//			$this->last_call_id = $params['timestamp'];
			if (!isset($params['v'])) {
			  $params['v'] = '1.0';
			}
			$post_params = array();
			foreach ($params as $key => &$val) {
			  if (is_array($val)) $val = implode(',', $val);
			  $post_params[] = $key.'='.urlencode($val);
			}
			$secret = $this->secret;
			$post_params[] = 'sign='.$this->generate_sig($params, $secret);
			return implode('&', $post_params);
		  }


	 public function post_request($method, $params) {
			$post_string = $this->create_post_string($method, $params);
//			return $post_string;
			//echo '<a href="'.$this->server_addr.'?'.$post_string.'">+</a>';
			if (function_exists('curl_init')) {
//				return "aa";
			  // Use CURL if installed...
			  $ch = curl_init();
			  curl_setopt($ch, CURLOPT_URL, $this->server_addr);
			  curl_setopt($ch, CURLOPT_POST, 1);
			  curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
			  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			  //curl_setopt($ch, CURLOPT_USERAGENT, 'Facebook API PHP5 Client 1.1 (curl) ' . phpversion());
			  $result = curl_exec($ch);
//			  return $result;
			  curl_close($ch);
			} else {
			  // Non-CURL based version...
			  $context =
				array('http' =>
					  array('method' => 'POST',
							'header' => 'Content-type: application/x-www-form-urlencoded'."\r\n".
										'User-Agent: Facebook API PHP5 Client 1.1 (non-curl) '.phpversion()."\r\n".
										'Content-length: ' . strlen($post_string),
							'content' => $post_string));
			  $contextid=stream_context_create($context);
			  
			  $sock=fopen($this->server_addr, 'r', false, $contextid);
			  if ($sock) {
				$result='';
				while (!feof($sock))
				  $result.=fgets($sock, 4096);

				fclose($sock);
			  }
			}
			
			if($result){
				if($params["format"]=="json"){
					$result = json_decode($result,true);				
				}else{
					$result = $this->xml_to_array($result);
				}
				$this->checkreturn($result);
			}else{
				$result = array();
			}
			return $result;
		    
	 }

		private function xml_to_array($xml)
			{
			  $array = (array)(simplexml_load_string($xml));
			  foreach ($array as $key=>$item){
				$array[$key]  = $this->struct_to_array((array)$item);
			  }
			  return $array;
			}

		private	function struct_to_array($item) {
			  if(!is_string($item)) {
				$item = (array)$item;
				foreach ($item as $key=>$val){
				  $item[$key]  =  self::struct_to_array($val);
				}
			  }
			  return $item;
			}

		
		private function checkreturn($result)
		{	
			$msg='';
			if($result['error_code'])
			{
				$msg.='<br />访问出错!<br />';
				if($result['error_code'][0])
				{
					$msg.='错误编号:'.$result['error_code'][0].'<br>';
				}
				if($result['error_msg'][0])
				{
					$msg.='错误信息:'.$result['error_msg'][0].'<br>';
				}

			}
			
			//if($msg!=''){echo $msg;}

		}
}
