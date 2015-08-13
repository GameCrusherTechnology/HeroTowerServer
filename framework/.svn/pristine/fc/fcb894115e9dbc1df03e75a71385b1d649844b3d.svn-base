<?php
/**
 * 校内格式的api调用客户端
 */
require_once FRAMEWORK . '/platform/plugins/APIPluginBase.class.php';

class XiaoneifulAPI extends APIPlugInBase {

	public function auth($method) {
		$params = array();
		switch($method){
			case 'createToken':
				break;
			case 'getSession':
				$authTokenArray = self::auth('createToken');
				if($authTokenArray[0] && $authTokenArray[0][0]){
					$authToken = $authTokenArray[0][0];
					$params['authToken'] = $authToken;
				}
				else{
					return false;
				}
				break;
		}
		$method = $this->method_prefix . '.auth.' . $method;
		return $this->post_request($method, $params);
	}

	public function users($method, $array = array(), $format = 'XML') {
		$params = array();
		switch($method){
			case 'getInfo':
				$params['uids'] = $array['uids'];
				if($array['fields']){
					$params['fields'] = $array['fields'];
				}
				break;
			case 'getLoggedInUser':
				break;
			case 'isAppAdded':
				if($array['uid'])
					$params['uid'] = $array['uid'];
				break;
			case 'footprint':
				if($array['uid'])
					$params['uid'] = $array['uid'];
				break;
		}
		
		$method = $this->method_prefix . '.users.' . $method;
		$params['format'] = $format;
		
		return $this->post_request($method, $params);
	}

	public function profile($method, $array = array(), $format = 'XML') {
		$params = array();
		switch($method){
			case 'getXNML':
				$params['uid'] = $array['uid'];
				break;
			case 'setXNML':
				if($array['uid'])
					$params['uid'] = $array['uid'];
				if($array['profile'])
					$params['profile'] = $array['profile'];
				if($array['profile_action'])
					$params['profile_action'] = $array['profile_action'];
				break;
		}
		$method = $this->method_prefix . '.profile.' . $method;
		$params['format'] = $format;
		return $this->post_request($method, $params);
	}

	public function friends($method, $array = array(), $format = 'XML') {
		$params = array();
		switch($method){
			case 'getFriends':
				if($array['page'])
					$params['page'] = $array['page'];
				if($array['count'])
					$params['count'] = $array['count'];
				break;
			
			case 'areFriends':
				if($array['uids1'])
					$params['uids1'] = $array['uids1'];
				if($array['uids2'])
					$params['uids2'] = $array['uids2'];
				break;
			case 'getAppUsers':
				break;
			case 'getAppFriends':
				break;
		}
		
		$method = $this->method_prefix . '.friends.' . $method;
		$params['format'] = $format;
		
		return $this->post_request($method, $params);
	}

	public function pay($method, $array = array(), $format = 'XML') {
		$params = array();
		switch($method){
			case 'regOrder':
				if($array['trade_id'])
					$params['order_id'] = $array['trade_id'];
				if($array['amount'])
					$params['amount'] = $array['amount'];
				if($array['desc'])
					$params['desc'] = $array['desc'];
				break;
			case 'isCompleted':
				if($array['order_id']){
					$params['order_id'] = $array['order_id'];
				}
				break;
		}
		$method = $this->method_prefix . '.pay.' . $method;
		$params['format'] = $format;
		return $this->post_request($method, $params);
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
		$method=$this->method_prefix . '.pay4Test.'.$method;
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
		$method = $this->method_prefix . '.invitations.' . $method;
		$params['format'] = $format;
		return $this->post_request($method, $params);
	}

	public function sendnotify($method, $array = array(), $format = 'XML') {
		$params = array();
		
		switch($method){
			case 'send':
				if($array['to_ids'])
					$params['to_ids'] = $array['to_ids'];
				if($array['notification'])
					$params['notification'] = $array['notification'];
				break;
		}
		
		$method = $this->method_prefix . '.notifications.' . $method;
		$params['format'] = $format;
		
		return $this->post_request($method, $params);
	}

	public function feed($method, $array = array(), $format = 'XML') {
		$params = array();
		switch($method){
			case 'publishTemplatizedAction':
				if($array['template_id'])
					$params['template_id'] = $array['template_id'];
				if($array['title_data'])
					$params['title_data'] = $array['title_data'];
				if($array['body_data'])
					$params['body_data'] = $array['body_data'];
				if($array['resource_id'])
					$params['resource_id'] = $array['resource_id'];
				break;
		}
		$method = $this->method_prefix . '.feed.' . $method;
		$params['format'] = $format;
		
		return $this->post_request($method, $params);
	}

	public function photos($method, $array = array(), $format = 'XML') {
		$params = array();
		switch($method){
			case 'getAlbums':
				$params['uid'] = $array['uid'];
				break;
			case 'get':
				break;
		}
		
		$method = $this->method_prefix . '.photos.' . $method;
		$params['format'] = $format;
		
		return $this->post_request($method, $params);
	}

	public function messages($method, $array = array(), $format = 'XML') {
		$params = array();
		switch($method){
			case 'gets':
				if($array['isInbox'])
					$params['isInbox'] = $array['isInbox'];
				else
					$params['isInbox'] = true;
				break;
			case 'get':
				break;
		}
		$method = $this->method_prefix . '.message.' . $method;
		$params['format'] = $format;
		return $this->post_request($method, $params);
	}

}

?>