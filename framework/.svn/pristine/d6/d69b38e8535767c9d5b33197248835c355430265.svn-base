<?php
class Yw_API_Client {
    var $user;
    var $friends;
    var $added;
    var $api_key;
    var $secret;
    var $errno;
    var $errmsg;
    
    function friend_get() {
        if (isset($this->friends)) {
            return $this->friends;
        }
        return $this->_call_method('friend.get', array());
    }

    function friend_areFriends($uid1, $uid2) {
        //if ($uid1 == $this->user_getLoggedInUser();
        return $this->_call_method('friend.areFriends', array('uid1' => $uid1,
                                                              'uid2' => $uid2
                                                              ));
    }

    function friend_getAppUsers() {
        return $this->_call_method('friend.getAppUsers', array());
    }

    function user_getLoggedInUser() {
        if (isset($this->user)) {
            return $this->user;
        }
        return $this->_call_method('user.getLoggedInUser', array());
    }

    function user_isAppAdded() {
        if (isset($this->added)) {
            return $this->added; 
        }
        return $this->_call_method('user.isAppAdded', array());
    }

    function user_getinfo($uids, $fields = null) {
        return $this->_call_method('user.getinfo', array( 'uids'=> $uids, 
                                                          'fields'=>$fields));
    }
	
    function notification_send($uids, $msg) {
        return $this->_call_method('notification.send', array( 'uids'=> $uids, 
                                                          'msg'=>$msg));
    }

    function feed_publishTemplatizedAction($title_template,$title_data,$body_template,$body_data,$body_general,$image_1,$image_1_link,$image_2,$image_2_link,$image_3,$image_3_link,$image_4,$image_4_link,$target_ids = null) {

        return $this->_call_method('feed.publishTemplatizedAction', array('title_template' => $title_template,
                                                                           'title_data' => $title_data,
                                                                           'body_template' => $body_template,
                                                                          'body_data' => $body_data,
                                                                          'body_general' => $body_general,
                                                                          'image_1' => $image_1,
                                                                          'image_1_link' => $image_1_link,
                                                                          'image_2' => $image_2,
                                                                          'image_2_link' => $image_2_link,
                                                                          'image_3' => $image_3,
                                                                          'image_3_link' => $image_3_link,
                                                                          'image_4' => $image_4,
                                                                          'image_4_link' => $image_4_link,
                                                                          'target_ids' => $target_ids));
    }

    function _call_method($method, $args) {
        $this->errno = 0;
        $this->errmsg = '';

        $url = 'http://home.oktang.com/ywapp/openapi.php'; 

        $params = array();
        $params['method'] = $method;
        $params['session_key'] = $this->session_key;
        $params['api_key'] = $this->api_key;
        $params['format'] = 'PHP';
        $params['v'] = '0.1';
        //$params['secret'] = $this->secret;

        ksort($params); 
        $str = '';
        foreach ($params as $k=>$v) {
            $str .= $k . '=' . $v . '&';
        }

        ksort($args); 
        foreach ($args as $k=>$v) {
            if (is_array($v)) {
                $v = join(',', $v);
            }
            $params['args'][$k] = $v;
            $k = 'args[' . $k . ']';
            $str .= $k .'=' . $v . '&';
        }
        $params['sig'] = md5($str . $this->secret);
        list($errno, $result) = $this->post_request($url, $params);

        if (!$errno) {
            $result = unserialize($result);
            if ($result['errCode'] != 0) {
                $this->errno = $result['errCode'];
                $this->errmsg = $result['errMessage'];
                // TODO handle error
				echo $result['errMessage'];
                return null;
            }
            //print_r($result);
            return $result['result'];
        } else {
            return false;
        }
    }

    function post_request($url, $params) {

        $str = '';

        foreach ($params as $k=>$v) {
            if (is_array($v)) {
                foreach ($v as $kv => $vv) { 
                    $str .= '&' . $k . '[' . $kv  . ']=' . urlencode($vv);
                }
            } else {
                $str .= '&' . $k . '=' . urlencode($v); 
            }
        }

        if (function_exists('curl_init')) {
            // Use CURL if installed...
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Manyou API PHP Client 0.1 (curl) ' . phpversion());
            $result = curl_exec($ch);
            $errno = curl_errno($ch);
            curl_close($ch);

            //var_dump($result);
            return array($errno, $result);
        } else {
            // Non-CURL based version...
            $context =
            array('http' =>
                    array('method' => 'POST',
                        'header' => 'Content-type: application/x-www-form-urlencoded'."\r\n".
                                    'User-Agent: Manyou API PHP Client 0.1 (non-curl) '.phpversion()."\r\n".
                                    'Content-length: ' . strlen($str),
                        'content' => $str));
            $contextid = stream_context_create($context);
            $sock = fopen($url, 'r', false, $contextid);
            if ($sock) {
                $result = '';
                while (!feof($sock)) {
                    $result .= fgets($sock, 4096);
                }
                fclose($sock);
            }
        }
        return array(0, $result);
    }
}

class Ywapi {
    
    var $params;
    var $session_key;
    var $api_client;
    var $api_key;
    var $secret;
    
    function Ywapi($api_key, $secret) {

        $this->api_key = $api_key;
        $this->secret = $secret;

        $this->params = $this->get_valid_params();
        $this->session_key = $_GET['my_sig_sessionId'];

        $this->api_client = new Yw_API_Client();

        $this->api_client->api_key = $api_key;
        $this->api_client->secret = $secret;
        $this->api_client->session_key = $this->session_key; 

        if (isset($this->params['my_sig_friends']) && trim($this->params['my_sig_friends'])) {
            $this->api_client->friends = explode(',', $this->params['my_sig_friends']);
        }
        if (isset($this->params['my_sig_added'])) {
            $this->api_client->added = $this->params['my_sig_added'] ? true : false;
        }
        if (isset($this->params['my_sig_uId'])) {
            $this->api_client->user = $this->params['my_sig_uId'];
        }
    }

    function get_valid_params() {
        $params = array();
        foreach ($_POST as $k=>$v) {
            if (substr($k, 0, 7) == 'my_sig_') {
                $params[$k] = $this->no_magic_quotes($v); 
            }
        }
        //print_r($params);
        $sig = $params['my_sig_key'];
        unset($params['my_sig_key']);

        ksort($params); 
        $str = '';
        foreach ($params as $k=>$v) {
            if ($v) {
                $str .= $k . '=' . $v . '&';
            }
        } 
        if ($sig != md5($str. $this->secret)) {
            return array();
        }
        return $params;
    }
    function no_magic_quotes($val) {
        if (get_magic_quotes_gpc()) {
            return stripslashes($val);
        } else {
            return $val;
        }
    }

}

?>
