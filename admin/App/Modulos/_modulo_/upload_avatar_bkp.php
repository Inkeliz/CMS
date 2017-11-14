<?php
	header("Expires: Mon, 26 Jul 1990 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	include($_SERVER['INCLUDE_PATH'].'/admin/App/Lib/class-ws-v1.php');


	$tmp_name 	= 	$_FILES['arquivo']["tmp_name"];
	$size 		= 	$_FILES['arquivo']["size"];
	$type		= 	$_FILES['arquivo']["type"];
	$nome 		= 	url_amigavel_filename($_FILES['arquivo']["name"]);
	$ext		= 	strtolower(substr($nome,(strripos($nome,'.')+1)));
	$ext		= 	str_replace(array("jpeg"),array("jpg"),$ext);
	$token 		= 	md5(uniqid(rand(), true));
	$nome_novo 	=	strtolower($token.'.'.$ext);

		if(!file_exists($_SERVER['INCLUDE_PATH'].'/website/assets')){				mkdir($_SERVER['INCLUDE_PATH'].'/website/assets');					}		
		if(!file_exists($_SERVER['INCLUDE_PATH'].'/website/assets/upload-files')){	mkdir($_SERVER['INCLUDE_PATH'].'/website/assets/upload-files');	}		
		if(move_uploaded_file( $tmp_name ,$_SERVER['INCLUDE_PATH'].'/website/assets/upload-files/'.$nome_novo)){
				$_biblioteca_					= new MySQL();
				$_biblioteca_->set_table(PREFIX_TABLES.'ws_biblioteca');
				$_biblioteca_->set_insert('file',		$nome_novo);
				$_biblioteca_->set_insert('filename',	$nome);
				$_biblioteca_->set_insert('token',		$token);
				$_biblioteca_->set_insert('type',		$type);
				$_biblioteca_->set_insert('upload_size',filesize($_SERVER['INCLUDE_PATH'].'/website/assets/upload-files/'.$nome_novo));
				$_biblioteca_->insert();
				echo  $nome_novo;
				exit;

			}
	
?>