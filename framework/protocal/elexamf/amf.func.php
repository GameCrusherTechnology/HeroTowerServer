<?php
function print_amf_result($result,$exit = true){
	$serializer = new AMFBaseSerializer();
	$body = new MessageBody('','/1');
	$body->responseURI = $body->responseIndex . "/onResult";
	$body->setResults($result);
	$amfObj = new AMFObject();
	$amfObj->addBody($body);
	$data = $serializer->serialize($amfObj);
	header("Content-type: application/x-amf");
	$dateStr = date("D, j M Y ") . date("H:i:s", strtotime("-2 days"));
	header("Expires: $dateStr GMT");
	header("Pragma: no-store");
	header("Cache-Control: no-store");
	header("Content-length: " . strlen($data));
 	echo $data;
 	if($exit){
 		exit();
 	}
}