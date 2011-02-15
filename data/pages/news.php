<?php
// THIS IS A SPECIAL ENVIRONMENT WITH PREDEFINED VARIABLES
// $DB, $LANG, $TPL

// load translation for this page
$LANG->loadTranslation('news');

$TPL -> assign ('text_header', $LANG->news['text_header'] );
$TPL -> assign ('text', $LANG->news['text'] );
$TPL -> assign ('tuxplace_header', $LANG->news['tuxplace_header'] );
$TPL -> assign ('tuxplace_text', $LANG->news['tuxplace_text'] );
?>
