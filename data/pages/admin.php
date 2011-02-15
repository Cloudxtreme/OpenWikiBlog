<?php
// THIS IS A SPECIAL ENVIRONMENT WITH PREDEFINED VARIABLES
// $DB, $LANG, $TPL, $Kernel

// load translation for this page
#$LANG->loadTranslation('admin');

// we need a module on demand, and we know that it isnt loaded by default
//$Kernel->modprobe('admin', ''); // ITS AUTOMATICALLY LOADED BY "MODULE ON DEMAND"

// the if is for performance... we dont want to create more arrays in memory...
//if ( is_object ($LANG))
//{
	$Kernel->hooks->defineHook('admin', 'modifyMenu', 'menu_translation');
//} else {
//	$Kernel->hooks->defineHook('admin', 'modifyMenu', 'menu_array');
//}

$LANG->loadTranslation('admin');

// ===== if admin is not logged in
if ( !$Kernel->admin->shva())
{
	// ===== if admin is now loging on, we must check if his login & password are correct
	if (isset($_POST['pa_first_input']))
	{
		$TPL -> assign ('login_state', 'login_information');	

		// get info from authorization library
		$LoginInfo = $Kernel -> admin -> login ( $_POST['pa_first_input'], $_POST['pa_second_input'] );

		if ( $LoginInfo == 'LIBADMIN_LOGGEDIN' )
		{
			//$TPL -> assign ('login_result', 'success'); // we are in!
			header('Location: ?page=' .$_GET['page']); // just fast redirect
		} else {
			$TPL -> assign ('login_result', 'fail');
			$TPL -> assign ('login_error', $LANG->admin[$LoginInfo]); // display correct translated message
		}

	} else {
		// ===== send login state to template to select "subpage"
		$TPL -> assign ('login_state', 'not_logged');	

		// ===== not logged in
		$TPL -> assign ('login_text', $LANG->admin['login_text'] );
		$TPL -> assign ('passwd_text', $LANG->admin['password_text'] );
		$TPL -> assign ('restricted_area', $LANG->admin['restricted_area']);
		$TPL -> assign ('login_submit', $LANG->admin['login_submit']);
	}
} else {
	// ===== logged in
	$TPL -> assign ('login_state', 'logged_in');
}

?>