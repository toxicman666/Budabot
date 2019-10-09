<?php
if (preg_match("/^orders$/i", $message, $arr)) {
	if (!isset($chatBot->data["BASIC_CHAT_MODULE"]["orders"]))
		$chatBot->data["BASIC_CHAT_MODULE"]["orders"] = Setting::get("orders");
	if (!empty($chatBot->data["BASIC_CHAT_MODULE"]["orders"]))
		$chatBot->send("<red>[ORDERS]<end> <yellow>{$chatBot->data["BASIC_CHAT_MODULE"]["orders"]}<end>",$sendto);
	else
		$chatBot->send("<red>[ORDERS]<end> <yellow>none<end>",$sendto);
} else if (preg_match("/^orders clear$/i", $message, $arr)) {

	$chatBot->data["BASIC_CHAT_MODULE"]["orders"] = "";
	Setting::save("orders","");
	$msg = "<yellow>Orders cleared<end> by {$sender}";

	$chatBot->send($msg,'priv');
} else if (preg_match("/^orders (.+)$/i", $message, $arr)) {
	if (!empty($arr[1])) {
		$chatBot->data["BASIC_CHAT_MODULE"]["orders"] = $arr[1];
		Setting::save("orders",$arr[1]);
		$msg = "<red>[ORDERS]<end> <yellow>{$arr[1]}<end> by {$sender}";
		$chatBot->send($msg,'priv');
	} else {
		$msg = "Empty orders";
		$chatBot->send($msg,$sendto);
	}

} else {
	$syntax_error = true;
}	