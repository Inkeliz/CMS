<?php
	include($_SERVER['INCLUDE_PATH'].'/admin/App/Lib/class-ws-v1.php');
	$file_url = $_SERVER['INCLUDE_PATH'].'/website/'.$_GET['filename'];
	header('Content-Type: application/octet-stream');
	header("Content-Transfer-Encoding: Binary"); 
	header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\""); 
	readfile($file_url); 
?>