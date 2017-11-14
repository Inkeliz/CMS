<?
	#######################################################################
	#
	#		AQUI VERIFICAMOS O PATH QUE O WEBSHEEP FOI INSTALADO 
	#		CASO ESTEJA INCOERENTE OU SEJA ACESSADO PELO WS-SET-PATS
	#		PROCESSA OS ARQUIVOS QUE SETAM O CAMINHO ROOT DO SISTEMA
	#
	#######################################################################
	$path_exists 	= isset($_SERVER['INCLUDE_PATH']);
	$rootws_exists 	= isset($_SERVER['ROOT_WEBSHEEP']);
	$path_diff 		= @$_SERVER['INCLUDE_PATH']			!=INCLUDE_PATH;
	$path_isNull    = @$_SERVER['INCLUDE_PATH']			=="null";
	$rootws_diff 	= @$_SERVER['ROOT_WEBSHEEP']		!=ROOT_WEBSHEEP;
	$rootws_isNull 	= @$_SERVER['ROOT_WEBSHEEP']		=="null";
	$rootws_isBlank = ROOT_WEBSHEEP						=="";

	#######################################################################
	#	SETAMOS AS CONDIÇÕES NECESSÁRIAS PARA EDIÇÃO DOS PATHS
	#######################################################################
	if( 
		basename($_SERVER['REQUEST_URI'])=="ws-set-paths" ||
		(
			(!$path_exists 		|| $path_isNull 	|| $path_diff) 	&& 
			(!$rootws_exists 	|| $rootws_isBlank 	|| $rootws_diff || $rootws_isNull ) 
		)
	){

	#######################################################################
	#	EXCLUI OS HTACCESS DO SITE E SISTEMA
	#######################################################################
		@unlink(dirname(__FILE__).'/../../.htaccess');
		@unlink(dirname(__FILE__).'/../../../.htaccess');
		
	#######################################################################
	#	TRATAMOS O PATH CASO SEJA APENAS UM PONTO
	#######################################################################
		$rootws = (dirname(ROOT_WEBSHEEP)=='.') ? "" : dirname(ROOT_WEBSHEEP);
		
	#######################################################################
	#	EXECUTAMOS A FUNÇÃO QUE GRAVA OS ARQUIVOS NOVAMENTE
	#######################################################################
		refresh_Path_AllFiles(INCLUDE_PATH, $rootws);

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