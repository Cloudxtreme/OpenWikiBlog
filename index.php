<?php
session_start ();
#### Open Wiki Blog copyright by WebNuLL ( JID: webnull@ubuntu.pl )
#### This code is on GPLv3 license
#### Linux/Unix and Open Source/Free Software forever ;-)

$_GET['SITE'] = $SITE='cube';
include ( 'core/database.php' );
include ( 'core/kernel.so.php');
include ( 'websites/' .$SITE. '/core/config.php' );

# DB and CFG variables will not be duplicated, there is a reference at class function

$Kernel = new tuxKernel ( $CFG, $MODS, $HTML, $DEFMODS );
$SQL = new tuxMyDB ( $DB, $CFG, $Kernel );

# TRY TO LOAD EXCEPTIONS MODULE, IF NOT WE MUST SHOW ALERT BUT WE CANT DO ANYTHING HERE BECAUSE SYSTEM IS HALTED
try {
	$Kernel -> modprobe ( $CFG['error_handler'] ); # load error_handler module specified in config
}

catch ( Exception $e )
{
	echo "We are sorry, the system crashed... please contact administrator!<br/>\n";
	die ( $e->getMessage());
}

$Kernel -> connect ( &$SQL ); # link database with kernel

$Error = array(); // single error
$Errors = array (); // multiple errors

if ( is_array ( $MODS ) )
{
	try {
		# LOAD STARTUP MODULES...
		foreach ( $MODS as $Key => $Value )
		{
			$Kernel -> modprobe ( $Key, $Value );
		}
	}

	catch ( Exception $e )
	{
		
		$Error = $Kernel -> error_handler -> Analyze ( $e );

		$Errors[] = &$Error;

		if ( $Error [ 'nice' ] < -4 )
		{
			$Kernel -> error_handler -> TriggerCrash ( $Errors );
		}

	}
}

#echo $Kernel -> error_handler -> ShowLog();
?>
