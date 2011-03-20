<?php
### Smarty implementation FOR tuxKernel by WebNuLL
### Licensed under AGPLv3 ( Affero GPLv3 )
### http://wiki.github.com/webnull/OpenWikiBlog/

$EXT_INF = array ( 'classname' => 'libsmarty' );

class libsmarty extends KernelModule
{
	# this is a plugin current state, kernel must check if the module is ready to use
	public $state='module not ready';
	public $tpl;
	private $Debug;

	public function __construct ( $Params, &$Kernel )
	{
		$this -> Kernel = $Kernel;
		$this->state = 'ready';

		// set as default template module
		$Kernel -> setAsDefault ( 'smarty', 'tpl' );

		$this->Debug = &$Kernel->error_handler;
		
		$this -> FixParams ( &$Params );


		require ( $this->Kernel->LD_LIBRARY_PATH. 'core/lib/smarty/Smarty.class.php' );

		// new smarty object
		$this->tpl = new Smarty;

		// turn on the cache?
		$this->tpl -> caching = $Params['caching']; 

		// cache life time in seconds
		$this->tpl -> cache_lifetime = $Params['cache_lifetime'];

		$this->tpl -> template_dir = $Params['template_dir'];
		$this->tpl -> compile_dir = $Params['compile_dir'];
		$this->tpl -> config_dir = $Params['config_dir'];
		$this->tpl -> cache_dir = $Params['cache_dir'];

		if ( get_class ( $this->tpl ) == 'Smarty' )
		{
			$this -> Debug -> logString ( 'smarty.so.php::E_INFO::init: Smarty initialized successfully');
		} else {
			$this -> Debug -> logString ( 'smarty.so.php::E_INFO::init: Cannot initialize smarty, check config');		
		}
	}

	private function FixParams ( &$Params )
	{
		if ( !is_array ( $Params ) )
		{
			$Params = array ('caching' => false, 'cache_lifetime' => 100 );

			$this -> Debug -> logString ( 'smarty.so.php::E_WARNING::FixParams: Fixing invalid parametrs');
		}

		if ( !is_int ( $Params['cache_lifetime'] ) )
		{
			$Params['cache_lifetime'] = 100;

			$this -> Debug -> logString ( 'smarty.so.php::E_WARNING::FixParams: Fixing invalid cache_lifetime parametr');
		}

		if ( !is_bool ( $Params['caching'] ) )
		{
			$Params['caching'] = false;
			$this -> Debug -> logString ( 'smarty.so.php::E_WARNING::FixParams: Fixing invalid caching parametr');
		}
	}

	public function assign ( $Variable, $Value )
	{
		$this -> tpl -> assign ( $Variable, $Value );
	}

	public function display ( $File )
	{
		$this->Debug->logString ( 'smarty.so.php::E_INFO::display: "' .$File. '"');
		$this -> tpl -> display ( $File );
	}

	// automaticaly add variables with values from array given in first argument
	public function fillFromArray($Array)
	{
		if(!is_array($Array))
		{
			$this->Debug->logString ( 'smarty.so.php::E_ERROR::fillFromArray: Specified argument is not an array');
			return false;
		}

		foreach ($Array as $Key => $Value)
		{
			$this->assign($Key,$Value); // this is so simple!
		}
	}
}
?>
