<?php
/**
 * Filters modify the AMF message has a whole, actions modify the AMF message PER BODY
 * This allows batching of calls
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage filters
 * @version $Id: Filters.php,v 1.6 2005/04/02   18:37:51 pmineault Exp $
 */

/**
 * required files
 */
require_once(AMFPHP_BASE . 'amf/util/TraceHeader.php');

/**
 * DeserializationFilter has the job of taking the raw input stream and converting in into valid php objects.
 *
 * The DeserializationFilter is just part of a set of Filter chains used to manipulate the raw data.  Here we
 * get the input stream and convert it to php objects using the helper class AMFInputStream.
 */
function deserializationFilter(AMFObject &$amf) {
	if($GLOBALS['amfphp']['native'] === true && function_exists('amf_decode'))
	{
		include_once(AMFPHP_BASE . "amf/io/AMFBaseDeserializer.php");
		include_once(AMFPHP_BASE . "amf/io/AMFBaseSerializer.php");
		$deserializer = new AMFBaseDeserializer($amf->rawData); // deserialize the data
	}
	else
	{
		include_once(AMFPHP_BASE . "amf/io/AMFDeserializer.php");
		include_once(AMFPHP_BASE . "amf/io/AMFSerializer.php");
		$deserializer = new AMFDeserializer($amf->rawData); // deserialize the data
	}
	
	$deserializer->deserialize($amf); // run the deserializer
	
	//Add some headers
	$headers = $amf->_headerTable;
	if(isset($headers) && is_array($headers))
	{
		foreach($headers as $value){
			Headers::setHeader($value->name, $value->value);
		}
	}
	
	//Set as a describe service
	$describeHeader = $amf->getHeader(AMFPHP_SERVICE_BROWSER_HEADER);
   
	if ($describeHeader !== false) {
		if($GLOBALS['amfphp']['disableDescribeService'])
		{
			//Exit
			trigger_error("Service description not allowed", E_USER_ERROR);
			die();
		}
		$bodyCopy = &$amf->getBodyAt(0);
		$bodyCopy->setSpecialHandling('describeService');
		$bodyCopy->noExec = true;
	}
}


/**
 * Executes each of the bodys
 */
function batchProcessFilter(AMFObject &$amf)
{
	$bodycount = $amf->numBody();
	$actions = $GLOBALS['amfphp']['actions'];
	for ($i = 0; $i < $bodycount; $i++) {
		$bodyObj = &$amf->getBodyAt($i);
		foreach($actions as $action){
			$results = $action($bodyObj);
			if($results === false){
				break;
			}
		}
	}
}

/**
 * Serializes the object
 */
function serializationFilter (AMFObject &$amf) {
	if($GLOBALS['amfphp']['native'] === true && function_exists('amf_decode'))
	{
		$serializer = new AMFBaseSerializer(); // Create a serailizer around the output stream
	}
	else
	{
		$serializer = new AMFSerializer(); // Create a serailizer around the output stream
	}
	$amf->outputStream = $serializer->serialize($amf); // serialize the data
}
?>