<?
############################################################################################################################################
# DEFINIMOS O ROOT DO SISTEMA
############################################################################################################################################

	if(!defined("ROOT_WEBSHEEP"))	{
		$path = substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'admin'));
		$path = implode(array_filter(explode('/',$path)),"/");
		define('ROOT_WEBSHEEP',(($path=="") ? "/" : '/'.$path.'/'));
	}
	if(!defined("INCLUDE_PATH")) {$includePath 	= substr(str_replace("\\","/",getcwd()),0,strpos(str_replace("\\","/",getcwd()),'admin'));define("INCLUDE_PATH",$includePath);}

############################################################################################################################################
	include_once(INCLUDE_PATH.'website/ws-config.php');
?>
<iframe id="painelMySQL" src="<?=ROOT_WEBSHEEP.'admin/app/modulos/phpMyAdmin/index.php'?>" style="top: 0;position: relative; width: 100%;height: calc(100% - 56px);"></iframe>
