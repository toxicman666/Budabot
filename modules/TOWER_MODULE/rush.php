<?php
if (preg_match("/^rush$/i", $message, $arr)) {
	$msg="<red>RUSH THE CLAMMERS! RUSH, RUSH, RUSH!!!<end>";
	$chatBot->send($msg,'priv');
	$chatBot->send($msg,'priv');
	$chatBot->send($msg,'priv');
} else {
	$syntax_error = true;
}	