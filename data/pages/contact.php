<?php
// THIS IS A SPECIAL ENVIRONMENT WITH PREDEFINED VARIABLES
// $DB, $LANG, $TPL

// load translation for this page
$LANG->loadTranslation('contact');

$TPL -> assign ('text_header', $LANG->contact['text_header'] );
$TPL -> assign ('contact_us', $LANG->contact['contact_us'] );
$TPL -> assign ('in_domain', $LANG->contact['in_domain'] );
$TPL -> assign ('instant_messagers', $LANG->contact['instant_messagers'] );
?>
