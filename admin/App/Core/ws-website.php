<? 
	$ROOT_EXISTS 		= (isset($_SERVER['ROOT_WEBSHEEP']));
	$ROOT_WEBSHEEP 		= ($ROOT_EXISTS) ? implode(array_filter(explode("/",@$_SERVER['ROOT_WEBSHEEP'])),"/") : false;
	$PATH_ATUAL 		= implode(array_filter(explode("/",substr($_SERVER['PHP_SELF'],0,-strlen('admin/App/Core/ws-website.php')))),"/");
	
	if( $ROOT_EXISTS && $ROOT_WEBSHEEP==$PATH_ATUAL && file_exists(dirname(__FILE__).'/../../../ws-config.php')){
		include_once(dirname(__FILE__).'/../Lib/class-ws-v1.php');
		ws::init();
	}else{
		include_once(dirname(__FILE__).'/../../../init.php');
	}
