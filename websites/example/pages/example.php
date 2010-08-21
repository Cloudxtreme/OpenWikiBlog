<?php
// THIS IS A SPECIAL ENVIRONMENT WITH PREDEFINED VARIABLES
// $DB, $LANG, $TPL

// load translation for this page
$LANG->loadTranslation('example');

$TPL -> assign ('text_header', $LANG->example['text_header'] );
$TPL -> assign ('text', $LANG->example['text'] );
?>
