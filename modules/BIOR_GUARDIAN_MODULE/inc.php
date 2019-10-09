<?php
if (preg_match("/^inc$/i", $message, $arr)) {
	$msg="<yellow>GET READY, CLANNERS INCOMING!!!<end>";
	$chatBot->send($msg,'priv');
	$chatBot->send($msg,'priv');
	$chatBot->send($msg,'priv');
} else {
	$syntax_error = true;
}	