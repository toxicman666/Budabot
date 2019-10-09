<?php

if (preg_match("/^topic$/i", $message, $arr)) {

	$raid_topic = Setting::get('raid_topic_long');
	$closed = "";
	if($raid_topic!="") {
		$topic = "<yellow>" . $raid_topic . "<end>";
		$date_string = Util::unixtime_to_readable(time() - Setting::get('raid_topic_time'), false);	
		$by = Setting::get("raid_by");
		$status=Setting::get("raid_status");
		if ($status==2) $closed = "<red> [Closed]<end>";
	} else {
		$topic=Setting::get('topic');
		$date_string = Util::unixtime_to_readable(time() - Setting::get('topic_time'), false);
		$by = Setting::get("topic_setby");
	}
	if(!isset($chatBot->data["leader"])||$chatBot->data["leader"]=="") $leader = "none";
	else $leader = "<yellow>" . $chatBot->data["leader"] . "<end>";
	
	if ($topic == '') {
		$topic = 'No topic set';
	}
	
	
	$msg = "<highlight>Topic:<end> {$topic}{$closed} [set by <highlight>{$by}<end>] [Leader - {$leader}] [<highlight>{$date_string} ago<end>]";
    $chatBot->send($msg, $sendto);

	if (!isset($chatBot->data["BASIC_CHAT_MODULE"]["orders"]))
		$chatBot->data["BASIC_CHAT_MODULE"]["orders"] = Setting::get("orders");
	if (!empty($chatBot->data["BASIC_CHAT_MODULE"]["orders"]))
		$chatBot->send("<red>[ORDERS]<end> <yellow>{$chatBot->data["BASIC_CHAT_MODULE"]["orders"]}<end>",$sendto);
	
} else {
	$syntax_error = true;
}

?>