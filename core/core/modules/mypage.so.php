<?php
### Simple page loader FOR tuxKernel by WebNuLL
### Licensed under AGPLv3 ( Affero GPLv3 )
# http://wiki.github.com/webnull/OpenWikiBlog/

$EXT_INF = array ( 'classname' => 'libmypage' );

class libmypage extends KernelModule
{
	# this is a plugin current state, kernel must check if the module is ready to use
	public $state='module not ready';
	private $Debug, $tpl, $DB, $Version='libmypage 1.1', $alang;

	public function __construct ( $Params, &$Kernel )
	{
		$this -> Kernel = $Kernel;
		$this->state = 'ready';
		$this->Debug = &$Kernel->error_handler;
		$this->DB = &$Kernel->SQL;
		$this->tpl = &$Kernel->tpl;
		$this->alang = &$Kernel->alang;

		// dont forget about security, be paranoic!
		$SITE = addslashes($_GET['SITE']);

		// if page is unset, set default one
		if ( !isset ( $_GET['page'] ) )
		{
			$_GET['page'] = $Params['index'];
		}

		#### SQL QUERY
		$WhereClause = new tuxMyDB_WhereClause ();
		$WhereClause -> Add ('', 'id', '=', intval($_GET['page']) );
		$WhereClause -> Add ('AND', 'site', '=', $SITE );
		
		# TEMPLATE: Select ( $What, $From, $Where='', $OrderBy='', $POS='ASC', $LimitFrom='', $LimitTo='' )
		$Q = $this->DB->Select( '*', 'libmypage', $WhereClause, '', '', 0,1);

		unset ( $WhereClause ); //# FREE THE OBJECT!


		if ( $Q -> num_rows == 1 )
		{
			$Page = $Q -> fetch_assoc;
			
			$INC_FILE = $this -> checkForPage ( &$Page, $SITE );
			$TPL_FILE = $Page['template'];

			// PREPARE ENVIRONMENT
			$TPL = &$this->tpl;
			$LANG = &$this->alang;
			$DB = &$this->DB;

			include ( $INC_FILE );

			$this->Debug->logString ( $this->Version. '::E_INFO::init: loading "' .$INC_FILE. '"(' .intval($_GET['page']). ')');
		} else {
			# DONT DELETE 404.php file, or it will show you an error ;-)
			# "ITS NOT A BUG - ITS FEATURE" haha ;-)
			$Page = array ( 'include' => '404' );

			$INC_FILE = $this -> checkForPage ( $Page, $SITE );
			$TPL_FILE = '404.tpl';

			// PREPARE ENVIRONMENT
			$TPL = &$this->tpl;
			$LANG = &$this->alang;
			$DB = &$this->DB;

			include ( $INC_FILE );
			$this->Debug->logString ( $this->Version. '::E_ERROR::init: 404 error, page ID:' .intval($_GET['page']). ' not found.');
		}

		$this -> tpl -> display ( $TPL_FILE );
	}

	public function checkForPage ( &$Page, $SITE )
	{
		$INC_FILE=false;

		if ( is_file ( 'websites/' .$SITE. '/pages/' .$Page['include']. '.php' ) )
		{
			$INC_FILE='websites/' .$SITE. '/pages/' .$Page['include']. '.php';
		} elseif ( is_file ( 'pages/' .$Page['include']. '.php' ) ) {
			$INC_FILE='pages/' .$Page['include']. '.php';			
		}

		return $INC_FILE;
	}
}
?>
