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
		if(!defined("ROOT_WEBSHEEP"))	{
			$path = substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'admin'));
			$path = implode(array_filter(explode('/',$path)),"/");
			define('ROOT_WEBSHEEP',(($path=="") ? "/" : '/'.$path.'/'));
		}
		if(!defined("INCLUDE_PATH")){define("INCLUDE_PATH",str_replace("\\","/",substr(realpath(__DIR__),0,strrpos(realpath(__DIR__),'admin'))));}

	############################################################################################################################
	# CASO NÃO TENHA SIDO VERIFICADO OU SEJA UMA NOVA INSTALAÇÃO/UPDATE IMPORTA VERIFICAÇÃO DO SERVIDOR
	############################################################################################################################
		if(
			!file_exists(INCLUDE_PATH.'admin/app/config/ws-server-ok')
		){
			include_once(INCLUDE_PATH.'admin/app/config/ws-verify-server.php');
		}

	############################################################################################################################################
	# IMPORTAMOS AS FUNÇÕES GLOBAIS 
	############################################################################################################################################
		include_once(INCLUDE_PATH.'admin/app/lib/ws-globals-functions.php');

	############################################################################################################################
	# CASO NÃO EXISTA O 'ws-config.php' IMPORTA A TELA DE SETUP
	############################################################################################################################
		if(!file_exists(INCLUDE_PATH.'ws-config.php')) {
			include_once(INCLUDE_PATH.'admin/app/core/ws-setup.php');
			exit;
		}

	############################################################################################################################################
	# ANTES DE TUDO, VERIFICA SE JÁ TEMOS AS VARIÁVEIS NO HTACCESS E SE ESTÃO CORRETAS
	############################################################################################################################################
		include_once(INCLUDE_PATH.'admin/app/lib/ws-refresh-paths.php');


	############################################################################################################################
	# IMPORTAMOS A CLASSE PADRÃO DO SISTEMA
	############################################################################################################################
		include_once(INCLUDE_PATH.'admin/app/lib/class-ws-v1.php');

	############################################################################################################################
	#	CASO SEJA O 1° ACESSO, IMPORTA A TELA DE INSTALAÇÃO
	############################################################################################################################
		if(file_exists(INCLUDE_PATH.'admin/app/config/firstacess') && file_get_contents(INCLUDE_PATH.'admin/app/config/firstacess')=='true'){
			include(INCLUDE_PATH.'admin/app/core/ws-install.php');exit;
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
			include(INCLUDE_PATH.'admin/app/core/ws-dashboard.php');
			exit;
		}

	############################################################################################################################
	#	CASO ESTEJA OFFLINE JÁ DIRECIONA PRO LOGIN
	############################################################################################################################
		include(INCLUDE_PATH.'admin/app/ws-modules/ws-login/index.php');
