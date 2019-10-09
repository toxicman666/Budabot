<?php
if (preg_match("/^troom$/i", $message, $arr)) {
	$msg="<yellow>EVERYONE RUSH TARA ROOM!!!<end>";
	$chatBot->send($msg,'priv');
	$chatBot->send($msg,'priv');
	$chatBot->send($msg,'priv');
	$orders = "Tara room, lair";
	Setting::save("orders",$orders);
	$chatBot->data["BASIC_CHAT_MODULE"]["orders"] = $orders;
} else if (preg_match("/^2troom$/i", $message, $arr)) {
	$msg="<yellow>SECOND ROOM BEFORE T-ROOM!!!<end>";
	$chatBot->send($msg,'priv');
	$chatBot->send($msg,'priv');
	$chatBot->send($msg,'priv');
	$orders = "2nd room before troom";
	Setting::save("orders",$orders);
	$chatBot->data["BASIC_CHAT_MODULE"]["orders"] = $orders;
} else if (preg_match("/^100$/i", $message, $arr)) {
	$msg="<yellow>EVERYBODY TO 100%<end>";
	$chatBot->send($msg,'priv');
	$chatBot->send($msg,'priv');
	$chatBot->send($msg,'priv');
	$orders = "Gather in 100%";
	Setting::save("orders",$orders);
	$chatBot->data["BASIC_CHAT_MODULE"]["orders"] = $orders;
} else {
	$syntax_error = true;
}	