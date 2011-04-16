<?php
### Simple page loader FOR tuxKernel by WebNuLL
### Licensed under AGPLv3 ( Affero GPLv3 )
# http://wiki.github.com/webnull/OpenWikiBlog/

$EXT_INF = array ( 'classname' => 'libmypage' );

class libmypage extends KernelModule
{
	# this is a plugin current state, kernel must check if the module is ready to use
	public $state='module not ready';
	private $Debug, $tpl, $DB, $Version='libmypage 1.2', $alang, $tplfile;

	public function __construct ( $Params, &$Kernel )
	{
		$this -> Kernel = $Kernel;
		$this->state = 'ready';
		$this->Debug = &$Kernel->error_handler;
		$this->DB = $Kernel->SQL;
		$this->tpl = $Kernel->tpl;
		$this->alang = $Kernel->alang;

		if (isset($_GET['seo_id']))
			$PageID = 'mypage_' .$_GET['seo_id'];
		else
			$PageID = 'mypage_' .intval($_GET['page']);

		// if cache expired, lets regenerate new
                if($this->Kernel->stringCacheExpired($PageID, $Params['cache_lifetime']) == True)
		{
			// if page is unset, set default one
			if ( !isset ( $_GET['page'] ) )
			{
				$_GET['page'] = $Params['index'];
			}

			#### SQL QUERY
			$WhereClause = new tuxMyDB_WhereClause ();

			// SEO LINKS
			if(isset($_GET['seo_id']))
			{
				$WhereClause -> Add ('', 'seo_name', '=', $_GET['seo_id'] );
			} else {
				$WhereClause -> Add ('', 'id', '=', intval($_GET['page']) );
			}

		
			#$WhereClause -> Add ('AND', 'site', '=', $SITE ); # Multi-site feature cancelled
		
			# TEMPLATE: Select ( $What, $From, $Where='', $OrderBy='', $POS='ASC', $LimitFrom='', $LimitTo='' )
			$Q = $this->DB->Select( '*', 'libmypage', $WhereClause, '', '', 0,1);

			unset ( $WhereClause ); //# FREE THE OBJECT!

			if ($Q -> num_rows == 1)
			{
				$Page = $Q -> fetch_assoc;

				// IF CACHING IS ENABLED IN LIBMYPAGE
				if($Params['cache_lifetime'] > 0)
				{
					$this->Kernel->stringCacheWrite($PageID, $Page);
				}
			}
		} else {
			$Page = $this->Kernel->stringCacheRead($PageID);
		}

		if ( count($Page) > 0 )
		{
			$INC_FILE = 'data/pages/' .$Page['include']. '.php';
			$TPL_FILE = $Page['template'];

			// PREPARE ENVIRONMENT
			$TPL = &$this->tpl;
			$LANG = &$this->alang;
			$DB = &$this->DB;

			include ( $INC_FILE );

			$this->Debug->logString ( $this->Version. '::E_INFO::init: loading "' .$INC_FILE. '"(' .intval($Page['id']). ')');
		} else {
			# DONT DELETE 404.php file, or it will show you an error ;-)
			# "ITS NOT A BUG - ITS FEATURE" haha ;-)
			$Page = array ( 'include' => '404' );

			$INC_FILE = 'data/pages/404.php';
			$TPL_FILE = '404.tpl';

			// PREPARE ENVIRONMENT
			$TPL = &$this->tpl;
			$LANG = &$this->alang;
			$DB = &$this->DB;
			$Kernel = &$this->Kernel;

			include ( $INC_FILE );
			$this->Debug->logString ( $this->Version. '::E_ERROR::init: 404 error, page ID:' .intval($_GET['page']). '/seo_id: ' .htmlspecialchars($_GET['seo_id']). ' not found.');
		}

		//$this -> tpl -> display ( $TPL_FILE );
		$this->tplfile=$TPL_FILE;
	}

	public function display ()
	{
		$this->tpl->display($this->tplfile);
		return true;
	}
}
?>
