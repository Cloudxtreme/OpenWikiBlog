<?php
# DATABASE CONFIG
$DB=array('host' => 'localhost', 
		'user' => 'MY_USERNAME', 
		'passwd' => 'MY_PASSWORD', 
		'db' => 'MY_DATABASE',
		'prefix' => 'prefix_' );

# SITE CONFIGURATION
$CFG=array('site_id' => 'example', 'error_handler' => 'exceptions');

# MODULES TO RUN WITH PARAMETRS
$MODS=array();
// ===== tuxKernel Hardened Patch is disabled by default, but you can easily enable it
#$MODS['hardened'] = array ('get' => true, 'post' => true, 'files' => true, 'directories' => true);

// ===== Important to turn on if you want to render any page...
$MODS['smarty'] = array ( 'caching' => false, 
				'cache_lifetime' => 120, 
				'template_dir' => 'websites/example/templates', 
				'compile_dir' => 'websites/example/templates_c', 
				'config_dir' => 'websites/example/core', 
				'cache_dir' => 'websites/example/cache' ); # FOR LOADING TEMPLATE

// ===== The translator will help you load diffirent language
$MODS['translator'] = array ( 'default_language' => 'english' );

// ===== This module will easily create a menu with language support
$MODS['menu'] = NuLL; # this will create our menu

// ===== Mypage will show current subpage using Smarty and Translator
$MODS['mypage'] = array ( 'index' => 1 ); # show current sub-page

# ADDITIONAL HTML CODE WHEN ERROR WILL OCCURE
$HTML=array('header' => "<html><head><title>OPS!</title></head><body><h2>We are sorry! Our application crashed unexpectly and report was sent to administrator.</h2>\n<br/>{error}<br/>Dont worry, just try to go back in your web browser - we will repair this site soon.</body></html>");
?>
