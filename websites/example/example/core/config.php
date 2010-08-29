<?php
# DEFAULT TIMEZONE
date_default_timezone_set ('Europe/Warsaw');

# DATABASE CONFIG
$DB=array('host' => 'localhost', 
		'user' => 'MY_USERNAME', 
		'passwd' => 'MY_PASSWORD', 
		'db' => 'MY_DATABASE',
		'prefix' => 'prefix_' );

# SITE CONFIGURATION
$CFG=array('site_id' => 'example', 'error_handler' => 'exceptions');

# ===== DEFAULT MODULES REQUIRED TO "RUN ON DEMAND" FUNCTION
$DEFMODS = array();
$DEFMODS['tpl'] = 'smarty';
$DEFMODS['alang'] = 'translator';
$DEFMODS['error_handler'] = 'exceptions';

# MODULES TO RUN WITH PARAMETRS
$MODS=array();

// admin module will run only if $_GET['page']==9
$MODS['admin'] = array ( 'k_TRIGGERS' => array (array(1=>$_GET['page'], 2=>9)));

// ===== tuxKernel Hardened Patch is disabled by default, but you can easily enable it
#$MODS['hardened'] = array ('get' => true, 'post' => true, 'files' => true, 'directories' => true);

// ===== ALLOWS "PATCHING" SOME MODULES, JUST ONE MODULE CAN MODIFY BINDED VARIABLE OF ANOTHER
$MODS['hooks'] = NuLL;

// ===== Important to turn on if you want to render any page...
$MODS['smarty'] = array ( 'caching' => false, 
				'cache_lifetime' => 120, 
				'template_dir' => 'websites/example/templates', 
				'compile_dir' => 'websites/example/templates_c', 
				'config_dir' => 'websites/example/core', 
				'cache_dir' => 'websites/example/cache' ); # FOR LOADING TEMPLATE

// ===== The translator will help you load diffirent language
$MODS['translator'] = array ( 'default_language' => 'english' );

// ===== Mypage will show current subpage using Smarty and Translator
$MODS['mypage'] = array ( 'index' => 1 ); # show current sub-page

// ===== This module will easily create a menu with language support
$MODS['menu'] = NuLL; # this will create our menu

// ===== This will call mypage->display
$MODS[] = array ( 'mypage', 'display', '');

# ADDITIONAL HTML CODE WHEN ERROR WILL OCCURE
$HTML=array('header' => "<html><head><title>OPS!</title></head><body><h2>We are sorry! Our application crashed unexpectly and report was sent to administrator.</h2>\n<br/>{error}<br/>Dont worry, just try to go back in your web browser - we will repair this site soon.</body></html>");
?>