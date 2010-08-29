<?php
### HARDENED PATCH FOR tuxKernel by WebNuLL
### Licensed under AGPLv3 ( Affero GPLv3 )
### http://wiki.github.com/webnull/OpenWikiBlog/

$EXT_INF = array ( 'classname' => 'KernelHardenedPatch');

class KernelHardenedPatch extends KernelModule
{
	# this is a plugin current state, kernel must check if the module is ready to use
	public $state='module not ready';
	private $tpl, $alang, $Params;

	public function __construct ( $Params, &$Kernel )
	{
		$this -> Kernel = $Kernel;
		$this->state = 'ready';
		$this->Params = $Params;

		// INPUT SCANNING FOR FILES AND SQL COMMANDS
		if ( $Params['get'] == true )
			$_GET = $this->ScanArray($_GET);

		if ( $Params['post'] == true )
			$_POST = $this->ScanArray($_POST);

		if ( $Params['session'] == true )
			$_SESSION = $this->ScanArray($_SESSION);

		if ( $Params['cookie'] == true )
			$_COOKIE = $this->ScanArray($_COOKIE);
	}

	private function ScanArray ( $Array )
	{
		foreach ( $Array as $Key => $Value )
		{
			if ( is_array ( $Value ) )
			{
				// recursion
				$Array[$Key] = $this -> ScanArray ( $Value );
			} elseif ( is_string ( $Value ) ) { // no else here because there is no need to check the integer ( optimalization )
				$Array[$Key] = $this -> SearchMatches ( $Value );
			}
		}

		return $Array;
	}

	private function SearchMatches ( $String )
	{
		$Disallowed = array();

		if ( is_file ( $String ) AND $this -> Params ['files'] == true )
		{
			// secures the file
			$String = addslashes ( str_replace('../', '', $String) );
		} elseif ( is_dir ( $String ) AND $this -> Params ['directories'] == true ) {
			// secures the directory
			$String = addslashes ( str_replace ('../', '', $String ) );
		} elseif ( $this -> Params ['sql'] == true ) {
			// SQL Injection simple detector
	$Disallowed = array_merge($Disallowed, array ( 'DROP TABLE', 'ALTER TABLE', 'TRUNCATE TABLE', 'INSERT INTO', 'DELETE FROM' ) );
			
		} elseif ( $this -> Params['script'] == true ) {
			// XSS simple injection
			$Disallowed = array_merge($Disallowed, array ( '<script', '<?php' ) );
		}

		if ( $this -> Params ['sql'] == true OR $this -> Params ['script'] == true )
		{
			// check for SQL injection
			// there is no possibility to secure UPDATE, SELECT and others without making script slowly

			if ( $this->stristr_array ( $Disallowed, $String  ) )
			{
				$String = NuLL;
			}	
		}

		return $String;
	}

	// this function was copied from public code snipped, dont remember source but its very simple code :)
	private function stristr_array( $haystack, $needle ) 
	{
		if ( !is_array( $haystack ) ) {
			return false;
		}

		foreach ( $haystack as $element ) {
			if ( stristr( $element, $needle ) ) 
			{
				return $element;
			}
		}
	}

}
?>