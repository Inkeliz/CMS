<?
class session{
	private $type;
	private $preStr;
	private $maxCookie;
	private $cookieLenght;
	private $stringone;
	private $duratacookie;
	private $secret;
	public function __construct ($name="ws-session",$cookieUI=null) {
		$cookieUI			= 	(isset($_COOKIE['ws-ui']) && $_COOKIE['ws-ui']!="") ? _decripta($_COOKIE['ws-ui'],TOKEN_ACCESS) : (($cookieUI!=null) ? _decripta($cookieUI,TOKEN_ACCESS) : null);
		$this->type 		= 	"session";
		$this->secury 		= 	1;
		$this->prefix 		= 	"websheep-"; 
		$this->preStr 		= 	ID_SESS; 
		$this->secret 		=	SECURE_AUTH_KEY;		
		$this->maxCookie	=	20;
		$this->CoockieIdSess=	$cookieUI;
		$this->cookieLenght	=	3096;	
		$this->duratacookie	=	(time() + ( 24 * 3600));	
		$this->newName 		= 	strtolower($this->prefix.substr(str_replace(array("_","-","==","=","."," ","+"),"",base64_encode(md5($this->preStr))),0,128));
	
		if($this->CoockieIdSess!=null){
			$checkUser					= new MySQL();
			$checkUser->set_table(PREFIX_TABLES.'ws_usuarios');
			$checkUser->set_where('token="'.$this->CoockieIdSess.'" AND token<>""');
			$checkUser->select();
			$this->checkUser = $checkUser->fetch_array[0];
			$this->start();
		}
		
	}

	public function start() {
		$ID_SESS = $this->checkUser['sessao'];
		if($this->verifyLogin()==true){
			####################################################################
			# ALGUNS SERVIDORES VEM COM O DIRETÓRIO /TMP SEM PERMISSÃO PRA LEITURA OU ESCRITA
			# ENTÃO PARA PREVINIR ISSO JÁ JOGAMOS A PERMISSÃO 0700
			####################################################################
			if(empty($_SESSION) || (isset($_SESSION) && session_id()!=$ID_SESS) || session_status() == PHP_SESSION_NONE){
				ini_set("session.gc_maxlifetime","432000");
				ini_set("url_rewriter.tags","");
				ini_set("session.cookie_secure",true);
				ini_set("session.cookie_httponly",true);
				ini_set("session.use_trans_sid", false);
				chmod(session_save_path().'/sess_'.$ID_SESS, 0700);
				@session_id($ID_SESS);
				@session_name($this->newName);
				@session_start();
			}
		}else{
				@session_id($ID_SESS);
				@session_name($this->newName);
				@session_start();
		}
	}

	public function verifyLogin() {

		if(empty($_SESSION) || $this->CoockieIdSess==null){ 
			return false;
		}elseif(isset($this->checkUser['sessao']) && session_id()==$this->checkUser['sessao']) {
		 	return true;
		 }else{
		 	return false;
		 }
	}
 	private function build_str($ar) {
		$qs = array();
		foreach ($ar as $k => $v) { $qs[] = $k.'='.$v; }
		return join('&', $qs);
	}
	private function prelevaStringaTotale() {
		$cookiesSet = array_keys($_COOKIE);
		$out 		= "";
		for ($x=0;$x<count($cookiesSet);$x++) {
			if (strpos(" ".$cookiesSet[$x],$this->preStr)==1)
				$out.=$_COOKIE[$cookiesSet[$x]];
		}
		return $this->decrypta($out,$this->secret);
	}
	public function debug() {return $this->prelevaStringaTotale();}
 	private function calcolaCookieLiberi() {
		$cookiesSet = array_keys($_COOKIE);
		$c=0;
		for ($x=0;$x<count($cookiesSet);$x++) {
			if (strpos(" ".$cookiesSet[$x],$this->preStr)==1)
				$c+=1;
		}
		return $this->maxCookie - count($cookiesSet) + $c;
	}
	private function my_str_split($s,$len) {
		$output = array();
		if (strlen($s)<=$len) {
			$output[0] = $s;
			return $output;
		}
		$i = 0;
		while (strlen($s)>0) {
			$s = substr($s,0,$len);
			$output[$i]=$s;
			$s = substr($s,$len);
			$i++;
		}
		return $output;
	}
	public function set($var,$value) {
		if ($this->type=="cookie") {
				if($this->secury==1){
					if ($this->stringone!="") {
						parse_str($this->stringone, $vars);
					} else {
						$vars=array();
					}
					$vars[$var] = $value;
					$str = $this->crypta($this->build_str($vars),$this->secret);
					$arr = $this->my_str_split($str,$this->cookieLenght);
					$cLiberi = $this->calcolaCookieLiberi();
					if (count($arr) < $cLiberi) {
						$this->stringone = $this->build_str($vars);
						for ($i=0;$i<count($arr);$i++) {
							setcookie($this->preStr.$i,$arr[$i],time()+$this->duratacookie,"/", $_SERVER['HTTP_HOST'] );
						}
					} else {
						return "errore cookie overflow";
					}
				}else{
					setcookie($var,$value,time()+$this->duratacookie,"/", $_SERVER['HTTP_HOST'] );
				}
		} else {
			if($this->secury==1){
				$_SESSION[$var]=$this->crypta($value,$this->secret);
			}else{
				$_SESSION[$var]=$value;
			}
		}
	}
	public function get($var) {
		if ($this->type=="cookie") {
				if($this->secury==1){
					if ($this->stringone!="") {
						parse_str($this->stringone, $vars);
					} else {
						return "";
					}
					if(!isset($vars[$var])) {return "";}
					return $vars[$var];

				}else{
					return $_COOKIE[$var];
				}
		} else {
			if($this->secury==1){
				return $this->decrypta(@$_SESSION[$var],$this->secret);
			}else{
				return @$_SESSION[$var];
			}
		}
	}
 	public function finish() {
			if($this->secury==1){
				$cookiesSet = array_keys($_COOKIE);
				for ($x=0;$x<count($cookiesSet);$x++) {
					if (strpos(" ".$cookiesSet[$x],$this->preStr)==1){
						setcookie($cookiesSet[$x],"",time()-3600*24,"/",$_SERVER['HTTP_HOST']);
						$this->stringone="";
					}
				}
			}else{
				$cookiesSet = array_keys($_COOKIE);
				for ($x=0;$x<count($cookiesSet);$x++) {
					setcookie($cookiesSet[$x],"",time()-3600*24,"/", $_SERVER['HTTP_HOST'] );
				}
			}


			$SetUserSession = new MySQL();
			$SetUserSession->set_table(PREFIX_TABLES.'ws_usuarios');
			$SetUserSession->set_where('id="'.$this->checkUser['id'].'"');
			$SetUserSession->set_update('sessao', '');
			$SetUserSession->salvar();

			session_id($_COOKIE['ws-ui']);
			session_name($this->newName);
			$_SESSION=array();
			unset($_SESSION);
			session_unset();
			session_destroy();
			session_write_close();
			flush();
	}
	private function crypta($t,$secret){
		if ($t=="") return $t;
		return _encripta($t,$secret);
	}
	private function decrypta($t,$secret) {
		if ($t=="") return $t;
		return _decripta($t,$secret);
	}
	private function ed($t) {
		$r = md5($this->secret); $c=0; $v="";
		for ($i=0;$i<strlen($t);$i++) {
			if ($c==strlen($r)) $c=0;
			$v.= substr($t,$i,1) ^ substr($r,$c,1);
			$c++;
		}
		return $v;
	}
	public function verify() {
		  if(isset($this->checkUser['sessao']) && isset($_SESSION) && session_id()==$this->checkUser['sessao']){
		 	 return true;
		  }else{
		 	 return false;
		 }
	}


}
?>
