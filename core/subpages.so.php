<?php
#### Open Wiki Blog copyright by WebNuLL ( JID: webnull@ubuntu.pl )
#### This code is on GPLv3 license
#### Linux/Unix and Open Source/Free Software forever ;-)

class Subpage
{
	private $SQL, $CFG;
	public $Kernel;

	public function __construct ($PATH='')
	{
		//include ( 'core/database.php' );
		include ( $PATH. 'core/kernel.so.php');
		include ( 'data/core/config.php' );

		# DB and CFG variables will not be duplicated, there is a reference at class function
		$this->Kernel = new tuxKernel ( $CFG );
		$this->Kernel -> LD_LIBRARY_PATH = $PATH;
		//$this->SQL = new tuxMyDB ( $DB, $CFG, $Kernel );
		$this->CFG = &$CFG;
		//$this->MODS=&$MODS;
		//$this->HTML=&$HTML;
		//$this->DEFMODS=&$DEFMODS;

		# TRY TO LOAD EXCEPTIONS MODULE, IF NOT WE MUST SHOW ALERT BUT WE CANT DO ANYTHING HERE BECAUSE SYSTEM IS HALTED
		try {
			$this->Kernel->modprobe ( $CFG['defmods']['error_handler'] ); # load error_handler module specified in config
		}

		catch ( Exception $e )
		{
			echo "We are sorry, the system crashed... please contact administrator!<br/>\n";
			die ( $e->getMessage());
		}
	}

	public function loadDefaultModules()
	{
		try {
			# LOAD STARTUP MODULES...
			foreach ( $this->CFG['mods'] as $Key => $Value )
			{
				$this->Kernel -> modprobe ( $Key, $Value );
			}
		}

		catch ( Exception $e )
		{
			$Error = $this->Kernel -> error_handler -> Analyze ( $e );

			$Errors[] = &$Error;

			if ( $Error [ 'nice' ] < -4 )
			{
				
				$this-> Kernel -> error_handler -> TriggerCrash ( $Errors );
			}

		}
	}
	
	public function modprobe($Module, $Params='')
	{
		$Error = array();

		try {
			$this->Kernel->modprobe ( $Module, $Params );
		}

		catch ( Exception $e )
		{
			$Error = $this->Kernel->error_handler->Analyze($e);

			$Errors[] = $Error;

			if ( $Error [ 'nice' ] < -4 )
			{
				$this->Kernel->error_handler->TriggerCrash($Errors);
			}

		}
		
	}
	
	public function getModules()
	{
	}
}
?>
