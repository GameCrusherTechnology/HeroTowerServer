<?php

	/**
	 * XML-RPC server
	 */
	include "core/xmlrpc/app/Gateway.php";
	
	$gateway = new Gateway();
	
	$gateway->setBaseClassPath('./services/');
	
	$gateway->service();
?>