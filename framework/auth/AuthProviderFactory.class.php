<?php
require_once (FRAMEWORK . '/auth/AuthenticationProvider.class.php');

class AuthProviderFactory {
	/**
	 * 创建一个authenticationProvider的实例
	 * @param string $driver
	 * @param array $option
	 * @return AuthenticationProvider
	 */
	public static function createProvider($driver = 'mysql',$option = null){
		switch ($driver) {
			case 'mysql':
			default:
				include_once FRAMEWORK . '/auth/providers/MySQLAuthenticationProvider.class.php';
				$instance = new MySQLAuthenticationProvider($option);
			break;
		}
		return $instance;
	}
}

?>