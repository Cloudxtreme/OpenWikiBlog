<?php
// THIS IS A SPECIAL ENVIRONMENT WITH PREDEFINED VARIABLES
// $DB, $LANG, $TPL

// load translation for this page
$LANG->loadTranslation('hwtc');

$TPL -> assign ('text_header', $LANG->hwtc['text_header'] );
$TPL -> assign ('tip1', $LANG->hwtc['tip1'] );
$TPL -> assign ('tip2', $LANG->hwtc['tip2'] );
$TPL -> assign ('tip3', $LANG->hwtc['tip3'] );
?>
