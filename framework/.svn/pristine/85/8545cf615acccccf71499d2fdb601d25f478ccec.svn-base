<?php
abstract class PlatformManager {
	protected $sig_api_key = null;
	protected $sig_secret = null;
	protected $sig_user = 0;
	protected $platform_handler = null;
	public function __construct() {
		$platform_config = get_app_config()->getSection("platform");
		$this->sig_api_key = $platform_config['sig_api_key'];
		$this->sig_secret = $platform_config['sig_secret'];
	}
	public static function getPlatformHandler($mod, $platform_params = null) {
		if (empty($mod)) return null;
		require_once FRAMEWORK."/platform/plugins/$mod.php";
		switch ($mod) {
			case "facebook":
				return new FacebookHandler($platform_params);
			case "thinksns":
				return new ThinkSnsHandler($platform_params);
			default:
				return null;
		}
	}
	public function validateUser() {
		$logged_in_user = $this->getLoggedInUser();
		if ($this->sig_user == $logged_in_user) return true;
		return false;
	}
	public function getUserInfo($uids) {
		if (is_string($uids)) $uids = explode(",",$uids);
		if (empty($uids)) return array();
		
		$user_info_keys = array();
		foreach ($uids as $uid) {
			$user_info_keys[] = sprintf(CACHE_KEY_PLATFORM_USER_INFO, $uid);
		}
		$user_infos = $this->getFromCache($user_info_keys);
		$valid_uids = array();
		$ret = array();
		foreach ($user_infos as $key=>$user_info) {
			$uid = intval(substr($key, strrpos($key, "_")+1));
			$valid_uids[] = $uid;
			$ret[$uid] = $user_info;
		}
		$expires = array_diff($uids, $valid_uids);
		if (count($expires) > 0) {
			$remote_infos = $this->getUserInfoFromPlatform(implode(",",$expires));
			foreach ($remote_infos as $remote_info) {
				$this->setToCache(sprintf(CACHE_KEY_PLATFORM_USER_INFO, $remote_info['uid']), $remote_info);
				$ret[$remote_info['uid']] = $remote_info;
			}
		}
		return $ret;
	}
	public function getUserFriends($uid) {
		if (empty($uid)) return "";
		$user_friends = $this->getFromCache(sprintf(CACHE_KEY_PLATFORM_USER_FRIENDS,$uid));
		if ($user_friends === false) {
			$user_friends = $this->getUserFriendsFromPlatform($uid);
			$this->setToCache(sprintf(CACHE_KEY_PLATFORM_USER_FRIENDS, $uid), $user_friends);
		}
		return $user_friends;
	}
	public function refreshUserFriends($uid) {
		if (empty($uid)) return "";
		$user_friends = $this->getUserFriendsFromPlatform($uid);
		$this->setToCache(sprintf(CACHE_KEY_PLATFORM_USER_FRIENDS, $uid), $user_friends);
		return $user_friends;
	}
	/**
	 * 发送feed，feed和notification发送需要控制，一天只能发送一次feed或者是notification
	 *
	 * @param 触发操作的用户的gameuid $gameuid
	 * @param 模版的id $template_id
	 * @param 模版标题中的变量数据，为json格式 $title_data
	 * @param 模版内容中的变量数据，为json格式 $body_data
	 * @return 发送成功返回true，否则返回false
	 */
	public function sendFeed($gameuid, $template_id, $title_data, $body_data) {
		if (!can_action_happen($gameuid, ACTION_SEND_FEED, 1)) return false;
		$this->sendFeedThroughPlatform($template_id, $title_data, $body_data);
		return true;
	}
	/**
	 * 发送notification，feed和notification发送需要控制，一天只能发送一次feed或者是notification
	 *
	 * @param 触发操作的用户的gameuid $gameuid
	 * @param 发送通知的目的uids，为逗号分隔的字符串 $to_uids
	 * @param 通知的内容 $content
	 * @return 发送成功返回true，否则返回false
	 */
	public function sendNotification($gameuid, $to_uids, $content) {
		if (!can_action_happen($gameuid, ACTION_SEND_NOTIFICATION, 1)) return false;
		$this->sendNotificationThroughPlatform($to_uids, $content);
		return true;
	}
	protected function getTableName() {
		return "platform_cache";
	}
	protected function getFromCache($key, $gameuid = null) {
    	$appConfigInstance = get_app_config($gameuid);
        $cache_handler = $appConfigInstance->getTableServer($this->getTableName(), $gameuid)->getCacheInstance();
        return $cache_handler->get($key);
    }
	protected function setToCache($key, $value, $gameuid = null, $expire_time=null) {
    	$appConfigInstance = get_app_config($gameuid);
    	$table_server = $appConfigInstance->getTableServer($this->getTableName(), $gameuid);
        $cache_handler = $table_server->getCacheInstance();
        if ($expire_time === null){
        	$expire_time = $table_server->getCacheExpireTime();
        }
        return $cache_handler->set($key, $value, $expire_time);
    }
	abstract public function getLoggedInUser();
	abstract protected function getUserInfoFromPlatform($uids);
	abstract protected function getUserFriendsFromPlatform($uid);
	abstract protected function sendFeedThroughPlatform($template_id, $title_data, $body_data);
	abstract protected function sendNotificationThroughPlatform($to_uids, $content);
}
/**
 * 这个类是private的，不要直接使用，
 * 要通过PlatformManger::getPlatformHandler('facebook', $platform_params)来获取操作句柄
 */
class FacebookHandler extends PlatformManager {
	private static $prefix = 'fb_';
	public function __construct($platform_params = null) {
		parent::__construct();
		if (!isset($_POST['fb_sig_session_key'])) {
			$facebook_params = array();
			foreach ($platform_params as $name=>$value) {
				$facebook_params[self::$prefix.$name]=$value;
			}
			$facebook_params[self::$prefix.'sig']=Facebook::generate_sig($facebook_params, $this->sig_secret);
			foreach ($facebook_params as $name=>$value) {
				$_POST[$name]=$value;
			}
		}
		$this->platform_handler = new Facebook($this->sig_api_key, $this->sig_secret);
		$this->sig_user = $this->platform_handler->user;
	}
	public function getLoggedInUser() {
		return $this->plateform_handler->api_client->users_getLoggedInUser();
	}
	protected function getUserInfoFromPlatform($uids) {
		$ret = array();
		$remote_infos = $this->plateform_handler->api_client->users_getInfo($uids, "uid,name,profile_url,pic_square");
		foreach ($remote_infos as $remote_info) {
			$ret[] = array("uid"=>$remote_info['uid'],"name"=>$remote_info['name'],"profile_url"=>$remote_info['profile_url'],"head_pic"=>$remote_info['pic_square']);
		}
		return $ret;
	}
	protected function getUserFriendsFromPlatform($uid) {
		$app_friends = $this->plateform_handler->api_client->fql_query(
				"SELECT uid,name,profile_url,pic_square FROM user WHERE uid IN (SELECT uid2 FROM friend WHERE uid1=$uid) AND is_app_user");
		$user_friends = "";
		if (is_array($app_friends) && count($app_friends) > 0) {
			foreach ($app_friends as $app_friend) {
				$user_friends .= $app_friend['uid'].',';
				$this->setToCache(sprintf(CACHE_KEY_PLATFORM_USER_INFO,$app_friend['uid']), array("name"=>$app_friend['name'],"profile_url"=>$app_friend['profile_url'],"head_pic"=>$app_friend['pic_square']));
			}
			$user_friends = trim($user_friends, ',');
		}
		return $user_friends;
	}
	protected function sendFeedThroughPlatform($template_id, $title_data, $body_data){}
	protected function sendNotificationThroughPlatform($to_uids, $content){}
}
class ThinkSnsHandler extends PlatformManager {
	public function __construct($platform_params = null) {
		parent::__construct();
		$this->platform_handler = new ThinkSns($this->sig_api_key, $this->sig_secret);
		$this->platform_handler->api_client->set_session_key($platform_params['sig_session_key']);
		$this->sig_user = $platform_params['sig_user'];
	}
	public function getLoggedInUser() {
		$self = $this->platform_handler->api_client->user_getLoggedInUser();
		if (!empty($self['error_code'])) {
			$this->logger->writeError("retrieving logged user failed from think sns:" . $self['error_msg']);
			return false;
		}
		if (empty($self['data'])) {
			$this->logger->writeError("can not retrieve user uid from think sns. it is empty");
			return false;
		}
		return $self['data'];
	}
	protected function getUserInfoFromPlatform($uids) {
		$ret = array();
		$remote_infos = $this->platform_handler->api_client->user_getInfo($uids, "id,name,pic_small");
		$remote_infos = $remote_infos['data'];
		foreach ($remote_infos as $remote_info) {
			$ret[] = array("uid"=>$remote_info['id'],"name"=>$remote_info['name'],"head_pic"=>$remote_info['pic_small']);
		}
		return $ret;
	}
	protected function getUserFriendsFromPlatform($uid) {
		$app_friends = $this->platform_handler->api_client->friends_get();
		$app_friends = $app_friends['data'];
		return implode(',', $app_friends);
	}
	protected function sendFeedThroughPlatform($template_id, $title_data, $body_data){
		$app_name = get_app_config()->getGlobalConfig('app_name');
		$this->platform_handler->api_client->notifications_send($app_name."_feed_$template_id", $title_data, $body_data);
	}
	protected function sendNotificationThroughPlatform($to_uids, $content){
		include_once FRAMEWORK .'/json/JSON.php';
    	$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
    	$data = $json->decode($content);
    	$template_id = $data['tpl_id'];
    	if (empty($template_id)) return;
    	$title_data = $data['title_data'];
    	$body_data = $data['body_data'];
    	$app_name = get_app_config()->getGlobalConfig('app_name');
    	$this->platform_handler->api_client->notifications_send($app_name."_notification_$template_id", $title_data, $body_data, $to_uids);
	}
}
?>