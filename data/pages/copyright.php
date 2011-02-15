<?php
// THIS IS A SPECIAL ENVIRONMENT WITH PREDEFINED VARIABLES
// $DB, $LANG, $TPL

// load translation for this page
$LANG->loadTranslation('copyright');

$TPL -> assign ('text_header', $LANG->copyright['text_header'] );
$TPL -> assign ('items', $LANG->copyright['list'] );
$TPL -> assign ('about_header', $LANG->copyright['about_header'] );
$TPL -> assign ('about_text', $LANG->copyright['about_text'] );
$TPL -> assign ('container_style', 'background: url(websites/cube/images/screenshots/map1.png)');
?>
