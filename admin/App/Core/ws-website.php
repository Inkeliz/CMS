<? 
	if(!defined("ROOT_WEBSHEEP"))	{
	$path = substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'admin'));
	$path = implode(array_filter(explode('/',$path)),"/");
	define('ROOT_WEBSHEEP',(($path=="") ? "/" : '/'.$path.'/'));
}

	if(!defined("INCLUDE_PATH"))	{$includePath 	= substr(str_replace("\\","/",getcwd()),0,strpos(str_replace("\\","/",getcwd()),'admin'));define("INCLUDE_PATH",$includePath);}

	if(file_exists(INCLUDE_PATH.'website/ws-config.php')){
		include_once(INCLUDE_PATH.'admin/app/lib/class-ws-v1.php');
		ws::init();
	}else{
		include_once(INCLUDE_PATH.'admin/app/core/init.php');
	}
