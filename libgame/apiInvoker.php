<?php
class apiInvoker{
	public function invoke($api,$param){
		if(empty($api) || strpos($api,'.') === false){
			return 'api not exist';
		}
		$api_arr = explode('.',$api);
		$class = $api_arr[0];
		$method = $api_arr[1];
		require_once APP_ROOT."/data/services/$class.php";
		$ins = new $class();
		require_once FRAMEWORK . '/rest/Signature.class.php';
		$param['t'] = time();
		$sig_key = get_app_config()->getGlobalConfig(AppConfig::SIGNATURE_KEY);
		$param['k'] = Signature::sign($param, $sig_key,'k');
		if(method_exists($ins,$method)){
			return $ins->$method($param);
		} else{
			return "method $method not exist in class $class.";
		}
	}
}
?>