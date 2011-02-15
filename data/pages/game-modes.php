<?php
// THIS IS A SPECIAL ENVIRONMENT WITH PREDEFINED VARIABLES
// $DB, $LANG, $TPL

// load translation for this page
$LANG->loadTranslation('gmodes');

$TPL -> assign ('text_header', $LANG->gmodes['text_header'] );
$TPL -> assign ('modes', $LANG->gmodes['modes'] );

?>
