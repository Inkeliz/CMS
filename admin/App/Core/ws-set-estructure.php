<?php
	#########################################################################
	#########################################################################
	#########################################################################
	#########################################################################
	#
	# 	CONSTRUINDO A ESTRUTURA DER PASTAS E ARQUIVOS BASICOS DO SISTEMA
	#
	#########################################################################
	#########################################################################
	#########################################################################
	#########################################################################

	##########################################################################################################
	# 	FUNÇÕES GLOBAIS DO SISTEMA
	##########################################################################################################
		include_once(__DIR__.'/../Lib/ws-globals-functions.php');

	#########################################################################
	# COPIAMOS O HTACCES PADRÃO DO SISTEMA PARA O CAMINHO ROOT
	#########################################################################
		$pathOrigem 	= ROOT_DOCUMENT.'/admin/App/Templates/txt/ws-first-htaccess.txt';
		$pathDestino 	= ROOT_DOCUMENT.'/.htaccess';
		if(file_exists($pathDestino)){
			_copy($pathDestino, $pathDestino);
		}

	#########################################################################
	# CRIAMOS TODOS OS DIRETORIOS DO WEBSITE A SER MONTADO
	#########################################################################
		_mkdir(ROOT_DOCUMENT 				.'/website');
		_mkdir(ROOT_DOCUMENT 				.'/ws-bkp');
		_mkdir(ROOT_DOCUMENT 				.'/ws-cache');
		_mkdir(ROOT_DOCUMENT 				.'/ws-tmp',0700);
		_mkdir(ROOT_DOCUMENT 				.'/website/includes');
		_mkdir(ROOT_DOCUMENT 				.'/website/plugins');
		_mkdir(ROOT_DOCUMENT 				.'/website/assets/upload-files/thumbnail');
		_mkdir(ROOT_DOCUMENT 				.'/website/assets/libraries');
		_mkdir(ROOT_DOCUMENT 				.'/website/assets/css');
		_mkdir(ROOT_DOCUMENT 				.'/website/assets/js');
		_mkdir(ROOT_DOCUMENT 				.'/website/assets/img');
		_mkdir(ROOT_DOCUMENT 				.'/website/assets/template');
		_mkdir(ROOT_DOCUMENT 				.'/website/assets/fonts');
		_mkdir(ROOT_DOCUMENT 				.'/ws-shortcodes');
		_excluiDir(ROOT_DOCUMENT 			.'/ws-install-master');
		_copyFolder(ROOT_DOCUMENT 			.'/admin/App/Modulos/plugins', ROOT_DOCUMENT . '/website/plugins');
		if (!file_exists(ROOT_DOCUMENT 		.'/website/includes/header.php')) 	_file_put_contents(ROOT_DOCUMENT . '/website/includes/header.php', 'Header<hr>');
		if (!file_exists(ROOT_DOCUMENT 		.'/website/includes/erro404.php')) 	_file_put_contents(ROOT_DOCUMENT . '/website/includes/erro404.php', 'ERRO 404!');
		if (!file_exists(ROOT_DOCUMENT 		.'/website/includes/inicio.php')) 	_file_put_contents(ROOT_DOCUMENT . '/website/includes/inicio.php', 'Olá mundo!');
		if (!file_exists(ROOT_DOCUMENT 		.'/website/includes/footer.php')) 	_file_put_contents(ROOT_DOCUMENT . '/website/includes/footer.php', '<hr>Footer');
		if (file_exists(ROOT_DOCUMENT 		.'/website/index.php')) 			@rename(ROOT_DOCUMENT . '/website/index.php', ROOT_DOCUMENT . '/website/index_bkp.php');
		_file_put_contents(ROOT_DOCUMENT 	.'/website/assets/.htaccess', 'RewriteEngine Off');
		_copy(ROOT_DOCUMENT."/admin/App/Lib/my-shortcode.php",ROOT_DOCUMENT."/ws-shortcodes/my-shortcode.php");

	######################################################################################################################################
	# CASO NÃO TENHA AINDA O ARQUIVOO NO LUGAR CERTO E ESTEJA FAZENDO UPDATE AO INVEZ DE INSTALL
	######################################################################################################################################
		$pathFile 	= ROOT_DOCUMENT.'/ws-bkp/.htaccess';
		$conteudo 	= "<IfModule mod_rewrite.c>\nRewriteEngine On\nRewriteCond %{SCRIPT_FILENAME} !-f\nRewriteRule ^(.*)$ ws-download-template.php\n</IfModule>";

		$origem 	= ROOT_DOCUMENT."/admin/App/Lib/ws-download-template.php";
		$destino 	= ROOT_DOCUMENT."/ws-bkp/ws-download-template.php";

		_copy($origem,$destino);
		_file_put_contents($pathFile,$conteudo);

