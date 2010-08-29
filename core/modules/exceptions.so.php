<?php
### Debugger and error handler FOR tuxKernel by WebNuLL
### Licensed under AGPLv3 ( Affero GPLv3 )
# http://wiki.github.com/webnull/OpenWikiBlog/

$EXT_INF = array ( 'classname' => 'libexceptions' );

class libexceptions extends KernelModule
{
	# this is a plugin current state, kernel must check if the module is ready to use
	public $state;
	private $log;

	public function __construct ( $Params=array(), &$Kernel )
	{
		$this -> state = 'ready';
		$this -> Kernel = $Kernel;
		$this -> log = "exceptions.so.php::E_INFO::init: libexceptions initialized on ".$Kernel->getVersion()."\n";

		# SET SELF AS DEFAULT ERROR HANDLER
		$Kernel -> setAsDefault ( 'exceptions', 'error_handler' );
	}

	public function Analyze ( &$arError )
	{
		$Info = explode ( '::', $arError );

		return array ( 'module' => $Info[0], 
				'code' => $arError -> getCode (), 
				'file' => $arError -> getFile (), 
				'line' => $arError -> getLine (),
				'trace' => $arError -> getTraceAsString(),
				'message' => $arError->getMessage(),
				'nice' => $this -> setNice ( $Info ) );
				
		
	}

	public function logString ( $String )
	{
		$this -> log .= $String."\n";
	}

	public function ShowLog ()
	{
		return $this -> log;
	}

	private function setNice ( &$Info )
	{
		switch ( $Info[1] )
		{
			case 'E_ERROR':
				return -15;
			break;

			case 'E_WARNING':
				return -5;
			break;

			case 'E_NOTICE':
				return 0;
			break;

			default:
				return -15;
			break;
		}
	}

	public function TriggerCrash ( $Errors )
	{
		# VARIABLES
		$Log = NuLL;
		$Config = $this->Kernel->config();

		# LETS SHOW THE ERRORS
		foreach ( $Errors as $Key => $Value )
		{
			$Log .= "\n<br/><b>" .$Value['file']. "(" .$Value['line']. "): " .$Value['message']. "</b><br/>\nStack trace: ".$Value['trace']."<br/><br/>\n\n";
		}

		echo str_replace('{error}', $Log, $Config['HTML']['header']);

		$this -> MakeADump ( $Log );

		die();
	}

	private function MakeADump(&$RID)
	{
		$LOG_ID = md5($RID);
		$Report = serialize ( array ( 'memory' => $this-> Kernel, 
					'files' => get_included_files(), 
					'date' => date('G:i:s d.m.Y'),
					'get' => $_GET,
					'post' => $_POST,
					'server' => $_SERVER,
					'cookie' => $_COOKIE,
					'session' => $_SESSION,
					'error' => $RID ) );

		$fp = @fopen ( 'data/crash/' .$LOG_ID. '.txt', 'w' );
		@fwrite ( $fp, $Report );
		@fclose ( $fp );
		

	}
}
?>