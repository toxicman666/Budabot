<?php
   
if (Setting::get('topic') == '') {
	return;
}

if ($type == 'joinPriv' || ($type == 'logOn' && isset($chatBot->guildmembers[$sender]) && $chatBot->is_ready())) {
	$date_string = Util::unixtime_to_readable(time() - $chatBot->settings["topic_time"], false);
	if(!isset($chatBot->data["leader"])||$chatBot->data["leader"]=="") $leader = "none";
	else $leader = "<yellow>" . $chatBot->data["leader"] . "<end>";	
	$msg = "<highlight>Topic:<end> {$chatBot->settings["topic"]} [set by <highlight>{$chatBot->settings["topic_setby"]}<end>] [Leader - {$leader}] [<highlight>{$date_string} ago<end>]";
    $chatBot->send($msg, $sender);
	
	if (!isset($chatBot->data["BASIC_CHAT_MODULE"]["orders"]))
		$chatBot->data["BASIC_CHAT_MODULE"]["orders"] = Setting::get("orders");
	if (!empty($chatBot->data["BASIC_CHAT_MODULE"]["orders"]))
		$chatBot->send("<red>[ORDERS]<end> <yellow>{$chatBot->data["BASIC_CHAT_MODULE"]["orders"]}<end>",$sender);
}

?>