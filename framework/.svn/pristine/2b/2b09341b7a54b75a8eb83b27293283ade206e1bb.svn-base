<?php
include_once 'thinksns.lib.php';

class ThinkSns{
    public $api_client;
    private $api_key;
    private $secret;
    private $params = array();
    private $user;

    public function __construct($api_key, $secret){
        $this->api_key = $api_key;
        $this->secret = $secret;

        $this->api_client = new ThinkSnsRestClient($api_key, $secret);
        $this->validate_params();
    }

    public function get_loggedin_user() {
        return $this->user;
    }

    public function logout($next) {
        $this->clear_cookie_state();
    }
    /**
     * 登出
     *
     */
    private function clear_cookie_state() {
        if (isset($_COOKIE[$this->api_key . '_user'])) {
            $cookies = array('user', 'session_key', 'expires');
            foreach ($cookies as $name) {
                setcookie($this->api_key . '_' . $name, false, time() - 3600);
                unset($_COOKIE[$this->api_key . '_' . $name]);
            }
            setcookie($this->api_key, false, time() - 3600);
            unset($_COOKIE[$this->api_key]);
        }
        $this->user = 0;
        $this->api_client->set_session_key(0);
    }
    /**
     * 參數處理與驗證
     *
     * @return boolean
     */
    private function validate_params() {
        $this->params = $this->get_valid_params($_POST, 'mj_sig');

        if (!$this->params) {
            $params = $this->get_valid_params($_GET, 'mj_sig');
            $post_params = $this->get_valid_params($_POST, 'mj_post_sig');
            $this->params = array_merge($params, $post_params);
        }

        if ($this->params) {
            $user  = isset($this->params['user']) ? $this->params['user'] : null;
            if (isset($this->params['session_key'])) {
                $session_key =  $this->params['session_key'];
            }else {
                $session_key = null;
            }
            $this->set_user($user, $session_key);
        }else if ($cookies = $this->get_valid_params($_COOKIE, $this->api_key)) {
            $this->set_user($cookies['user'],$cookies['session_key']);
        }
        return !empty($this->params);
    }
    /**
     * 設置當前用戶
     *
     * @param string $user
     * @param string $session_key
     * @param string $expires
     */
    public function set_user($user, $session_key, $expires=null) {
        if ((!isset($_COOKIE[$this->api_key . '_user']) || $_COOKIE[$this->api_key . '_user'] != $user)) {
            $this->set_cookies($user, $session_key, $expires);
        }
        $this->user = $user;
        $this->api_client->set_session_key($session_key);
    }
    /**
     * 設置Cookie值
     *
     * @param string $user
     * @param string $session_key
     * @param string $expires
     */
    public function set_cookies($user, $session_key, $expires=null) {
        $cookies = array();
        $cookies['user'] = $user;
        $cookies['session_key'] = $session_key;
        foreach ($cookies as $name => $val) {
            setcookie($this->api_key . '_' . $name, $val, (int)$expires, '');
            $_COOKIE[$this->api_key . '_' . $name] = $val;
        }
        $sig = self::generate_sig($cookies, $this->secret);
        setcookie($this->api_key, $sig, (int)$expires, '');
        $_COOKIE[$this->api_key] = $sig;
    }
    /**
     * 獲得有效的參數
     *
     * @param array $params
     * @param string $namespace
     * @return array();
     */
    private function get_valid_params($params, $namespace='mj_sig') {
        $prefix = $namespace . '_';
        $prefix_len = strlen($prefix);
        $valid_params = array();
        if (empty($params)) {
            return array();
        }
        foreach ($params as $name => $val) {
            if (strpos($name, $prefix) === 0) {
                $valid_params[substr($name, $prefix_len)] = self::no_magic_quotes($val);
            }
        }
        // validate that the params match the signature
        $signature = isset($params[$namespace]) ? $params[$namespace] : null;
        if (!$signature || (!$this->verify_signature($valid_params, $signature))) {
            return array();
        }
        return $valid_params;
    }
    /**
     * 參數有效性驗證
     *
     * @param array $params
     * @param array $expected_sig
     * @return boolean
     */
    private function verify_signature($params, $expected_sig) {
        return self::generate_sig($params, $this->secret) == $expected_sig;
    }
    /**
     * 生成驗證串
     *
     * @param array $params_array
     * @param string $secret
     * @return string
     */
    private static function generate_sig($params_array, $secret) {
        $str = '';

        ksort($params_array);
        foreach ($params_array as $k=>$v) {
            $str .= "$k=$v";
        }
        $str .= $secret;

        return md5($str);
    }
    /**
     * 參數轉義處理
     *
     * @param string $val
     * @return string
     */
    private static function no_magic_quotes($val) {
        if (get_magic_quotes_gpc()) {
            return stripslashes($val);
        } else {
            return $val;
        }
    }
}
?>