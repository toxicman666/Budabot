<?php

if (preg_match("/^topic$/i", $message, $arr)) {
	$date_string = Util::unixtime_to_readable(time() - Setting::get('topic_time'), false);
	$topic=Setting::get('topic');
	if ($topic == '') {
		$topic = 'No topic set';
	}
	$rally = Setting::get('rally');
	if ($rally!=""){
		$rally_arr=explode(' ',$rally);
		$rally="(Rally: <a href=\"text://<center><a href='chatcmd:///waypoint {$rally_arr[1]} {$rally_arr[2]} {$rally_arr[0]}' style='text-decoration:none'><font color=CCInfoHeader>Get waypoint<br><font color=CCLinkColor><img src='rdb://11336'><br>{$rally_arr[1]}x{$rally_arr[2]}</font></a></center>\">{$rally_arr[1]}x{$rally_arr[2]}</a>) ";
	}
	if(!isset($chatBot->data["leader"])||$chatBot->data["leader"]=="") $leader = "none";
	else $leader = "<yellow>" . $chatBot->data["leader"] . "<end>";	
	$msg = "<highlight>Topic:<end> {$topic} {$rally}[set by <highlight>{$chatBot->settings["topic_setby"]}<end>] [Leader - {$leader}] [<highlight>{$date_string} ago<end>]";
    $chatBot->send($msg, $sendto);
	
	if (!isset($chatBot->data["BASIC_CHAT_MODULE"]["orders"]))
		$chatBot->data["BASIC_CHAT_MODULE"]["orders"] = Setting::get("orders");
	if (!empty($chatBot->data["BASIC_CHAT_MODULE"]["orders"]))
		$chatBot->send("<red>[ORDERS]<end> <yellow>{$chatBot->data["BASIC_CHAT_MODULE"]["orders"]}<end>",$sendto);
		
} else {
	$syntax_error = true;
}

?>