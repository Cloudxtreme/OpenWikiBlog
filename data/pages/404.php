<?php
// THIS IS A SPECIAL ENVIRONMENT WITH PREDEFINED VARIABLES
// $DB, $LANG, $TPL, $Kernel

// load translation for this page
$LANG->loadTranslation('notfound');

$TPL -> assign ('err_header', $LANG->notfound['err_header'] );
$TPL -> assign ('err_text', $LANG->notfound['err_text'] );
?>
