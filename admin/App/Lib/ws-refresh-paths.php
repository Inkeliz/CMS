<?

	############################################################################################################################################
	# DEFINIMOS O ROOT DO SISTEMA
	############################################################################################################################################
		if(!defined("ROOT_WEBSHEEP")){$path = substr(dirname($_SERVER['REQUEST_URI']),1);define('ROOT_WEBSHEEP',(($path=="") ? "/" : '/'.$path.'/'));}
		if(!defined("INCLUDE_PATH")) {$includePath 	= substr(str_replace("\\","/",getcwd()),0,strpos(str_replace("\\","/",getcwd()),'admin'));define("INCLUDE_PATH",$includePath);}

	#######################################################################
	#
	#		AQUI VERIFICAMOS O PATH QUE O WEBSHEEP FOI INSTALADO 
	#		CASO ESTEJA INCOERENTE OU SEJA ACESSADO PELO WS-SET-PATS
	#		PROCESSA OS ARQUIVOS QUE SETAM O CAMINHO ROOT DO SISTEMA
	#
	#######################################################################

	#######################################################################
	#	SETAMOS AS CONDIÇÕES NECESSÁRIAS PARA EDIÇÃO DOS PATHS
	#######################################################################
	if( 
		basename($_SERVER['REQUEST_URI'])=="ws-set-paths" || !file_exists(INCLUDE_PATH.'.htaccess')
	){

	#######################################################################
	#	EXCLUI OS HTACCESS DO SITE E SISTEMA
	#######################################################################
		@unlink(INCLUDE_PATH.'.htaccess');
		@unlink(INCLUDE_PATH.'admin/.htaccess');
		
	#######################################################################
	#	EXECUTAMOS A FUNÇÃO QUE GRAVA OS ARQUIVOS NOVAMENTE
	#######################################################################
		refresh_Path_AllFiles();
	#######################################################################
	#	DEIXAMOS DORMINDO 0.5 SEGUNDOS APENAS PARA 
	#	DAR TEMPO DE PROCESSAR OS ARQUIVOS NO SERVIDOR 
	#######################################################################
		sleep(0.5);

	#######################################################################
	#	DAMOS UM REFRESH OU REDIRECT NO PAINEL JÁ COM O PATH CONFIGURADO
	#######################################################################
		if(dirname($_SERVER['REQUEST_URI'])=="admin"){
			header('Refresh:0');
			exit;
		}else{
			header('Location: '.dirname($_SERVER['REQUEST_URI']));
			exit;
		}

	#######################################################################
	}