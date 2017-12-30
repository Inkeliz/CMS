<?php 
	############################################
	#	DEFINE CHARSET
	############################################
	 header("Content-Type: text/html; charset=utf-8",true);

	############################################################################################################################################
	# DEFINIMOS O ROOT DO SISTEMA
	############################################################################################################################################
	if(!defined("ROOT_WEBSHEEP"))	{
	$path = substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'admin'));
	$path = implode(array_filter(explode('/',$path)),"/");
	define('ROOT_WEBSHEEP',(($path=="") ? "/" : '/'.$path.'/'));
}

	if(!defined("INCLUDE_PATH")){define("INCLUDE_PATH",str_replace("\\","/",substr(realpath(__DIR__),0,strrpos(realpath(__DIR__),'admin'))));}


	############################################
	#	CARREGA SESSION
	############################################
	$user = new session();

	##########################################################################################
	#  VERSÃO DO SISTEMA   
	##########################################################################################
	$localVersion  = json_decode(file_get_contents(INCLUDE_PATH.'admin/app/templates/json/ws-update.json'));

	##########################################################################################
	#  PUXAMOS OS DADOS BÁSICOS DA INSTALAÇÃO
	##########################################################################################
	$setupdata 					= new MySQL();
	$setupdata->set_table(PREFIX_TABLES.'setupdata');
	$setupdata->set_order('id','DESC');
	$setupdata->set_limit(1);
	$setupdata->debug(0);
	$setupdata->select();
	$setupdata = $setupdata->fetch_array[0];

	##########################################################################################
	#  VERIFICAMOS AS PERMIÇÕES DE ACESSO A FERRAMENTA DO USUÁRIO 
	##########################################################################################
	$_permissao_user_ 					= new MySQL();
	$_permissao_user_->set_table(PREFIX_TABLES.'ws_user_link_ferramenta');

	##########################################################################################
	#  CASO O PAINEL SEJA INICIADO NO MODO 'INSECURE'  IGNORA AS PERMISSÕES E LIBERA TUDO
	##########################################################################################
	if (SECURE!=FALSE && $user->verify()){
		$_permissao_user_->set_where('id_user="'.$user->get('id').'"');
	}
	$_permissao_user_->select();

	##########################################################################################
	#  GUARDAMOS TODAS AS PERMIÇÕES EM UMA ARRAY
	##########################################################################################
	$permTool = array();
	foreach ($_permissao_user_->fetch_array as $tool) {$permTool[] = $tool['id_ferramenta'];}

	##########################################################################################
	#  GRAVA UM JSON COM OS PLUGINS ATIVOS NO SITE
	##########################################################################################
	refreshJsonPluginsList();
	$string 		= file_get_contents(INCLUDE_PATH.'admin/app/templates/json/ws-plugin-list.json');
	$jsonPlugins 	= json_decode($string);

	##########################################################################################
	#  INICIAMOS A CLASSE TEMPLATE
	##########################################################################################
	$TEMPLATE 					= new Template(INCLUDE_PATH.'admin/app/templates/html/ws-dashboard-template.html', true);
	// $TEMPLATE->ROOT_WEBSHEEP 	= ROOT_WEBSHEEP;

	
	##########################################################################################
	#  ABRIMOS A PÁGINA INICIAL DO SISTEMA PARA WEBMASTER
	##########################################################################################
	$TEMPLATE->INIT_DASHBOARD = '<iframe src="http://dashboard.websheep.com.br" style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;margin:0;"></iframe>';


	##########################################################################################
	#  CASO NÃO TENHA NENHUM SPLASH CADASTRADO, PUXAMOS O PADRÃO DO SISTEMA
	##########################################################################################
	if($setupdata['splash_img']==""){
			$TEMPLATE->block("NOSPLASH");
	}else{
			$TEMPLATE->SPLASHSCREEN_IMG = $setupdata['splash_img'];
			$TEMPLATE->block("SPLASHSCREEN");
	}

	##########################################################################################
	#  AQUI VERIFICAMOS APENAS PARA AVISAR QUE ESTÁ NO MODO 'INSECURE'   
	##########################################################################################

		if (SECURE!=FALSE && @$log_session->get('ws_log')==1){
			$name = $user->get('nome');
		}else{
			$name = "<span style='color:#f00;font-weight:bold;'>INSECURE</span>";
		}

	##########################################################################################
	#  DEFINE A SAUDAÇÃO   
	##########################################################################################
		$TEMPLATE->SAUDACAO =  ws::getlang('dashboard>welcome',array('[avatar]','[username]'),array('',$name));


	##########################################################################################
	#  LISTANDO OS PLUGINS DE TOPO   
	##########################################################################################
		foreach ($jsonPlugins as $plugin) {
			$contents 				=	$plugin;
			$_menu_existe  	 		= isset($contents->menu);
			$_painel_existe  		= (isset($contents->painel) && $contents->painel!="");
			$_menuArrayAndTopo   	= (is_array(@$contents->menu) && in_array("topo",$contents->menu));
			$_menuStringAndTopo   	= (is_string(@$contents->menu) && @$contents->menu =="topo");
			$is_loadType   			= isset($contents->loadType) && is_array($contents->loadType);

			##########################################################################################
			#  CASO ELE ESTEJA CONFIGURADO PARA APARECER NO MENU DE TOPO   
			##########################################################################################
				if(($_menu_existe  && $_painel_existe ) && ($_menuArrayAndTopo	||	$_menuStringAndTopo)){
					if($is_loadType){
						$dataType 	= @$contents->loadType[0];
						$dataW 		= @$contents->loadType[1];
						$dataH 		= @$contents->loadType[2];
					}else{
						$dataType 	= $contents->loadType;
						$dataW 		= 500;
						$dataH 		= 500;
					}

					##########################################################################################
					#  SEPARA AS VARIÁVEIS DA LISTAGEM   
					##########################################################################################
						$TEMPLATE->LI_W 	= @$dataW;
						$TEMPLATE->LI_H 	= @$dataH;
						$TEMPLATE->LI_TYPE 	= @$dataType;
						if(@trim($dataType)!="iframe"){
							if (filter_var($contents->painel, FILTER_VALIDATE_URL) === FALSE) {
								$link = ws::rootPath.$setupdata['url_plugin'].'/'.$contents->painel;
							} else {
								$link = $contents->painel;
							}
							$TEMPLATE->LI_PATH 	= dirname($link);;
							$TEMPLATE->LI_FILE 	= $link;
						}else{
							if (filter_var($contents->realPath, FILTER_VALIDATE_URL) === FALSE) {
								$link = ws::rootPath.$contents->realPath;
							} else {
								$link = $contents->realPath;
							}
							$TEMPLATE->LI_PATH 	= dirname($link);
							$TEMPLATE->LI_FILE 	= $contents->painel;
						}

							if (filter_var($contents->realPath, FILTER_VALIDATE_URL) === FALSE) {
								$link = ws::rootPath.$contents->realPath;
							} else {
								$link = $contents->realPath;
							}

						$TEMPLATE->LI_HREF 	= $link.'/'.$contents->painel;

						if(isset($contents->icon) && $contents->icon!="" &&	file_exists(ws::includePath.'website/'.$contents->realPath.'/'.$contents->icon)){
							$TEMPLATE->LI_ICON 	= '<img src="'.ws::rootPath.$contents->realPath.'/'.$contents->icon.'" width="15px"/>';
						}else{
							$TEMPLATE->clear("LI_ICON");
						}
						$TEMPLATE->LI_NAME 	= $contents->pluginName;

					##########################################################################################
					#  RETORNA A STRING DO PLUGIN   
					##########################################################################################
						$TEMPLATE->block('TOP_ICONS_PLUGINS');
				}
		}

	##########################################################################################
	#  MENSAGEM COPYRIGHT DO RODAPÉ   
	##########################################################################################
	$TEMPLATE->copyright 	= ws::getlang('dashboard>footerCopyright',array('[name]','[system_version]'),array($setupdata['client_name'],$localVersion->version));

	##########################################################################################
	#  VERIFICA SE EXISTE 2° PATH NA URL
	##########################################################################################
	$keyAccess = ws::urlPath(2,false);
 	if($keyAccess){
		##########################################################################################
		#  CASO O 2° PATH SEJA UM ACESSO DIRETO, PUXAMOS DA BASE A CHAVE
		##########################################################################################
		$ws_direct_access 				= new MySQL();
		$ws_direct_access->set_table(PREFIX_TABLES.'ws_direct_access');
		$ws_direct_access->set_where('keyaccess="'.$keyAccess.'"');
		$ws_direct_access->select();
		$_num_rows 						= $ws_direct_access->_num_rows;
		$ws_direct_access 				= $ws_direct_access->fetch_array;
		##########################################################################################
		#  CASO EXISTA O SERIALKEY RETORNA 
		##########################################################################################
		if($_num_rows>0){
			$TEMPLATE->type_obj 		= $ws_direct_access[0]['type_obj'];
			$TEMPLATE->id_tool 			= $ws_direct_access[0]['id_tool'];
			$TEMPLATE->id_item 			= $ws_direct_access[0]['id_item'];
			$TEMPLATE->id_gal 			= $ws_direct_access[0]['id_gal'];
			$TEMPLATE->block('DIRECTACCESS');
		}else{
			$TEMPLATE->clear('DIRECTACCESS');
		}
 	}		
 	
 	$TEMPLATE->ws_rootPath 			= ws::rootPath;
 	$TEMPLATE->classHTMLtypeAcess 	= ( isset($_num_rows) && $_num_rows>0 ) ? "IframeModel" : "";

	##########################################################################################
	#  VERIFICA SE É O EDITOR EM POPUP   
	##########################################################################################


 	  $TEMPLATE->CLASS_POPUP 	= (ws::urlPath(2,false)=="popup") ? 'popup' : '';
 	  $TEMPLATE->LoadDirectTool = (ws::urlPath(3,false)=="code-editor") ? ws::rootPath.'admin/app/ws-modules/ws-webmaster/index.php' : 'null';




	##########################################################################################
	#  RETORNA O HTML MONTADO   
	##########################################################################################
	$TEMPLATE->block("DASHBOARD");

	##########################################################################################
	#  PRINTA RESULTADO   
	##########################################################################################
	$TEMPLATE->show();

