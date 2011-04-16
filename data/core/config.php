<?php

# Set default time zone
date_default_timezone_set ('Europe/Warsaw');

# DATABASE CONFIG

// mysql database
/*$CFG['db']=array('host' => 'localhost', 
		'user' => 'wikiblog', 
		'passwd' => 'password-to-your-mysql-server', 
		'db' => 'wikiblog',
		'prefix' => 'prefix_' );*/

// sqlite database
$CFG['db'] = array('db' => 'data/db/site.sqlite', 'prefix' => 'prefix_');

# SITE CONFIGURATION
$CFG['site']=array('site_id' => 'cube', 'error_handler' => 'exceptions');

# ===== DEFAULT MODULES REQUIRED TO "RUN ON DEMAND" FUNCTION
$CFG['defmods'] = array();
$CFG['defmods']['tpl'] = 'smarty';
$CFG['defmods']['alang'] = 'translator';
$CFG['defmods']['error_handler'] = 'exceptions';

# MODULES TO RUN WITH PARAMETRS
$CFG['mods']=array();
$CFG['mods']['exceptions'] = NuLL;
$CFG['mods']['sqlite'] = array(&$CFG);
//$CFG['mods']['admin'] = array ( 'k_TRIGGERS' => array (array(1=>$_GET['page'], 2=>9)));

// ===== tuxKernel Hardened Patch is disabled by default, but you can easily enable it
#$MODS['hardened'] = array ('get' => true, 'post' => true, 'files' => true, 'directories' => true);

// ===== ALLOWS "PATCHING" SOME MODULES, JUST ONE MODULE CAN MODIFY BINDED VARIABLE OF ANOTHER
$CFG['mods']['hooks'] = NuLL;

// ===== Important to turn on if you want to render any page...
$CFG['mods']['smarty'] = array ( 'caching' => false, 
				'cache_lifetime' => 120, 
				'template_dir' => 'data/templates', 
				'compile_dir' => 'data/templates_c', 
				'config_dir' => 'data/core', 
				'cache_dir' => 'data/cache' ); # FOR LOADING TEMPLATE

// ===== The translator will help you load different language
$CFG['mods']['translator'] = array ( 'default_language' => 'english' );

// ===== Mypage will show current subpage using Smarty and Translator
$CFG['mods']['mypage'] = array ( 'index' => 1, 'cache_lifetime' => 120 ); # show current sub-page

// ===== This module will easily create a menu with language support
$CFG['mods']['menu'] = NuLL; # this will create our menu

// ===== DISPLAY OUR PAGE...
//$CFG['mods'][] = array ( 'mypage', 'display', '');

# ADDITIONAL HTML CODE WHEN ERROR WILL OCCURE
$CFG['html']=array('header' => "<html><head><title>OPS!</title></head><body><h2>We are sorry! Our application crashed unexpectly and report was sent to administrator.</h2>\n<br/>{error}<br/>Dont worry, just try to go back in your web browser - we will repair this site soon.</body></html>");

?>


