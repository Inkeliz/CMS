<?
############################################################################################################################################
# DEFINIMOS O ROOT DO SISTEMA
############################################################################################################################################
	if(!defined("ROOT_WEBSHEEP"))	{
		$path = substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'ws-commits'));
		$path = implode(array_filter(explode('/',$path)),"/");
		define('ROOT_WEBSHEEP',(($path=="") ? "/" : '/'.$path.'/'));
	}
	
############################################################################################################################################
	if(!defined("INCLUDE_PATH")){define("INCLUDE_PATH",str_replace("\\","/",substr(realpath(__DIR__),0,strrpos(realpath(__DIR__),'admin'))));}

#############################################################################
#	IMPORTAMOS A CLASSE PADRÃO DO SISTEMA
#############################################################################
	ob_start();
	include_once(INCLUDE_PATH.'admin/app/lib/class-ws-v1.php');
	ob_end_clean();
	$url = ws::urlPath(0,false,'array');

#############################################################################
#	SETAMOS O HEADER PARA JSON
#############################################################################
	header("Content-type:application/json");

#############################################################################
#	TRATAMOS O JSON DOS COMMITS E RETORNAMOS
#############################################################################
	if(count($url)==2){
		$json = json_decode(ws::get_github_commits('/'.$url[2]));
	}else{
		$json = json_decode(ws::get_github_commits());
	}
	echo json_encode($json, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
?>