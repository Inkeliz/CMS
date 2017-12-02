<?php

	############################################################################################################################################
	# DEFINIMOS O ROOT DO SISTEMA
	############################################################################################################################################
	if(!defined("ROOT_WEBSHEEP"))	{
	$path = substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'admin'));
	$path = implode(array_filter(explode('/',$path)),"/");
	define('ROOT_WEBSHEEP',(($path=="") ? "/" : '/'.$path.'/'));
}

if(!defined("INCLUDE_PATH")){define("INCLUDE_PATH",str_replace("\\","/",substr(realpath(__DIR__),0,strrpos(realpath(__DIR__),'admin'))));}

	##########################################################################################################
	# IMPORTAMOS A CLASSE PADRÃO DO WEBSHEEP
	##########################################################################################################
	include_once(INCLUDE_PATH.'admin/app/lib/class-ws-v1.php');

	##########################################################################################################
	# INICIA A SESSÃO
	##########################################################################################################	
	// _session();
	$session = new session();
	##########################################################################################################
	# DADOS DA FERRAMENTA
	##########################################################################################################
	$_FERRAMENTA=new MySQL();
	$_FERRAMENTA->set_table(PREFIX_TABLES.'ws_ferramentas');
	$_FERRAMENTA->set_where('id="'.$_GET['ws_id_ferramenta'].'"');
	$_FERRAMENTA->debug(0);
	$_FERRAMENTA->select();
	$_FERRAMENTA = $_FERRAMENTA->fetch_array[0];

	##########################################################################################################
	# IMPORTA AS CONFIGURAÇÕES DO MÓDULO
	##########################################################################################################	
	$session->set('PATCH','app/modulos/_modulo_');
	$session->set('ws_id_ferramenta',$_FERRAMENTA['id']);

	##########################################################################################################
	# CASO NÃO TENHA AINDA UM TOKEN GROUP
	##########################################################################################################
	if(empty($_GET['token_group'])){
		$_GET['token_group'] = _token(PREFIX_TABLES . '_model_item', 'token');
	}

	##########################################################################################################
	# VERIFICAMOS SE EXISTE RASCUINHOS NAS IMAGENS
	##########################################################################################################
	$_INNER_IMG				= new MySQL();
	$_INNER_IMG->set_table(PREFIX_TABLES."_model_img");
	$_INNER_IMG->set_where('ws_draft="0"');
	$_INNER_IMG->set_where('AND ws_id_ferramenta="'.$_GET['ws_id_ferramenta'].'"');
	$_INNER_IMG->set_where('AND id_item="'.$_GET['id_item'].'"');
	$_INNER_IMG->select();

	##########################################################################################################
	# VERIFICAMOS SE EXISTE RASCUINHOS NAS IMAGENS
	##########################################################################################################
	$_RASCUNHO_IMG				= new MySQL();
	$_RASCUNHO_IMG->set_table(PREFIX_TABLES."_model_img");
	$_RASCUNHO_IMG->set_where('ws_draft="1"');
	$_RASCUNHO_IMG->set_where('AND ws_id_ferramenta="'.$_GET['ws_id_ferramenta'].'"');
	$_RASCUNHO_IMG->set_where('AND id_item="'.$_GET['id_item'].'"');
	$_RASCUNHO_IMG->select();


	/*
		LISTAR TODASSS AS IMAGENS, TROCANDO APENAS A CLASSE DE DRAFT OU ORIGINAL
	*/

	##########################################################################################################
	# IMPORTAMOS A CLASSE DE TEMPLATE
	##########################################################################################################
	$TEMPLATE 						= new Template(INCLUDE_PATH.'admin/app/templates/html/Modulos/ws-tool-imagens-template.html', true);

	$TEMPLATE->ws_rootPath 			= ws::rootPath;
	$TEMPLATE->WS_ID_FERRAMENTA 	= $_FERRAMENTA['id'];
	$TEMPLATE->TOKEN_GROUP			= $_GET['token_group'];
	$TEMPLATE->ID_ITEM 				= $_GET['id_item'];
	$TEMPLATE->ID_CAT 				= $_GET['id_cat'];
	$TEMPLATE->ID_NIVEL 			= $_GET['ws_nivel'];
	$TEMPLATE->TITULO 				= $_FERRAMENTA['_tit_menu_'];
	$TEMPLATE->PATH 				= 'app/modulos/_modulo_';
	$TEMPLATE->HTTPVARS				= http_build_query($_GET);

	if(isset($_GET['back'])){
		$TEMPLATE->BACK = '&back='.@$_GET['back'];
	}else{
		$TEMPLATE->clear("BACK");
	}

	if(isset($_GET['ws_nivel']) && $_GET['ws_nivel']>-1 ){ 
		criaRascunho($_FERRAMENTA['id'],$_GET['id_item']);
		$TEMPLATE->block('BOT_BACK');
	}else{
		$TEMPLATE->block('BOT_PUBLICAR');
	}


	##########################################################################################################
	# PESQUISAMOS AS IMAGENS DO ÍTEM
	##########################################################################################################
	$s 					= new MySQL();
	$s->set_table(PREFIX_TABLES.'_model_img');
	##########################################################################################################
	# PUXAMOS APENAS AS IMAGENS DO RASCUNHO
	##########################################################################################################
	$s->set_where('id_item="'.$_GET['id_item'].'" ');
	$s->set_where('AND ws_id_ferramenta="'.$_GET['ws_id_ferramenta'].'"');
	$s->set_order('posicao','ASC');
	$s->select();
	#########################################################################################################
	foreach($s->fetch_array as $img){
		$TEMPLATE->LI_ID 	= $img['id'];
		$TEMPLATE->LI_IMG	= $img['imagem'];
		if($img['ws_draft']==1){
			$TEMPLATE->CLASS_IMG	= "draft";
		}else{
			$TEMPLATE->CLASS_IMG	= "original";

		}
		$TEMPLATE->block('IMG_GAL');
	}
	##########################################################################################################
	# RETORNA O TEMPLATE HTML
	##########################################################################################################
	$TEMPLATE->block('BLOCK_IMG');
	$TEMPLATE->show();