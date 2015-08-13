<?php
/**
 * 输出amf格式的内容
 * @param $result 需要输出到浏览器的内容
 * @param $exit 是否在输出后结束脚本
 * @return void
 */
function print_amf_result($result, $exit = true) {
	if($GLOBALS['amfphp']['native'] === true && function_exists('amf_decode')){
		$serializer = new AMFBaseSerializer(); // Create a serailizer around the output stream
	}
	else{
		$serializer = new AMFSerializer(); // Create a serailizer around the output stream
	}
	$body = new MessageBody('', '/1');
	$body->responseURI = $body->responseIndex . "/onResult";
	$body->setResults($result);
	$amfObj = new AMFObject();
	$amfObj->addBody($body);
	$data = $serializer->serialize($amfObj);
	header('Content-type: application/x-amf');
	$dateStr = date('D, j M Y H:i:s', time() - 86400);
	header("Expires: $dateStr GMT");
	header('Pragma: no-store');
	header('Cache-Control: no-store');
	header('Content-length: ' . strlen($data));
	echo $data;
	if($exit){
		exit();
	}
}