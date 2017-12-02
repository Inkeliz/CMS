<?
	############################################################################################################################################
	############################################################################################################################################
	############################################################################################################################################
	#
	#
	# FUNÇÕES GLOBAIS DO SISTEMA QUE NECESSITAM DO MYSQLI FUNCTIONANDO
	#
	#
	############################################################################################################################################
	############################################################################################################################################
	############################################################################################################################################

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
	# 	FUNÇÕES GLOBAIS DO SISTEMA
	##########################################################################################################
		include_once(INCLUDE_PATH.'admin/app/lib/ws-globals-functions.php');
		
	##########################################################################################################
	# 	INCLUIMOS A SESSÃO
	##########################################################################################################
		include_once(INCLUDE_PATH.'admin/app/lib/class-session.php');
		
	##########################################################################################################
	# RETORNA UM TOKEN INÉDITO NA COLUNA SETADA 
	##########################################################################################################
		function _token($tabela,$coluna,$type="all"){
			$tk 					=	_crypt($type);
			$setToken				= 	new MySQL();
	 		$setToken->set_table($tabela);
			$setToken->set_where($coluna.'="'.$tk.'"');
			$setToken->select();
			if($setToken->_num_rows!=0){
				$tk = _crypt();
				_token($tabela,$coluna);
			}else{
				return $tk;
			}
		}

	##########################################################################################################
	#	CASO ESTEJA LOGADO DIRETAMENTE COM SERIALKEY
	##########################################################################################################
		function verifyUserLogin($return = false) {
			if (ws::urlPath(3, false)) {
				$keyAccess = ws::getTokenRest(ws::urlPath(3, false), false);
			} elseif (ws::urlPath(2, false)) {
				$keyAccess = ws::getTokenRest(ws::urlPath(2, false), false);
			}else{
				$keyAccess = false;
			}
			$log_session = new session();
			if ((SECURE == TRUE && $keyAccess == false) && ($log_session->verifyLogin() != true)) {
				$log_session->finish();
				if ($return == true) {
					return false;
				} else {
				echo '<script>
							document.cookie.split(";").forEach(function(c) {document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/");}); 
							if(window.location.pathname=="/admin/"){
								window.top.location.reload();
							}else{
								window.top.location = "/admin/";
							}
					</script>';
					exit;
				}
			} else {
				if ($return == true) {
					return true;
				}
			}
		}
			
	##########################################################################################################
	#	FUNÇÃO QUE APLICA O RASCUNHO DO ÍTEM
	##########################################################################################################
		function descartaRascunho($ws_id_ferramenta,$id_item,$apenasAplica=false){
			global $_conectMySQLi_;
			##########################################################################################################
			# EXCLUI O RASCUNHO DO ÍTEM
			##########################################################################################################
			if($apenasAplica==false){
				$get_draft				= new MySQL();
				$get_draft->set_table(PREFIX_TABLES."_model_item");
				$get_draft->set_where('ws_draft="1"');
				$get_draft->set_where('AND ws_id_draft="'.$id_item.'"');
				$get_draft->set_where('AND ws_id_ferramenta="'.$ws_id_ferramenta.'"');
				$get_draft->exclui();
			}
			##########################################################################################################
			# EXCLUI OS REGISTROS DAS IMAGENS DO ÍTEM ORIGINAL
			##########################################################################################################
				$ExclIMGs				= new MySQL();
				$ExclIMGs->set_table(PREFIX_TABLES."_model_img");
				$ExclIMGs->set_where('ws_draft="1"');
				$ExclIMGs->set_where('AND ws_id_ferramenta="'.$ws_id_ferramenta.'"');
				$ExclIMGs->set_where('AND id_item="'.$id_item.'"');						
				$ExclIMGs->exclui();

			##########################################################################################################
			# EXCLUI AS GALERIAS ORIGINAIS
			##########################################################################################################
				$ExclGal = new MySQL();
				$ExclGal->set_table(PREFIX_TABLES.'_model_gal');
				$ExclGal->set_where('ws_draft="1"');
				$ExclGal->set_where('AND ws_id_ferramenta="'.$ws_id_ferramenta.'"');
				$ExclGal->set_where('AND id_item="'.$id_item.'"');
				$ExclGal->exclui();
			##########################################################################################################
			# EXCLUI AS IMAGENS DAS GALERIAS ORIGINAIS
			##########################################################################################################
				$ExclGal = new MySQL();
				$ExclGal->set_table(PREFIX_TABLES.'_model_img_gal');
				$ExclGal->set_where('ws_draft="1" AND ws_id_ferramenta="'.$ws_id_ferramenta.'" AND id_item="'.$id_item.'"');
				$ExclGal->exclui();

			##########################################################################################################
			# EXCLUI OS REGISTROS DOS ARQUIVOS DO ÍTEM ORIGINAL
			##########################################################################################################
				$ExclFiles				= new MySQL();
				$ExclFiles->set_table(PREFIX_TABLES."_model_files");
				$ExclFiles->set_where('ws_draft="1"');
				$ExclFiles->set_where('AND ws_id_ferramenta="'.$ws_id_ferramenta.'"');
				$ExclFiles->set_where('AND id_item="'.$id_item.'"');
				$ExclFiles->exclui();

			##########################################################################################################
			# EXCLUI OS REGISTROS DOS RELACIONAMENTOS ORIGINAIS
			##########################################################################################################
				$ExclLink				= new MySQL();
				$ExclLink->set_table(PREFIX_TABLES."_model_link_prod_cat");
				$ExclLink->set_where(' ws_draft="1" ');
				$ExclLink->set_where('AND ws_id_ferramenta="'.$ws_id_ferramenta.'"');
				$ExclLink->set_where('AND id_item="'.$id_item.'"');
				$ExclLink->exclui();
			##########################################################################################################
			# EXCLUI OS REGISTROS DOS RELACIONAMENTOS ORIGINAIS
			##########################################################################################################
				$Set_Link				= new MySQL();
				$Set_Link->set_table(PREFIX_TABLES.'ws_link_itens');
				$Set_Link->set_where('ws_draft="1" AND ws_id_draft="'.$id_item.'" AND id_item="'.$id_item.'"');
				$Set_Link->exclui();

			##########################################################################################################
				criaRascunho($ws_id_ferramenta,$id_item,$apenasAplica);
				return true;
		}


	##########################################################################################################
	#	FUNÇÃO QUE APLICA O RASCUNHO DO ÍTEM
	##########################################################################################################
		function aplicaRascunho($ws_id_ferramenta,$id_item,$apenasAplica=false){
				global $_conectMySQLi_;
				if($apenasAplica==true){goto apenasAplica;}

				##########################################################################################################
				# SEPARA OS CAMPOS UTILIZADOS NA FERRAMENTA
				##########################################################################################################
					$campos							= new MySQL();
					$campos->set_table(PREFIX_TABLES.'_model_campos');
					$campos->set_order(	"posicao","ASC");
					$campos->set_where(	'ws_id_ferramenta="'.$ws_id_ferramenta.'"');
					$campos->select();

				##########################################################################################################
				# SELECIONA O RASCUNHO A SER APLICADO
				##########################################################################################################
					$get_draft				= new MySQL();
					$get_draft->set_table(PREFIX_TABLES."_model_item");
					$get_draft->set_where('ws_draft="1"');
					$get_draft->set_where('AND ws_id_draft="'.$id_item.'"');
					$get_draft->set_where('AND ws_id_ferramenta="'.$ws_id_ferramenta.'"');
					$get_draft->select();
					if($get_draft->_num_rows==0){
						die('Não existe rascunho cadastrado deste ítem');
					}
					$rascunho = $get_draft->fetch_array[0];
				##########################################################################################################
				# ABRE OS DADOS DO ÍTEM A SER ALTERADO
				##########################################################################################################
					$Set_Item				= new MySQL();
					$Set_Item->set_table(PREFIX_TABLES.'_model_item');
					$Set_Item->set_where(PREFIX_TABLES.'_model_item.id="'.$id_item.'"');

				##########################################################################################################
				# ADICIONA OS REGISTROS NOS CAMPOS ADICIONADOS DA FERRAMENTA
				##########################################################################################################
					foreach ($campos->fetch_array as $value) {
						if($value['coluna_mysql']!=""){
							$rascunhoSave = mysqli_real_escape_string($_conectMySQLi_,urldecode($rascunho[$value['coluna_mysql']]));
							$Set_Item->set_update($value['coluna_mysql'], $rascunhoSave);
						}
					}
					if($Set_Item->salvar()){
						apenasAplica:
						##########################################################################################################
						# EXCLUI O RASCUNHO DO ÍTEM
						##########################################################################################################
							$get_draft				= new MySQL();
							$get_draft->set_table(PREFIX_TABLES."_model_item");
							$get_draft->set_where('ws_draft="1"');
							$get_draft->set_where('AND ws_id_draft="'.$id_item.'"');
							$get_draft->set_where('AND ws_id_ferramenta="'.$ws_id_ferramenta.'"');
							$get_draft->exclui();

						##########################################################################################################
						# EXCLUI OS REGISTROS DAS IMAGENS DO ÍTEM ORIGINAL
						##########################################################################################################
							$ExclIMGs				= new MySQL();
							$ExclIMGs->set_table(PREFIX_TABLES."_model_img");
							$ExclIMGs->set_where('ws_draft="0"');
							$ExclIMGs->set_where('AND ws_id_draft="0"');
							$ExclIMGs->set_where('AND ws_id_ferramenta="'.$ws_id_ferramenta.'"');
							$ExclIMGs->set_where('AND id_item="'.$id_item.'"');						
							$ExclIMGs->exclui();
						##########################################################################################################
						# AGORA HABILITA COMO ORIGINAL OS REGISTROS DO RASCUNHO
						##########################################################################################################
							$Set_img				= new MySQL();
							$Set_img->set_table(PREFIX_TABLES.'_model_img');
							$Set_img->set_where('ws_draft="1" AND ws_id_draft="'.$id_item.'"');
							$Set_img->set_update("ws_draft","0");
							$Set_img->set_update("ws_id_draft","0");
							$Set_img->salvar();

						##########################################################################################################
						# EXCLUI AS GALERIAS ORIGINAIS
						##########################################################################################################
							$ExclGal = new MySQL();
							$ExclGal->set_table(PREFIX_TABLES.'_model_gal');
							$ExclGal->set_where('ws_draft="0"');
							$ExclGal->set_where('AND ws_id_draft="0"');
							$ExclGal->set_where('AND ws_id_ferramenta="'.$ws_id_ferramenta.'"');
							$ExclGal->set_where('AND id_item="'.$id_item.'"');
							if($apenasAplica==true){
								$ApenasAplicaQuery = new MySQL();
								$ApenasAplicaQuery->select("SELECT COUNT(*) as contador FROM ".PREFIX_TABLES."_model_gal where(ws_draft='1' AND ws_id_ferramenta='".$ws_id_ferramenta."' AND id_item='".$id_item."' AND ws_id_draft='".$id_item."')");
								$ExclGal->set_where('AND '.$ApenasAplicaQuery->obj[0]->contador.'>0');
							}				
							$ExclGal->exclui();

						##########################################################################################################
						# APLICANDO AS GALERIAS DE FOTOS
						##########################################################################################################
							$Set_img = new MySQL();
							$Set_img->set_table(PREFIX_TABLES.'_model_gal');
							$Set_img->set_where('ws_draft="1" AND ws_id_draft="'.$id_item.'"');
							$Set_img->set_update("ws_draft","0");
							$Set_img->set_update("ws_id_draft","0");
							$Set_img->salvar();

						##########################################################################################################
						# EXCLUI AS IMAGENS DAS GALERIAS ORIGINAIS
						##########################################################################################################
							$ExclGal = new MySQL();
							$ExclGal->set_table(PREFIX_TABLES.'_model_img_gal');
							$ExclGal->set_where('ws_draft="0" AND ws_id_draft="0" AND ws_id_ferramenta="'.$ws_id_ferramenta.'" AND id_item="'.$id_item.'"');
							if($apenasAplica==true){
								$ApenasAplicaQuery = new MySQL();
								$ApenasAplicaQuery->select("SELECT COUNT(*) as contador FROM ".PREFIX_TABLES."_model_img_gal where(ws_draft='1' AND ws_id_ferramenta='".$ws_id_ferramenta."' AND id_item='".$id_item."' AND ws_id_draft='".$id_item."')");
								$ExclGal->set_where('AND '.$ApenasAplicaQuery->obj[0]->contador.'>0');
							}
							$ExclGal->exclui();

						##########################################################################################################
						# APLICANDO AS IMAGENS NAS GALERIAS DE FOTOS
						##########################################################################################################
							$Set_img = new MySQL();
							$Set_img->set_table(PREFIX_TABLES.'_model_img_gal');
							$Set_img->set_where('ws_draft="1" AND ws_id_draft="'.$id_item.'"');
							$Set_img->set_update("ws_draft","0");
							$Set_img->set_update("ws_id_draft","0");
							$Set_img->salvar();

						##########################################################################################################
						# EXCLUI OS REGISTROS DOS ARQUIVOS DO ÍTEM ORIGINAL
						##########################################################################################################
							$ExclFiles				= new MySQL();
							$ExclFiles->set_table(PREFIX_TABLES."_model_files");
							$ExclFiles->set_where('ws_draft="0"');
							$ExclFiles->set_where('AND ws_id_draft="0"');
							$ExclFiles->set_where('AND ws_id_ferramenta="'.$ws_id_ferramenta.'"');
							$ExclFiles->set_where('AND id_item="'.$id_item.'"');
							if($apenasAplica==true){						
								$ApenasAplicaQuery = new MySQL();
								$ApenasAplicaQuery->select("SELECT COUNT(*) as contador FROM ".PREFIX_TABLES."_model_files where(ws_draft='1' AND ws_id_ferramenta='".$ws_id_ferramenta."' AND id_item='".$id_item."' AND ws_id_draft='".$id_item."')");
								$ExclFiles->set_where('AND '.$ApenasAplicaQuery->obj[0]->contador.'>0');
							}
							$ExclFiles->exclui();

						##########################################################################################################
						# AGORA HABILITA COMO ORIGINAL OS REGISTROS DO RASCUNHO
						##########################################################################################################
							$Set_files				= new MySQL();
							$Set_files->set_table(PREFIX_TABLES.'_model_files');
							$Set_files->set_where('ws_draft="1" AND ws_id_draft="'.$id_item.'"');
							$Set_files->set_update("ws_draft","0");
							$Set_files->set_update("ws_id_draft","0");
							$Set_files->salvar();

						##########################################################################################################
						# EXCLUI OS REGISTROS DOS RELACIONAMENTOS ORIGINAIS
						##########################################################################################################
							$ExclLink				= new MySQL();
							$ExclLink->set_table(PREFIX_TABLES."_model_link_prod_cat");
							$ExclLink->set_where(' ws_draft="0" ');
							$ExclLink->set_where('AND ws_id_draft="0" ');
							$ExclLink->set_where('AND ws_id_ferramenta="'.$ws_id_ferramenta.'"');
							$ExclLink->set_where('AND id_item="'.$id_item.'"');
							$ExclLink->exclui();

						##########################################################################################################
						# AGORA HABILITA COMO ORIGINAL OS RASCUNHOS
						##########################################################################################################
							$Set_Link				= new MySQL();
							$Set_Link->set_table(PREFIX_TABLES.'_model_link_prod_cat');
							$Set_Link->set_where('ws_draft="1" AND ws_id_draft="'.$id_item.'"');
							$Set_Link->set_where('AND id_item="'.$id_item.'"');
							$Set_Link->set_update("ws_draft","0");
							$Set_Link->set_update("ws_id_draft","0");
							$Set_Link->salvar();
							
						##########################################################################################################
						# EXCLUI OS REGISTROS DOS RELACIONAMENTOS ORIGINAIS
						##########################################################################################################
							$ExclLink				= new MySQL();
							$ExclLink->set_table(PREFIX_TABLES."ws_link_itens");
							$ExclLink->set_where(' ws_draft="0"  AND ws_id_draft="0"  AND id_item="'.$id_item.'"');
							$ExclLink->exclui();

						##########################################################################################################
						# AGORA HABILITA COMO ORIGINAL OS RASCUNHOS
						##########################################################################################################
							$Set_Link				= new MySQL();
							$Set_Link->set_table(PREFIX_TABLES.'ws_link_itens');
							$Set_Link->set_where('ws_draft="1" AND ws_id_draft="'.$id_item.'" AND id_item="'.$id_item.'"');
							$Set_Link->set_update("ws_draft","0");
							$Set_Link->set_update("ws_id_draft","0");
							$Set_Link->salvar();

						##########################################################################################################
						# END
						##########################################################################################################
					};
				return true;
		}

	##########################################################################################################
	#	FUNÇÃO QUE CRIA O RASCUNHO DO ÍTEM
	##########################################################################################################
		function criaRascunho($ws_id_ferramenta=0,$id_item=null, $imagens=false){
			global $_conectMySQLi_;
			##########################################################################################################
			# VERIFICA SE JÁ TEM RASCUNHO
			##########################################################################################################
				$draft				= new MySQL();
				$draft->set_table(PREFIX_TABLES."_model_item");
				$draft->set_where('ws_draft="1"');
				$draft->set_where('AND ws_id_draft="'.$id_item.'"');
				$draft->select();
			##########################################################################################################
			# VERIFICA SE É´PARA GERAR APENAS RASCUNHOS DAS IMAGENS E ARQUIVOS INTERNOS
			##########################################################################################################
			if($imagens==true){goto imagens;}
			##########################################################################################################
			# CASO NÃO TENHA CRIA UM RASCUNHO, CLONAMOS O ORIGINAL PARA O RASCUNHO 
			##########################################################################################################
				if($draft->_num_rows==0){
					##########################################################################################################
					# SEPARA O ÍTEM ORIGINAL
					##########################################################################################################
						$get_produto	= new MySQL();
						$get_produto->set_table(PREFIX_TABLES.'_model_item');
						$get_produto->set_where(PREFIX_TABLES.'_model_item.id="'.$id_item.'"');
						$get_produto->select();
					##########################################################################################################
					# INICIA A CÓPIA
					##########################################################################################################
						$Set_Draft	= new MySQL();
						$Set_Draft->set_table(PREFIX_TABLES.'_model_item');
						$Set_Draft->set_insert('ws_draft','1');
						$Set_Draft->set_insert('ws_id_draft',$id_item);
						$Set_Draft->set_insert('ws_id_ferramenta',$ws_id_ferramenta);
						$Set_Draft->set_insert('token', $get_produto->fetch_array[0]['token']);
					##########################################################################################################
					# SEPARAMOS OS CAMPOS DESTE ÍTEM
					##########################################################################################################
						$campos							= new MySQL();
						$campos->set_table(PREFIX_TABLES.'_model_campos');
						$campos->set_order(	"posicao","ASC");
						$campos->set_where(	'ws_id_ferramenta="'.$ws_id_ferramenta.'"');
						$campos->select();
						foreach ($campos->fetch_array as $value) {
							if($value['coluna_mysql']!=""){
								$Set_Draft->set_insert($value['coluna_mysql'], mysqli_real_escape_string($_conectMySQLi_,urldecode($get_produto->fetch_array[0][$value['coluna_mysql']])));
							}
						}
						$Set_Draft->insert();

					imagens:
					##########################################################################################################
					# GERA RASCUNHO DAS IMAGENS DIRETAS
					##########################################################################################################
						$getIMGs				= new MySQL();
						$getIMGs->set_table(PREFIX_TABLES."_model_img");
						$getIMGs->set_where('ws_draft="0"');
						$getIMGs->set_where('AND ws_id_draft="0"');
						$getIMGs->set_where('AND ws_id_ferramenta="'.$ws_id_ferramenta.'"');
						$getIMGs->set_where('AND id_item="'.$id_item.'"');
						$getIMGs->select();
						$draftIMG				= new MySQL();
						$draftIMG->set_table(PREFIX_TABLES."_model_img");
						$draftIMG->set_where('ws_draft="1"');
						$draftIMG->set_where('AND ws_id_ferramenta="'.$ws_id_ferramenta.'"');
						$draftIMG->set_where('AND ws_id_draft="'.$id_item.'"');
						$draftIMG->select();
						//CASO NÃO TENHA RASCUNHO AINDA
						if($draftIMG->_num_rows<1 && $getIMGs->_num_rows>0){
							foreach ($getIMGs->fetch_array as $valueImg) {
								$Set_DraftIMG	= new MySQL();
								$Set_DraftIMG->set_table(PREFIX_TABLES.'_model_img');
								$Set_DraftIMG->set_insert('ws_draft',			'1');
								$Set_DraftIMG->set_insert('ws_id_draft',		$id_item);
								$Set_DraftIMG->set_insert('ws_type',			$valueImg['ws_type']);
								$Set_DraftIMG->set_insert('avatar',				$valueImg['avatar']);
								$Set_DraftIMG->set_insert('ws_id_ferramenta',	$ws_id_ferramenta);
								$Set_DraftIMG->set_insert('ws_tool_item',		$valueImg['ws_tool_item']);
								$Set_DraftIMG->set_insert('id_item',			$id_item);
								$Set_DraftIMG->set_insert('id_cat',				$valueImg['id_cat']);
								$Set_DraftIMG->set_insert('ws_nivel',			$valueImg['ws_nivel']);
								$Set_DraftIMG->set_insert('posicao',			$valueImg['posicao']);
								$Set_DraftIMG->set_insert('painel',				$valueImg['painel']);
								$Set_DraftIMG->set_insert('titulo',				$valueImg['titulo']);
								$Set_DraftIMG->set_insert('url',				$valueImg['url']);
								$Set_DraftIMG->set_insert('texto',				$valueImg['texto']);
								$Set_DraftIMG->set_insert('imagem',				$valueImg['imagem']);
								$Set_DraftIMG->set_insert('filename',			$valueImg['filename']);
								$Set_DraftIMG->set_insert('token',				$valueImg['token']);
								$Set_DraftIMG->insert();
							}
						}
					##########################################################################################################
					# GERA RASCUNHO DAS GALERIAS E SUAS IMAGENS
					##########################################################################################################
						##########################################################################################################
						# GERA RASCUNHO DAS GALERIAS E SUAS IMAGENS
						##########################################################################################################
							$draftGals				= new MySQL();
							$draftGals->set_table(PREFIX_TABLES."_model_gal");
							$draftGals->set_where('ws_draft="1"');
							$draftGals->set_where('AND ws_id_ferramenta="'.$ws_id_ferramenta.'"');
							$draftGals->set_where('AND ws_id_draft="'.$id_item.'"');
							$draftGals->select();
							$getGALS				= new MySQL();
							$getGALS->set_table(PREFIX_TABLES."_model_gal");
							$getGALS->set_where('ws_draft="0"');
							$getGALS->set_where('AND ws_id_draft="0"');
							$getGALS->set_where('AND ws_id_ferramenta="'.$ws_id_ferramenta.'"');
							$getGALS->set_where('AND id_item="'.$id_item.'"');
							$getGALS->select();

							if($draftGals->_num_rows<1 && $getGALS->_num_rows>0){
								foreach ($getGALS->fetch_array as $valueGal){
									##########################################################################################################
									# CLONA A GALERIA DO LOOP
									##########################################################################################################
										$Set_DraftGal	= new MySQL();
										$Set_DraftGal->set_table(PREFIX_TABLES.'_model_gal');
										$Set_DraftGal->set_insert('ws_id_draft',		$id_item);
										$Set_DraftGal->set_insert('ws_draft',			'1');
										$Set_DraftGal->set_insert('ws_type',			$valueGal['ws_type']);
										$Set_DraftGal->set_insert('ws_id_ferramenta',	$ws_id_ferramenta);
										$Set_DraftGal->set_insert('ws_tool_id',			$id_item);
										$Set_DraftGal->set_insert('ws_tool_item',		$id_item);
										$Set_DraftGal->set_insert('ws_nivel',			$valueGal['ws_nivel']);
										$Set_DraftGal->set_insert('id_cat',				$valueGal['id_cat']);
										$Set_DraftGal->set_insert('id_item',			$id_item);
										$Set_DraftGal->set_insert('posicao',			$valueGal['posicao']);
										$Set_DraftGal->set_insert('avatar',				$valueGal['avatar']);
										$Set_DraftGal->set_insert('filename',			$valueGal['filename']);
										$Set_DraftGal->set_insert('titulo',				$valueGal['titulo']);
										$Set_DraftGal->set_insert('token',				$valueGal['token']);
										$Set_DraftGal->set_insert('texto',				$valueGal['texto']);
										$Set_DraftGal->set_insert('url	',				$valueGal['url']);
										$Set_DraftGal->insert();
									##########################################################################################################
									# PEGA O ID DA GALERIA ADICIONADA
									##########################################################################################################
										$CloneGal	= new MySQL();
										$CloneGal->set_table(PREFIX_TABLES.'_model_gal');
										$CloneGal->set_order('id','DESC');
										$CloneGal->set_colum('id');
										$CloneGal->set_limit(1);
										$CloneGal->select();
										$idCloneGal = $CloneGal->fetch_array[0]['id'];
									##########################################################################################################
									# SELECIONA AS IMAGENS DESSA GALERIA
									##########################################################################################################
										$imgGaleria				= new MySQL();
										$imgGaleria->set_table(PREFIX_TABLES."_model_img_gal");
										$imgGaleria->set_where('id_galeria="'.$value['id'].'"');
										$imgGaleria->select();							
									##########################################################################################################
									# AGORA CLONA OS REGISTROS DAS IMAGENS DA GALERIA ORIGINAL COM A REFERENCIA DESSA GALERIA CLONADA
									##########################################################################################################
										foreach ($imgGaleria->fetch_array as $imgVal){
											$Set_Draft_img_Gal	= new MySQL();
											$Set_Draft_img_Gal->set_table(PREFIX_TABLES.'_model_img_gal');
											$Set_Draft_img_Gal->set_insert('ws_draft',			'1');
											$Set_Draft_img_Gal->set_insert('ws_id_draft',		$id_item);
											$Set_Draft_img_Gal->set_insert('id_galeria',		$idCloneGal);//ID DA GALERIA CLONADA
											$Set_Draft_img_Gal->set_insert('ws_type',			$imgVal['ws_type']);
											$Set_Draft_img_Gal->set_insert('ws_id_ferramenta',	$ws_id_ferramenta);
											$Set_Draft_img_Gal->set_insert('ws_tool_id',		$imgVal['ws_tool_id']);
											$Set_Draft_img_Gal->set_insert('ws_tool_item',		$imgVal['ws_tool_item']);
											$Set_Draft_img_Gal->set_insert('id_item',			$id_item);
											$Set_Draft_img_Gal->set_insert('id_cat',			$imgVal['id_cat']);
											$Set_Draft_img_Gal->set_insert('posicao',			$imgVal['posicao']);
											$Set_Draft_img_Gal->set_insert('ws_nivel',			$imgVal['ws_nivel']);
											$Set_Draft_img_Gal->set_insert('titulo',			$imgVal['titulo']);
											$Set_Draft_img_Gal->set_insert('url',				$imgVal['url']);
											$Set_Draft_img_Gal->set_insert('texto',				$imgVal['texto']);
											$Set_Draft_img_Gal->set_insert('imagem',			$imgVal['imagem']);
											$Set_Draft_img_Gal->set_insert('filename',			$imgVal['filename']);
											$Set_Draft_img_Gal->set_insert('file',				$imgVal['file']);
											$Set_Draft_img_Gal->set_insert('avatar',			$imgVal['avatar']);
											$Set_Draft_img_Gal->set_insert('token',				$imgVal['token']);
											$Set_Draft_img_Gal->insert();
										}
								}
							}
						##########################################################################################################
						# GERA RASCUNHO DOS ARQUIVOS
						##########################################################################################################
							$getFiles				= new MySQL();
							$getFiles->set_table(PREFIX_TABLES."_model_files");
							$getFiles->set_where('ws_draft="0"');
							$getFiles->set_where('AND ws_id_draft="0"');
							$getFiles->set_where('AND ws_id_ferramenta="'.$ws_id_ferramenta.'"');
							$getFiles->set_where('AND id_item="'.$id_item.'"');
							$getFiles->select();

							$draftFiles				= new MySQL();
							$draftFiles->set_table(PREFIX_TABLES."_model_files");
							$draftFiles->set_where('ws_draft="1"');
							$draftFiles->set_where('AND ws_id_ferramenta="'.$ws_id_ferramenta.'"');
							$draftFiles->set_where('AND ws_id_draft="'.$id_item.'"');
							$draftFiles->set_where('AND id_item="'.$id_item.'"');
							$draftFiles->select();
							//CASO NÃO TENHA RASCUNHO AINDA E TENHA ARQUIVOS NO ORIGINAL
							if($draftFiles->_num_rows<1 && $getFiles->_num_rows>0){
								foreach ($getFiles->fetch_array as $valueFile) {
									$Set_DraftFiles	= new MySQL();
									$Set_DraftFiles->set_table(PREFIX_TABLES.'_model_files');
									$Set_DraftFiles->set_insert('ws_id_draft',		$id_item);
									$Set_DraftFiles->set_insert('ws_draft',			'1');
									$Set_DraftFiles->set_insert('ws_type',			$valueFile['ws_type']);
									$Set_DraftFiles->set_insert('ws_id_ferramenta',	$ws_id_ferramenta);
									$Set_DraftFiles->set_insert('ws_tool_id',		$valueFile['ws_tool_id']);
									$Set_DraftFiles->set_insert('ws_tool_item',		$valueFile['ws_tool_item']);
									$Set_DraftFiles->set_insert('id_item',			$id_item);
									$Set_DraftFiles->set_insert('id_cat',			$valueFile['id_cat']);
									$Set_DraftFiles->set_insert('ws_nivel',			$valueFile['ws_nivel']);
									$Set_DraftFiles->set_insert('posicao',			$valueFile['posicao']);
									$Set_DraftFiles->set_insert('uploaded',			$valueFile['uploaded']);
									$Set_DraftFiles->set_insert('titulo',			$valueFile['titulo']);
									$Set_DraftFiles->set_insert('painel',			$valueFile['painel']);
									$Set_DraftFiles->set_insert('url',				$valueFile['url']);
									$Set_DraftFiles->set_insert('texto',			$valueFile['texto']);
									$Set_DraftFiles->set_insert('file',				$valueFile['file']);
									$Set_DraftFiles->set_insert('filename',			$valueFile['filename']);
									$Set_DraftFiles->set_insert('token',			$valueFile['token']);
									$Set_DraftFiles->set_insert('size_file',		$valueFile['size_file']);
									$Set_DraftFiles->set_insert('download',			$valueFile['download']);
									$Set_DraftFiles->insert();
								}
							}
					##########################################################################################################
					# GERA RASCUNHO DO RELACIONAMENTO DE CATEGORIAS
					##########################################################################################################
						$getCat				= new MySQL();
						$getCat->set_table(PREFIX_TABLES."_model_link_prod_cat");
						$getCat->set_where('ws_draft="0"');
						$getCat->set_where('AND ws_id_draft="0"');
						$getCat->set_where('AND ws_id_ferramenta="'.$ws_id_ferramenta.'"');
						$getCat->set_where('AND id_item="'.$id_item.'"');
						$getCat->select();

						$draftLink				= new MySQL();
						$draftLink->set_table(PREFIX_TABLES."_model_link_prod_cat");
						$draftLink->set_where('ws_draft="1"');
						$draftLink->set_where('AND ws_id_ferramenta="'.$ws_id_ferramenta.'"');
						$draftLink->set_where('AND ws_id_draft="'.$id_item.'"');
						$draftLink->set_where('AND id_item="'.$id_item.'"');
						$draftLink->select();
						//CASO NÃO TENHA RASCUNHO E TENHA CATEGORIAS NO ORIGINAL
						if($draftLink->_num_rows<1 && $getCat->_num_rows>0){
							foreach ($getCat->fetch_array as $valueCat) {
								$Set_Cat	= new MySQL();
								$Set_Cat->set_table(PREFIX_TABLES.'_model_link_prod_cat');
								$Set_Cat->set_insert('ws_id_draft',		$id_item);
								$Set_Cat->set_insert('ws_draft',		'1');
								$Set_Cat->set_insert('id_cat',			$valueCat['id_cat']);
								$Set_Cat->set_insert('ws_id_ferramenta',$ws_id_ferramenta);
								$Set_Cat->set_insert('id_item',		$valueCat['id_item']);
								$Set_Cat->set_insert('ws_tool_id',		$valueCat['ws_tool_id']);
								$Set_Cat->set_insert('ws_tool_item',	$id_item);
								$Set_Cat->set_insert('ws_nivel',		$valueCat['ws_nivel']);
								$Set_Cat->insert();
							}
						}
					##########################################################################################################
					# GERA RASCUNHO DO RELACIONAMENTO ENTRE ITENS
					##########################################################################################################
						$getLinkProd				= new MySQL();
						$getLinkProd->set_table(PREFIX_TABLES."ws_link_itens");
						$getLinkProd->set_where('ws_draft="0"');
						$getLinkProd->set_where('AND ws_id_draft="0"');
						$getLinkProd->set_where('AND id_item="'.$id_item.'"');
						$getLinkProd->select();
						$draftLinkProd				= new MySQL();
						$draftLinkProd->set_table(PREFIX_TABLES."ws_link_itens");
						$draftLinkProd->set_where('ws_draft="1"');
						$draftLinkProd->set_where('AND ws_id_draft="'.$id_item.'"');
						$draftLinkProd->set_where('AND id_item="'.$id_item.'"');
						$draftLinkProd->select();
						//CASO NÃO TENHA RASCUNHO E TENHA CATEGORIAS NO ORIGINAL
						if($draftLinkProd->_num_rows<1 && $getLinkProd->_num_rows>0){
							foreach ($getLinkProd->fetch_array as $valueCat) {
								$Set_Cat	= new MySQL();
								$Set_Cat->set_table(PREFIX_TABLES.'ws_link_itens');
								$Set_Cat->set_insert('ws_id_draft',		$id_item);
								$Set_Cat->set_insert('ws_draft',		'1');
								$Set_Cat->set_insert('id_item',			$valueCat['id_item']);
								$Set_Cat->set_insert('id_item_link',	$valueCat['id_item_link']);
								$Set_Cat->set_insert('id_cat_link',		$valueCat['id_cat_link']);
								$Set_Cat->insert();
							}
						}

					##########################################################################################################
					# GERA RASCUNHO DOS ARQUIVOS DIRETOS
					##########################################################################################################

						$getFILES 				= new MySQL();
						$getFILES->set_table(PREFIX_TABLES."_model_files");
						$getFILES->set_where('ws_draft="0"');
						$getFILES->set_where('AND ws_id_draft="0"');
						$getFILES->set_where('AND ws_id_ferramenta="'.$ws_id_ferramenta.'"');
						$getFILES->set_where('AND id_item="'.$id_item.'"');
						$getFILES->select();

						$draftFILES				= new MySQL();
						$draftFILES->set_table(PREFIX_TABLES."_model_files");
						$draftFILES->set_where('ws_draft="1"');
						$draftFILES->set_where('AND ws_id_ferramenta="'.$ws_id_ferramenta.'"');
						$draftFILES->set_where('AND ws_id_draft="'.$id_item.'"');
						$draftFILES->select();

						//CASO NÃO TENHA RASCUNHO AINDA
						if($draftFILES->_num_rows<1 && $getFILES->_num_rows>0){
							foreach ($getFILES->fetch_array as $valueFile) {
								$Set_DraftIMG	= new MySQL();
								$Set_DraftIMG->set_table(PREFIX_TABLES.'_model_files');
								$Set_DraftIMG->set_insert('ws_draft',			'1');
								$Set_DraftIMG->set_insert('ws_id_draft',		$id_item);
								$Set_DraftIMG->set_insert('id_item',			$id_item);
								$Set_DraftIMG->set_insert('ws_id_ferramenta',	$ws_id_ferramenta);
								$Set_DraftIMG->set_insert('ws_type',			$valueFile['ws_type']);
								$Set_DraftIMG->set_insert('ws_tool_id',			$valueFile['ws_tool_id']);
								$Set_DraftIMG->set_insert('ws_tool_item',		$valueFile['ws_tool_item']);
								$Set_DraftIMG->set_insert('id_cat',				$valueFile['id_cat']);
								$Set_DraftIMG->set_insert('ws_nivel',			$valueFile['ws_nivel']);
								$Set_DraftIMG->set_insert('posicao',			$valueFile['posicao']);
								$Set_DraftIMG->set_insert('titulo',				$valueFile['titulo']);
								$Set_DraftIMG->set_insert('painel',				$valueFile['painel']);
								$Set_DraftIMG->set_insert('url',				$valueFile['url']);
								$Set_DraftIMG->set_insert('texto',				$valueFile['texto']);
								$Set_DraftIMG->set_insert('file',				$valueFile['file']);
								$Set_DraftIMG->set_insert('filename',			$valueFile['filename']);
								$Set_DraftIMG->set_insert('token',				$valueFile['token']);
								$Set_DraftIMG->set_insert('size_file',			$valueFile['size_file']);
								$Set_DraftIMG->set_insert('download',			$valueFile['download']);
								$Set_DraftIMG->set_insert('uploaded',			$valueFile['uploaded']);
								$Set_DraftIMG->insert();
							}
						}

					############################################### END ######################################################
					return true;
				}

			##########################################################################################################
			# FIM (apenas se ñ tiover rascunho do ítem)
			##########################################################################################################
		}

	##########################################################################################################
	# FUNÇÃO QUE CRIA O JSON COM A LISTA DOS PLUGINS INSTALADOS
	##########################################################################################################
		function refreshJsonPluginsList(){
			$setupdata 	= new MySQL();
			$setupdata->set_table(PREFIX_TABLES.'setupdata');
			$setupdata->set_order('id','DESC');
			$setupdata->set_limit(1);
			$setupdata->debug(0);
			$setupdata->select();
			$setupdata = $setupdata->fetch_array[0];
			//################################################################################################
			$_path_plugin_ = INCLUDE_PATH.'website/'.$setupdata['url_plugin']; 
			$json_plugins = array();
			if(is_dir($_path_plugin_)){
				$dh = opendir($_path_plugin_);
				while($diretorio = readdir($dh)){
					if($diretorio != '..' && $diretorio != '.' && $diretorio != '.htaccess' ){
						$phpConfig 	= $_path_plugin_.'/'.$diretorio.'/plugin.config.php';
						if(file_exists($phpConfig)){
							ob_start();
							@include($phpConfig);
							$jsonRanderizado=ob_get_clean();
							$contents=$plugin;
						}
						$itemArray = Array();
						if(file_exists($_path_plugin_.'/'.$diretorio.'/active')){
							@$contents->{'active'}="yes";
						}else{
							@$contents->{'active'}="no";
						}
						$contents->{'realPath'}=str_replace(INCLUDE_PATH.'website/','',$_path_plugin_).'/'.$diretorio;
						//################################################################################################
						$json_plugins[] = $contents;
					}
				}
			}
			file_put_contents(INCLUDE_PATH.'admin/app/templates/json/ws-plugin-list.json', json_encode($json_plugins,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
		}

	##########################################################################################################
	# INSTALA UMA FERRAMENTA EXTERNA
	##########################################################################################################
		function installExternalTool($webtool=null,$grupoPai=null){

			####################################################################
			# FERRAMENTSA JÁ ESTA VINDO EM BASE64
			####################################################################
			if(isset($_REQUEST['base64'])){ 
				####################################################################
				# ADICIONAMOS O PREFIXO DAS TABELAS
				####################################################################
				 $content =	str_replace('{PREFIX_TABLES}',PREFIX_TABLES, base64_decode($_REQUEST['base64']));
				goto processa;
			}

			####################################################################
			# AQUI É CASO ESTEJAMOS DUPLICANDO UMA FERRAMENTA
			####################################################################
			if($grupoPai==null){	echo "Insira um grupo pai";			exit;}
			
			####################################################################
			# CASO SEJA UPLOAD DE ARQUIVO, VERIFICAMOS A EXTENSÃO
			####################################################################
			if($webtool==null){		echo "Insira um arquivo na função";	exit;}
			$pathinfo 	= pathinfo($webtool);
			$ext 		= $pathinfo['extension'];
			if($ext=="ws"){
				include(INCLUDE_PATH.'admin/app/lib/class-base2n.php');
				$binary 	= new Base2n(6);
				$content	= $binary->decode(file_get_contents($webtool));
			}elseif($ext=="json"){
				$content		=	file_get_contents($webtool);
			}

			####################################################################
			# INICIAMOS O PROCESSO DE ANALISE
			####################################################################
			processa:
			if(isset($_REQUEST['prefix'])){$prefix= $_REQUEST['prefix'];}else{$prefix = "";}
			if(isset($_REQUEST['base64'])){
				$getAll				=	array(json_decode($content,true));
			}else{
				$getAll				=	json_decode($content,true);
			}




			#####################################################################################
			# 		ARRAY QUE VAI RECEBER OS ERROS
			#####################################################################################
				$_ERROS 		= array("sistema"=>array(),"ferramenta"=>array());
			#####################################################################################

				#####################################################################################
				# 		GUARDAMOS AS COLUNAS DAS FERRAMENTAS NA VARIÁVEL $_COLUNA_FERRAMENTA
				#####################################################################################
				$_COLUNAS_SISTEMA 		= array();

				#####################################################################################
				# 		VERIFICAMOS SE AS COLUNAS DA FERRAMENTA EXISTEM NAS TABELAS DO SISTEMA
				#####################################################################################
					$campos			= new MySQL();
					$campos->set_table(PREFIX_TABLES.'_model_campos');
					$campos->show_columns();

					foreach ($campos->fetch_array as $value) {
						$_COLUNAS_SISTEMA[] = $value['Field'];
					 };
				#####################################################################################
				# 		MONTAMOS UMA VARIÁVEL PARA RECEBER POSSÍVEIS COLUNAS INEXISTENTES
				#####################################################################################
				$_COLUNAS_INEXISTENTE 	= array();

				#####################################################################################
				# 		GUARDAMOS AS COLUNAS DAS FERRAMENTAS NA VARIÁVEL $_COLUNA_FERRAMENTA
				#####################################################################################
				$_COLUNA_FERRAMENTA 	= $getAll[0]['colunasCampos'];

				#####################################################################################
				# 		VERIFICAMOS SE AS COLUNAS DA FERRAMENTA EXISTEM NAS TABELAS DO SISTEMA
				#####################################################################################
				foreach ($_COLUNA_FERRAMENTA as $value) {
					if(!in_array($value, $_COLUNAS_SISTEMA)){
						$_ERROS['ferramenta'][] = $value;
					}
				}

				if(count($_ERROS['ferramenta'])>=1){
					return json_encode(
						array(
							'status'=>'falha',
							'content'=>'Ops! Verificamos que esta ferramenta necessita de algumas colunas na tabela <strong>'.PREFIX_TABLES.'_model_campos</strong>:',
							'colunas'=>implode($_ERROS['ferramenta']," , "),
						)
					);
				}


				#####################################################################################
				# 		GUARDAMOS AS COLUNAS DAS FERRAMENTAS NA VARIÁVEL $_COLUNA_FERRAMENTA
				#####################################################################################
				$_COLUNAS_SISTEMA 		= array();

				#####################################################################################

				$ferramentas			= new MySQL();
				$ferramentas->set_table(PREFIX_TABLES.'ws_ferramentas');
				$ferramentas->show_columns();

				foreach ($ferramentas->fetch_array as $value) {
					$_COLUNAS_SISTEMA[] = $value['Field'];
				 };

				#####################################################################################
				# 		MONTAMOS UMA VARIÁVEL PARA RECEBER POSSÍVEIS COLUNAS INEXISTENTES
				#####################################################################################
				$_COLUNAS_INEXISTENTE 	= array();

				#####################################################################################
				# 		GUARDAMOS AS COLUNAS DAS FERRAMENTAS NA VARIÁVEL $_COLUNA_FERRAMENTA
				#####################################################################################
				$_COLUNA_FERRAMENTA 	= $getAll[0]['colunasTool'];

			#####################################################################################
			# 		VERIFICAMOS SE AS COLUNAS DA FERRAMENTA EXISTEM NAS TABELAS DO SISTEMA
			#####################################################################################
				foreach ($_COLUNA_FERRAMENTA as $value) {
					if(!in_array($value, $_COLUNAS_SISTEMA)){
						$_ERROS['sistema'][] = $value;
					}
				}

			#####################################################################################
			# 		VERIFICAMOS SE AS COLUNAS DO SISTEMA EXISTEM NAS TABELAS DA FERRAMENTA
			#####################################################################################
				foreach ($_COLUNAS_SISTEMA as $value) {
					if(!in_array($value, $_COLUNA_FERRAMENTA)){
						$_ERROS['ferramenta'][] = $value;
					}
				}
				if(count($_ERROS['sistema'])>=1){
					return json_encode(
						array(
							'status'=>'falha',
							'content'=>'Ops! Verificamos que esta ferramenta necessita de algumas colunas na tabela <strong>'.PREFIX_TABLES.'ws_ferramentas</strong>:',
							'colunas'=>implode($_ERROS['sistema']," , "),
						)
					);
				}

			foreach ($getAll as $newTool){
				$token 				= _token(PREFIX_TABLES.'ws_ferramentas','token');
				$colunasListItens 	=	explode(',',$newTool['det_listagem_item']);
				$colunasListPrefix 	= 	Array();
				foreach ($colunasListItens as $val){$colunasListPrefix[] = $prefix.$val;};
				$colunasListItens 	=	implode(array_map("duplicateColumName",$colunasListPrefix),',');
				$ferramenta 		=	str_replace(
													array(
														'{{prefix}}',
														'{{token}}',
														'{{grupo_pai}}',
														'{{det_listagem_item}}',
														'{{slugTool}}',
														'{{nameTool}}'
													),
													array(
														$prefix,
														$token,
														$grupoPai,
														$colunasListItens,
														$_REQUEST['slugTool'],
														$_REQUEST['nameTool']
													),$newTool['tool']);
				$campos 			=   $newTool['colunas'];
				$insert = new MySQL();
				if($insert->select($ferramenta)){
					$Ferramenta_atual 					= new MySQL();
					$Ferramenta_atual->set_table(PREFIX_TABLES.'ws_ferramentas');
					$Ferramenta_atual->set_where('token="'.$token.'"');
					$Ferramenta_atual->select();
					$ws_id_ferramenta = $Ferramenta_atual->fetch_array[0]['id'];

					if(count($campos)>0){
						$AddColunaItem= new MySQL();
						$AddColunaItem->set_table(PREFIX_TABLES.'_model_item');
						foreach ($campos as $value) {
							if(isset($value['query'])){
								$token 			= _token(PREFIX_TABLES.'_model_campos','token');
								$coluna 		= duplicateColumName($prefix.$value['colum']);
								$value['query'] = str_replace(
								 							array('{{ws_id_ferramenta}}','{{name}}','{{id_campo}}','{{coluna_mysql}}','{{token}}'), 
								 							array($ws_id_ferramenta,$coluna,$coluna,$coluna,$token),
								 							$value['query']);
								 $InsertCampo 	= new MySQL();
								 $InsertCampo   ->select($value['query']);
								 $AddColunaItem->set_colum(array($coluna,$value['insert']));
							}
						}
						$AddColunaItem->add_column();
					}
				}
				return json_encode(
						array(
							'status'=>'sucesso',
							'content'=>'Ferramenta instalada com sucesso',
							'colunas'=>null,
						)
					);
				};
		}

	##########################################################################################################
	# VERIFICA SE A COLUNA DA TABELA ÍTEM EXISTE, SE EXISTE CRIA OUTRO NOME NOME_1 NOME_2 NOME_3 
	##########################################################################################################
		function duplicateColumName($colunaVerificar){
			$i=2;
			$colunasAtuais = array();
			$D = new MySQL();
			$D->set_table(PREFIX_TABLES.'_model_item');
			$D->show_columns();
			foreach ($D->fetch_array as $coluna){$colunasAtuais[] =$coluna['Field'];};
			verificaNovamente:
			if(!in_array($colunaVerificar, $colunasAtuais)){
				//	CASO NAO EXISTA NENHUMA COLUNA COM ESSE NOME ADD NA TABELA
				return $colunaVerificar;exit;
			}else{ //CASO JÁ EXISTA
				//final com o i
				$str = '_'.$i;
				//final atual da coluna
				$finalAtual = substr($colunaVerificar,-strlen($str));
				//Nome da coluna sem o i
				$colunName  = substr($colunaVerificar,0,-strlen($str));
				//verifica se é uma coluna já duplicada, com final _(int)  se for aumenta um valor e verifica
				if($finalAtual==$str){
					$i = $i+1;
					$colunaVerificar = $colunName.'_'.$i;
				}else{
					//se nao for duplicado ou com valor numerico, adiciona _2
					$colunaVerificar = $colunaVerificar.'_'.$i;
				}
			}
			goto verificaNovamente;
		}

	##########################################################################################################
	# VERIFICA SE UMA TABELA EXISTE
	##########################################################################################################
		function _verifica_tabela($tabela) {
				global $_conectMySQLi_;
				while ($row = mysqli_fetch_row(mysqli_query($_conectMySQLi_, "SHOW TABLES"))) {
						if ($tabela == $row[0]) {
								return false;
								exit;
						}
				}
				return true;
				exit;
		}

	##########################################################################################################
	# EXECUTA ARQUIVO SQL
	##########################################################################################################
		function exec_SQL($filename=null){
			global $_conectMySQLi_;
			if(file_exists($filename)){
				$templine = '';
				$filename 	= file_get_contents($filename);
				$filename 	= str_replace('{_prefix_}',PREFIX_TABLES,$filename);
		 		$filename   = str_replace(array("\n","\r" ,PHP_EOL),PHP_EOL, $filename); 
		 		$lines 		= explode(PHP_EOL,$filename);
				foreach($lines as $line_num => $line) {
					if (substr($line, 0, 2) != '--' && $line != '') {
						$templine .= $line;
						if (substr(trim($line), -1, 1) == ';') {
							mysqli_query($_conectMySQLi_,$templine) or die("Erro em gravar banco de dados: \n :".PHP_EOL.mysqli_error() );
							$templine = '';
						}
					}
				}
				return true;
			}elseif(is_string($filename)){
				$templine 	= '';
				$filename 	= str_replace('{_prefix_}',PREFIX_TABLES,$filename);
		 		$filename   = str_replace(array("\n","\r" ,PHP_EOL),PHP_EOL, $filename); 
		 		$lines 		= explode(PHP_EOL,$filename);
				foreach($lines as $line_num => $line) {
					if (substr($line, 0, 2) != '--' && $line != '') {
						$templine .= $line;
						if (substr(trim($line), -1, 1) == ';') {
							mysqli_query($_conectMySQLi_,$templine) or die("Erro em gravar banco de dados: \n :".mysqli_error().PHP_EOL.'Comando: '.$templine );
							$templine = '';
						}
					}
				}
				return true;

			}
		}


	##########################################################################################################
	# A PARTIR DAQUI AS FUNÇÕES EM BREVE ESTARÃO OBSOLETAS
	##########################################################################################################

		function _set_session($id){
				ob_start();
				if(empty($_SESSION) && session_id()!=$id){
					ini_set('session.cookie_secure',	1);
					ini_set('session.cookie_httponly',	1);
					ini_set('session.cookie_lifetime', "432000");
					ini_set("session.gc_maxlifetime",	"432000");
					ini_set("session.use_trans_sid", 	0);
					ini_set('session.use_cookies', 	1);
					ini_set('session.use_only_cookies', 1);
					ini_set('session.name', 			'_WS_');
					session_cache_expire("432000");
					session_cache_limiter('private');
					session_id($id);
					session_name($id);
					session_start();
				}
		};

		function _session(){
			if(isset($_COOKIE['ws_session'])){
				session_name('_WS_');
				@session_id($_COOKIE['ws_session']);
				@session_start(); 
				@session_regenerate_id();
			};
		};