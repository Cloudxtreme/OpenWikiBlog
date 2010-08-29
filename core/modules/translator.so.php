<?php
### Translation loader FOR tuxKernel by WebNuLL
### Licensed under AGPLv3 ( Affero GPLv3 )
# http://wiki.github.com/webnull/OpenWikiBlog/

$EXT_INF = array ( 'classname' => 'libtranslate');

class libtranslate extends KernelModule
{
	# this is a plugin current state, kernel must check if the module is ready to use
	public $state='module not ready';
	private $Site, $arTranslations, $Language='english', $Params, $Debug;

	public function __construct ( $Params, &$Kernel )
	{
		$this -> Kernel = $Kernel;
		$this->state = 'ready';
		$this->Params = &$Params;

		// set as default translating module
		$Kernel->setAsDefault('translator', 'alang');

		// bind debugger
		$this->Debug = &$Kernel->error_handler;

		// bind as local variable
		$this -> Site = addslashes($_GET['SITE']);

		// HERE IS LANGUAGE "SWITCH", JUST TYPE INDEX.PHP?trlang=english to set english as default language in current session
		if ( isset ( $_GET['trlang'] ) )
		{
			// SET NEW LANGUAGE IF EXISTS
			if ( $this->languageExists ($_GET['trlang']))
				$this->setLanguage ($_GET['trlang']);
			else // SET DEFAULT LANGUAGE IF OLD DOES NOT EXISTS
				$this->setLanguage ($Params['default_language']);
		} else {
			// saved language, or when "trlang" is not set.

			if ( !isset ( $_SESSION[$this->Site]['language'] ) )
			{
				if ( isset ( $Params ['default_language'] ) )
				{
					$this->setLanguage ( $Params['default_language'] );
				} else {
					throw new Exception ( 'translator.so.php::E_ERROR::init:: Cannot load default language, please check configuration', 80 );
				}
			} else {
				// if language was set
				$this->setLanguage($_SESSION[$this->Site]['language'] );
			}
		}
	}

	public function __get ( $Mod )
	{
		if ( isset ( $this->arTranslations[$Mod] ) )
		{
			return $this->arTranslations[$Mod];
		}
	}

	public function setLanguage($Lang)
	{
		// security, never trust user... or third party module developer ;-)
		$Lang = addslashes($Lang);

		if (!is_dir('websites/' .$this->Site. '/translations/' .$Lang. '/'))
		{
			$this->Debug->logString ('translator.so.php::E_ERROR::setLanguage: No language directory found for "' .$Lang. '"');
			throw new Exception ( 'translator.so.php::E_ERROR::setLanguage:: No language directory found for "' .$Lang. '"', 81 );
			return false;
		}

		// we must save some debugging informations - its more easy to fix error when there are debugging informations ;-)
		$this->Debug->logString ('translator.so.php::E_INFO::setLanguage: Language "websites/' .$this->Site. '/translations/' .$Lang. '/" loaded.');
		// save language to session to recover at next page load
		$_SESSION[$this->Site]['language'] = $Lang;

		// set as default language
		$this->Language = $Lang;

		return true;
	}

	public function loadTranslation($File)
	{
		// security... never, never trust user or third party module developer ;-)
		$File = addslashes ( $File );

		if (!is_file( 'websites/' .$this->Site. '/translations/' .$this->Language. '/' .$File. '.php'))
		{
			$this->Debug->logString ( 'translator.so.php::E_ERROR::loadTranslation: Cannot load translation file "' .$File. '"');
			return false;
		}

		// lang will be loaded from file..
		$Lang = false;

		include ( 'websites/' .$this->Site. '/translations/' .$this->Language. '/' .$File. '.php' );
		$this -> arTranslations [ $File ] = $Lang;
		return true;
	}

	public function isLoaded ($File)
	{
		if (!isset ( $this->arTranslations[$File]))
		{
			return false;
		}

		return true;
	}

	// to free memory from unused variables, just please use this function to make whole system faster ;-)
	public function unloadTranslation ($File)
	{
		if ( $this -> isLoaded ( $File ) )
		{
			unset ($this->arTranslations[$File]);
			return true; 
		}
	}

	public function languageExists ( $Lang )
	{
		if ( is_dir('websites/' .$this->Site. '/translations/' .$Lang. '/'))
		{
			return true;
		} else {
			return false;		
		}
	}
}
?>