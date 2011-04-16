<?php
session_start ();

#### Open Wiki Blog copyright by WebNuLL ( JID: webnull@ubuntu.pl )
#### This code is on GPLv3 license
#### Linux/Unix and Open Source/Free Software forever ;-)

$PATH="./";

include($PATH. 'core/subpages.so.php');
$Subpage = new Subpage($PATH);
$Subpage -> loadDefaultModules();
$stModules = $Subpage->getModules();
$Subpage -> modprobe(0, array( 'mypage', 'display', ''));
$subpage -> Kernel -> __destruct();
?>
