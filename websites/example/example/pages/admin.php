<?php
// THIS IS A SPECIAL ENVIRONMENT WITH PREDEFINED VARIABLES
// $DB, $LANG, $TPL, $Kernel

// load translation for this page
#$LANG->loadTranslation('admin');

// we need a module on demand, and we know that it isnt loaded by default
//$Kernel->modprobe('admin', ''); // ITS AUTOMATICALLY LOADED BY "MODULE ON DEMAND"

// the if is for performance... we dont want to create more arrays in memory...
if ( is_object ($LANG))
{
	$Kernel->hooks->define_hook('admin', 'modifyMenu', 'menu_translation');
} else {
	$Kernel->hooks->define_hook('admin', 'modifyMenu', 'menu_array');
}
?>