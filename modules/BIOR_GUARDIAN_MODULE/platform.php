<?php
if (preg_match("/^platform$/i", $message, $arr)) {
	$msg="<red>EVERYONE RUSH TO THE PLATFORM! ALL THE WAY OVER THE BRIDGE!!!<end>";
	$chatBot->send($msg,'priv');
	$chatBot->send($msg,'priv');
	$chatBot->send($msg,'priv');
	$orders = "T-room, platform";
	Setting::save("orders",$orders);
	$chatBot->data["BASIC_CHAT_MODULE"]["orders"] = $orders;
} else {
	$syntax_error = true;
}	