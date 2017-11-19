<?php
	############################################################################################################################################
	# DEFINIMOS O ROOT DO SISTEMA
	############################################################################################################################################
	if(!defined("ROOT_WEBSHEEP"))	{
		$path = substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'ws-download-now'));
		$path = implode(array_filter(explode('/',$path)),"/");
		define('ROOT_WEBSHEEP',(($path=="") ? "/" : '/'.$path.'/'));
	}

if(!defined("INCLUDE_PATH")) {$includePath 	= substr(str_replace("\\","/",getcwd()),0,strpos(str_replace("\\","/",getcwd()),'admin'));define("INCLUDE_PATH",$includePath);}

	############################################################################################################################################
	include(INCLUDE_PATH.'admin/app/lib/class-ws-v1.php');
	$file_url = INCLUDE_PATH.'website/'.$_GET['filename'];
	header('Content-Type: application/octet-stream');
	header("Content-Transfer-Encoding: Binary"); 
	header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\""); 
	readfile($file_url); 
?>