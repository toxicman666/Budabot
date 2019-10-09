<?php
if (preg_match("/^os$/i", $message, $arr)) {
	$msg="<red>ORBITAL STRIKE GET OUT OF THE WAY!<end>";
	$chatBot->send($msg,'priv');
	$chatBot->send($msg,'priv');
	$chatBot->send($msg,'priv');
} else {
	$syntax_error = true;
}	