<?php
	
	/**
	 * JSON gateway
	 */
	
	include("./globals.php");
	
	include "./core/json/app/Gateway.php";
	
	$gateway = new Gateway();
	
	$gateway->setBaseClassPath('./services/');
	
	$gateway->service();
?>