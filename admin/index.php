<?php 

/*
		########################################################################################
		# 	SEJA BEM VINDO AO WEBSHEEP!
		########################################################################################
			Este arquivo é parte do Websheep CMS
			Websheep é um software livre; você pode redistribuí-lo e/ou 
			modificá-lo dentro dos termos da Licença Pública Geral GNU como 
			publicada pela Fundação do Software Livre (FSF); na versão 3 da 
			Licença, ou qualquer versão posterior.

			Este programa é distribuído na esperança de que possa ser  útil, 
			mas SEM NENHUMA GARANTIA; sem uma garantia implícita de ADEQUAÇÃO
			a qualquer MERCADO ou APLICAÇÃO EM PARTICULAR. 
			
			Veja a Licença Pública Geral GNU para maiores detalhes.
			Você deve ter recebido uma cópia da Licença Pública Geral GNU junto
			com este programa, Se não, veja <http://www.gnu.org/licenses/>.
	*/

		ob_start();	

	############################################################################################################################################
	# DEFINIMOS O ROOT DO SISTEMA
	############################################################################################################################################
		define('INCLUDE_PATH', dirname(str_replace(array('\\'), DIRECTORY_SEPARATOR, dirname(__FILE__))));
		define('ROOT_WEBSHEEP', substr(dirname($_SERVER['REQUEST_URI']),1));
		
	############################################################################################################################################
	# IMPORTAMOS AS FUNÇÕES GLOBAIS 
	############################################################################################################################################
	include_once('./App/Lib/ws-globals-functions.php');

	############################################################################################################################################
	# ANTES DE TUDO, VERIFICA SE JÁ TEMOS AS VARIÁVEIS NO HTACCESS E SE ESTÃO CORRETAS
	############################################################################################################################################
	include_once('./App/Lib/ws-refresh-paths.php');

	############################################################################################################################
	# CASO NÃO TENHA SIDO VERIFICADO OU SEJA UMA NOVA INSTALAÇÃO/UPDATE IMPORTA VERIFICAÇÃO DO SERVIDOR
	############################################################################################################################
	if(
		!file_exists($_SERVER['INCLUDE_PATH'].'/admin/App/Config/ws-server-ok')
	){
		include_once(INCLUDE_PATH.'/admin/App/Config/ws-verify-server.php');
	}

	############################################################################################################################
	# CASO NÃO EXISTA O 'ws-config.php' IMPORTA A TELA DE SETUP
	############################################################################################################################
	if(!file_exists($_SERVER['INCLUDE_PATH'].'/ws-config.php')) {
		include_once($_SERVER['INCLUDE_PATH'].'/admin/App/Core/ws-setup.php');
		exit;
	}

	############################################################################################################################
	# IMPORTAMOS A CLASSE PADRÃO DO SISTEMA
	############################################################################################################################
		include_once($_SERVER['INCLUDE_PATH'].'/admin/App/Lib/class-ws-v1.php');

	############################################################################################################################
	#	CASO SEJA O 1° ACESSO, IMPORTA A TELA DE INSTALAÇÃO
	############################################################################################################################
	if(file_exists(ws::getFullPath().'/admin/App/Config/firstacess') && file_get_contents(ws::getFullPath().'/admin/App/Config/firstacess')=='true'){
		include(ws::getFullPath().'/admin/App/Core/ws-install.php');exit;
	}

	############################################################################################################################
	#	CASO ESTEJA LOGADO DIRETAMENTE COM ACCESSKEY
	############################################################################################################################
		if(ws::urlPath(2,false)){
			$keyAccess 			= ws::getTokenRest(ws::urlPath(2,false),false);

			####################################################################################################################
			#	CASO O ACCESSKEY ESTEJA LIGADA DIRETAMENTE A UM ELEMENTO
			#	Por segurança, só libera o acesso se tiver o keyAccess nas duas tabelas
			####################################################################################################################
			$ws_direct_access 				= new MySQL();
			$ws_direct_access->set_table(PREFIX_TABLES.'ws_direct_access');
			$ws_direct_access->set_where('keyaccess="'.ws::urlPath(2,false).'"');
			$ws_direct_access->select();
			$_num_rows = $ws_direct_access->_num_rows;
			$authKey  = ( isset($_num_rows) && $_num_rows>0 && $keyAccess ) ? true : false;
		}else{
			$authKey = false;
		}

	############################################################################################################################
	#	CASO ESTEJA LOGADO IMPORTAMOS O DESKTOP
	############################################################################################################################	
		$log_session = new session();
		if( 
			SECURE==FALSE || 
			(isset($authKey) && $authKey == true) || 
			$log_session->verifyLogin() == true
		){	
			include(ws::getFullPath().'/admin/App/Core/ws-dashboard.php');
			exit;
		}

	############################################################################################################################
	#	CASO ESTEJA OFFLINE JÁ DIRECIONA PRO LOGIN
	############################################################################################################################
		include(ws::getFullPath().'/admin/App/Modulos/login/index.php');
