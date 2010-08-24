<?php
### HARDENED PATCH FOR tuxKernel by WebNuLL
### Licensed under AGPLv3 ( Affero GPLv3 )
# http://wiki.github.com/webnull/OpenWikiBlog/libadmin-documentation

$EXT_INF = array ( 'classname' => 'libadmin');

class libadmin extends KernelModule
{
	# this is a plugin current state, kernel must check if the module is ready to use
	public $state='module not ready'; // "ready" or any error string like "module not ready"
	private $tpl, $alang, $Params, $DB;

	public function __construct ( $Params, &$Kernel )
	{
		$this -> Kernel = $Kernel;
		$this->state = 'ready';
		$this->Params = $Params;
		$this->DB = &$Kernel->SQL;
		$this->Site = $_GET['SITE'];
	}

	public function shva ( ) //# From google "shva" - should have valid autorization ;-)
	{
		if ( isset ( $_SESSION [ $this->Site ] [ 'libadmin'] ['login'] ) )
		{
			//# If the admin adress IP is same as login IP
			if ( $_SESSION [ $this->Site] ['libadmin'] ['ip'] == $_SERVER['REMOTE_ADDR'] )
			{
				return true;
			} else {
				//# What now... we will crash the session here because of invalid ip adress
				$this->logout();
				$this -> Debug -> logString ( 'admin.so.php::E_ERROR::shva: Security violation, invalid ADMIN IP adress, see shva() documentation');
				return false;
				//# Why not deleting the whole session? - Because admin can re-login to save his data...
				//# ofcourse we can throw an exception here but there is no need to crash the whole script, we can just logout the user
				//# its not windows, where is too many bsods ;-)
			}
		}
	}

	public function login ( $Username, $Password )
	{
		$Username = mysql_escape_string ( $Username );
		$Password = $Password; // dont need to secure this, because there is no way to do SQL injection attack from hashed string...

		$WhereClause = new tuxMyDB_WhereClause ();
		$WhereClause -> Add ('', 'name', '=', $Username );
		$WhereClause -> Add ('AND', 'passwd', '=', md5($Password) );

		$Result = $this -> DB -> Select ( 'id', 'libadmin', $WhereClause, '', '', 0,1 );
		$Array = $Result->fech_assoc;

		if ( $Result->num_rows == 1 ) //# ONLY ONE ADMIN AT SAME PASSWORD AND USERNAME, BUT ANYWAY THERE IS LIMITER IN QUERY AS YOU CAN SEE...
		{
			$_SESSION[$this->Site]['libadmin']['ip'] = $_SERVER['REMOTE_ADDR'];
			$_SESSION[$this->Site]['libadmin']['login'] = $Username;
			$_SESSION[$this->Site]['libadmin']['id'] = $Array['id']; // selected only id

			return 'LIBADMIN_LOGGEDIN';
		} else {
			# OHH WRONG USERNAME OR PASSWORD
			return 'LIBADMIN_LOGINFAIL';		
		}
	}

	public function logout ( $DestroySession=false )
	{
		if ( $DestroySession == false )
		{
			unset ( $_SESSION[$this->Site]['libadmin']['login'] );
			unset ( $_SESSION[$this->Site]['libadmin']['ip'] );
			unset ( $_SESSION[$this->Site]['libadmin']['id'] );

			return true;
		} else {
			unset ( $_SESSION[$this->Site]['libadmin'] );		

			return true;
		}
	}
}
?>
