<?php
if (preg_match("/^as$/i", $message, $arr)) {
	$msg="<yellow>AIR STRIKE GET AWAY FROM THE FLAMES!<end>";
	$chatBot->send($msg,'priv');
	$chatBot->send($msg,'priv');
	$chatBot->send($msg,'priv');
} else {
	$syntax_error = true;
}	