<?php
class API_Yn {
	  public $secret;
	  public $session_key;
	  public $api_key;
	  public $version;
	  public $server_addr = 'http://openapi.me.zing.vn/api';
	  public $method;

	public function __construct($api_key,$secret,$v='1.0') 
	{	
		$this->secret       = $secret;
		$this->api_key  = $api_key;
		$this->v=$v;
		$this->session_key = MooGetGPC("session_id",'string',"R");
		
		$this->server_addr='http://openapi.me.zing.vn/api';

	}
	public function auth($method)
	{
		$params=array();
		switch($method){
			case 'createToken':
				break;
			
			case 'getSession':
				$authTokenArray=self::auth('createToken');
				
				if($authTokenArray[0]&&$authTokenArray[0][0])
				{
						$authToken=$authTokenArray[0][0];
						$params['authToken']=$authToken;
				}else
				{
					return false;
				}
				
				break;

		}
		return $this->post_request($method,$params);
	}
	


	public function users($method,$array=array())
	{
		$params=array();
		switch($method){
			case 'getInfo':
				if($array['uids'])
				$params['uids']=$array['uids'];
				//else $params['uids']=MooGetGPC('xn_sig_user', 'string');
				if($array['fields'])
				$params['fields']=$array['fields'];
				if($array['debug'])
				$params['debug']='nosig';

				break;
			case 'getLoggedInUser':
				
				break;

			case 'isAppAdded':
				if($array['uid'])
				$params['uid']=$array['uid'];
				break;
		}
		$method='Users.'.$method;
		return $this->post_request($method,$params);
	}
	

	public function profile($method,$array=array(),$format='XML')
	{
		$params=array();
		switch($method){
			case 'getXNML':
				if($array['uid'])
				$params['uid']=$array['uid'];
				else $params['uid']=MooGetGPC('xn_sig_user', 'string');break;
			
			case 'setXNML':
				if($array['uid'])
				$params['uid']=$array['uid'];
				if($array['profile'])
				$params['profile']=$array['profile'];
				if($array['profile_action'])
				$params['profile_action']=$array['profile_action'];
				break;

		}
		

		$method='profile.'.$method;
		$params['format']=$format;

		return $this->post_request($method,$params);
	}
	public function friends($method,$array=array())
	{
		$params=array();
		switch($method){
			case 'getFriends':
				if($array['page'])
				$params['page']=$array['page'];
				if($array['count'])
				$params['count']=$array['count'];break;
			
			case 'areFriends':
				if($array['uids1'])
				$params['uids1']=$array['uids1'];
				if($array['uids2'])
				$params['uids2']=$array['uids2'];
				break;

			case 'getAppUsers':
				if($array['debug'])
				$params['debug']='nosig';
								
				break;
			case 'getAppFriends':
				break;
		}
		$method='friends.'.$method;
		return $this->post_request($method,$params);
	}
	
	public function pay($method,$array=array(),$format='XML')
	{
		$params=array();
		switch($method){
			case 'regOrder':
				if($array['trade_id'])
					$params['order_id']=$array['trade_id'];
				if($array['amount'])
					$params['amount']=$array['amount'];
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
	
	public function paytest($method,$array = array(),$format = 'XML'){
		$params = array();
		switch ($method){
			case 'regOrder':
				if($array['trade_id'])
				$params['order_id']=$array['trade_id'];
				if($array['amount'])
				$params['amount']=$array['amount'];
				break;
		}
		$method='pay4Test.'.$method;
		$params['format']=$format;

		return $this->post_request($method,$params);
		
	}
	
	public function sendinvitation($method,$array = array(),$format = 'XML'){
		
		$params = array();
		
		switch($method){
			case 'getOsInfo':
				if($array['invite_ids'])
					$params['invite_ids'] = $array['invite_ids'];
				break;
			case 'getUserOsInviteCnt':
				if($array['uids'])
					$params['uids'] = $array['uids'];
				break;
		}
		
		$method = 'invitations.'.$method;
		$params['format'] = $format;
		
		return $this->post_request($method,$params);
	}
	
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
	
	public function feed($method,$array=array(),$format='JSON'){
		$method='feed.'.$method;
		$array['format']=$format;

		return $this->post_request($method,$array);
	}

	public function photos($method,$array=array(),$format='XML')
	{
		$params=array();
		switch($method){
			case 'getAlbums':
				if($array['uid'])
				$params['uid']=$array['uid'];
				else $params['uid']=MooGetGPC('xn_sig_user', 'string');
				break;
			
			case 'get':
				break;

		}
		

		$method='photos.'.$method;
		$params['format']=$format;

		return $this->post_request($method,$params);
	}
	

	public function messages($method,$array=array(),$format='XML')
	{
		$params=array();
		switch($method){
			case 'gets':
				if($array['isInbox'])
				$params['isInbox']=$array['isInbox'];
				else $params['isInbox']=true;
				break;
			
			case 'get':
				break;

		}
		

		$method='message.'.$method;
		$params['format']=$format;

		return $this->post_request($method,$params);
	}

//=================================================================================================
	 public static function generate_sig($params_array, $secret) {
			$str = '';
			if(!is_array($params_array)) return '';
			ksort($params_array);
			// Note: make sure that the signature parameter is not already included in
			//       $params_array.
			foreach ($params_array as $k=>$v) {
			  $str .= "$k=$v";
			}
			$str .= $secret;

			return md5($str);
		  }


	 private function create_post_string($method, $params) {
			$params['session_key'] = $this->session_key;
			$params['api_key'] = $this->api_key;
			$params['call_id'] = microtime(true);
	 		$params['v'] = '1.0';
			$params['method'] = $method;
			$post_params = array();
			$post_params[] = 'sig='.$this->generate_sig($params, $this->secret);
			

			foreach ($params as $key => &$val) {
			  if (is_array($val)) $val = implode(',', $val);
			  $post_params[] = $key.'='.urlencode($val);
			}
			
			
			return implode('&', $post_params);
		  }


	 public function post_request($method, $params) {
			$post_string = $this->create_post_string($method, $params);
			//echo '<a href="'.$this->server_addr.'?'.$post_string.'">+</a>';
			
			//file_put_contents('log/vn.log','post_string' . print_r($post_string,true),FILE_APPEND);
			
			if (function_exists('curl_init')) {
			  // Use CURL if installed...
			  $ch = curl_init();
			  curl_setopt($ch, CURLOPT_URL, $this->server_addr);
			  curl_setopt($ch, CURLOPT_POST, 1);
			  curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
			  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
			  //curl_setopt($ch, CURLOPT_USERAGENT, 'Facebook API PHP5 Client 1.1 (curl) ' . phpversion());
			  $result = curl_exec($ch);
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
		    $result = json_decode($result,TRUE);
			//$this->checkreturn($result);
			return $result;
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
			
			//if($msg!=''){echo $msg;exit;}

		}
}
