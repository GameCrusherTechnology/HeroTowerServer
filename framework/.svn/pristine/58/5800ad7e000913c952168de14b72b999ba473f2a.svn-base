<?php
class ThinkSnsRestClient{
    private $api_key;
    private $secret;
    private $server_addr;
    private $last_call_id;
    private $session_key;
    private $format = "json";

    public function __construct($api_key, $secret){
        $this->api_key = $api_key;
        $this->secret = $secret;
        $this->server_addr = "http://home.elex-tech.com/api/restserver.php";
    }
    /**
     * 獲取用戶信息
     */
    public function user_getInfo($uids, $fields = null){
        $params['uids'] = $uids;
        if($fields == null){
            $params['fields'] =  "name, email";
        }else{
            $params['fields'] = $fields;
        }
        return $this->call_method("elex.sns.user.getInfo", $params);
    }
    /**
     * 獲取當前登入用戶ID
     *
     */
    public function user_getLoggedInUser(){
        return $this->call_method("elex.sns.user.getLoggedInUser");
    }

    /**
     * 返回好友列表
     *
     */
    public function friends_get(){
        return $this->call_method("elex.sns.friends.get");
    }

    public function notifications_send($type, $title_data, $body_data, $to_ids = null){
        $params = array();
        $params['type'] = $type;
        $params['title_data'] = $title_data;
        $params['body_data'] = $body_data;
        if($to_ids != null && is_string($to_ids)){
            $params['to_ids'] = $to_ids;
        }
        return $this->call_method("elex.sns.notifications.send", $params);
    }
    public function set_format($format){
        if(in_array($format, array("php", "json"))){
            $this->format = $format;
        }
    }
    /**
     * 設置Session_Key;
     */
    public function set_session_key($session_key){
        $this->session_key = $session_key;
    }
    private function &call_method($method, $params = array()) {
        if ($this->format) {
            $params['format'] = $this->format;
        }
        $data = $this->post_request($method, $params);

        return $data;
    }
    /**
     * 添加標準參數
     */
    private function add_standard_params($method, $params){
        $post = $params;
        $get = array();

        $get['method'] = $method;
        $get['session_key'] = $this->session_key;
        $get['api_key'] = $this->api_key;
        $post['call_id'] = microtime(true);
        if ($post['call_id'] <= $this->last_call_id) {
            $post['call_id'] = $this->last_call_id + 0.001;
        }
        $this->last_call_id = $post['call_id'];

        return array($get, $post);
    }
    private function finalize_params($method, $params) {
        list($get, $post) = $this->add_standard_params($method, $params);
        $this->convert_array_values_to_json($post);
        $post['sig'] = $this->generate_sig(array_merge($get, $post),   $this->secret);
        return array($get, $post);
    }
    private function convert_array_values_to_json(&$params) {
        foreach ($params as $key => &$val) {
            if (is_array($val)) {
                $val = json_encode($val);
            }
        }
    }
    private function generate_sig($params_array, $secret){
        $str = '';

        ksort($params_array);
        foreach ($params_array as $k=>$v) {
            $str .= "$k=$v";
        }
        $str .= $secret;

        return md5($str);
    }
    /**
     * 發送URL請求
     *
     * @param unknown_type $method
     * @param unknown_type $params
     * @return unknown
     */
    private function post_request($method, $params) {
        list($get, $post) = $this->finalize_params($method, $params);
        $post_string = http_build_query($post);
        $get_string = http_build_query($get);
        $url_with_get = $this->server_addr . '?' . $get_string;
/*        if($method =='elex.sns.user.getInfo'){
        print_r('post_string:'.$post_string.'url_with_get:'.$url_with_get);
        exit();
        }*/
        $useragent = 'Mymaji API PHP5 Client 1.1 (curl) ' . phpversion();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_with_get);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result,TRUE);
    }

}
?>