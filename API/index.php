<?php
	include "wp-broadbean-command-processor.php";
	
   	$ip_request = $_SERVER['REMOTE_ADDR'];
	$data = json_decode($HTTP_RAW_POST_DATA, true);

	$processor = new wp_broadbean_command_processor($ip_request);

	$result = $processor->save($data);
    
    // Allow any domain, but we are IP checking so it should be good.
    header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
	header('Content-type: application/json');
	echo json_encode($result);
?>