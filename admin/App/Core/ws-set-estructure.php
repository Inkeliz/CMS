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
		include_once($_SERVER['INCLUDE_PATH'].'/admin/App/Lib/ws-globals-functions.php');

	#########################################################################
	# CRIAMOS O HTACCES PADRÃO DO SISTEMA PARA O CAMINHO ROOT
	#########################################################################
		$pathOrigem 	= $_SERVER['INCLUDE_PATH'].'/admin/App/Templates/txt/ws-first-htaccess.txt';
		$pathDestino 	= $_SERVER['INCLUDE_PATH'].'/.htaccess';

		file_put_contents($pathDestino, str_replace(
													array(
														'{{INCLUDE_PATH}}',
														'{{ROOT_WEBSHEEP}}'
													), array(
														$_SERVER['INCLUDE_PATH'],
														$_SERVER['ROOT_WEBSHEEP']
													),
													file_get_contents($pathOrigem)
									)
						);


	#########################################################################
	# CRIAMOS TODOS OS DIRETORIOS DO WEBSITE A SER MONTADO
	#########################################################################
		_excluiDir($_SERVER['INCLUDE_PATH'].'/ws-install-master');

		_mkdir($_SERVER['INCLUDE_PATH'].'/ws-cache');
		_mkdir($_SERVER['INCLUDE_PATH'].'/ws-bkp');
		_mkdir($_SERVER['INCLUDE_PATH'].'/ws-tmp');
		_mkdir($_SERVER['INCLUDE_PATH'].'/ws-shortcodes');
		_mkdir($_SERVER['INCLUDE_PATH'].'/website');
		_mkdir($_SERVER['INCLUDE_PATH'].'/website/includes');
		_mkdir($_SERVER['INCLUDE_PATH'].'/website/plugins');
		_mkdir($_SERVER['INCLUDE_PATH'].'/website/assets/css');
		_mkdir($_SERVER['INCLUDE_PATH'].'/website/assets/js');
		_mkdir($_SERVER['INCLUDE_PATH'].'/website/assets/img');
		_mkdir($_SERVER['INCLUDE_PATH'].'/website/assets/template');
		_mkdir($_SERVER['INCLUDE_PATH'].'/website/assets/fonts');
		_mkdir($_SERVER['INCLUDE_PATH'].'/website/assets/upload-files/thumbnail');
		_mkdir($_SERVER['INCLUDE_PATH'].'/website/assets/libraries');

		_copyFolder($_SERVER['INCLUDE_PATH'].'/admin/App/Modulos/plugins', $_SERVER['INCLUDE_PATH'].'/website/plugins');
		_copy($_SERVER['INCLUDE_PATH']."/admin/App/Lib/my-shortcode.php",$_SERVER['INCLUDE_PATH']."/ws-shortcodes/my-shortcode.php",false);

		_file_put_contents($_SERVER['INCLUDE_PATH'].'/website/includes/header.php', 'Header<hr>',false);
		_file_put_contents($_SERVER['INCLUDE_PATH'].'/website/includes/erro404.php', 'ERRO 404!',false);
		_file_put_contents($_SERVER['INCLUDE_PATH'].'/website/includes/inicio.php', 'Olá mundo!',false);
		_file_put_contents($_SERVER['INCLUDE_PATH'].'/website/includes/footer.php', '<hr>Footer',false);
		_file_put_contents($_SERVER['INCLUDE_PATH'].'/website/assets/.htaccess', 'RewriteEngine Off',false);

		
	######################################################################################################################################
	# CASO NÃO TENHA AINDA O ARQUIVOO NO LUGAR CERTO E ESTEJA FAZENDO UPDATE AO INVEZ DE INSTALL
	######################################################################################################################################
		$pathFile 	= $_SERVER['INCLUDE_PATH'].'/ws-bkp/.htaccess';
		$conteudo 	= "<IfModule mod_rewrite.c>\nRewriteEngine On\nRewriteCond %{SCRIPT_FILENAME} !-f\nRewriteRule ^(.*)$ ws-download-template.php\n</IfModule>";

		$origem 	= $_SERVER['INCLUDE_PATH']."/admin/App/Lib/ws-download-template.php";
		$destino 	= $_SERVER['INCLUDE_PATH']."/ws-bkp/ws-download-template.php";

		_copy($origem,$destino);
		_file_put_contents($pathFile,$conteudo);

