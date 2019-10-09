<?php
if (preg_match("/^box$/i", $message, $arr)) {
	$msg="<yellow>EVERYONE TO BOX ROOM!!!<end>";
	$chatBot->send($msg,'priv');
	$chatBot->send($msg,'priv');
	$chatBot->send($msg,'priv');

	$orders = "Box room";
	Setting::save("orders",$orders);
	$chatBot->data["BASIC_CHAT_MODULE"]["orders"] = $orders;
} else {
	$syntax_error = true;
}	