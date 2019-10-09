<?php

if (preg_match("/^(mypoints|points)$/i", $message, $arr)) {
	$points = Tara::get_points($sender);
	if ($points!==false) {
		$msg = "You have {$points} points.";
		$history = Tara::get_points_blob($sender);
		if ($history!==false) $msg .= " " . $history;
		if ($points<0) $msg .= " " . Text::make_link("Why?", "<header>:::: Negative points ::::<end>\n\n<orange>Points can go negative if you register an alt that has ever received www.omnihq.net 20 points bonus. This only happens if you registered multiple forum accounts, which is inappropriate. You will not be able to bid untill you get out of the debt.<end>", 'blob');
		$chatBot->send($msg,$sendto);
	} else $chatBot->send("You are not registered.",$sendto);	
} else if (preg_match("/^points ([a-z0-9-]+)$/i", $message, $arr)) {
	$name = ucfirst(strtolower($arr[1]));
//	if(((Setting::get("raid_status")==0)||isset($chatBot->data["TARA_MODULE"]["auction"])) && ($name!=$sender)){
//	if(isset($chatBot->data["TARA_MODULE"]["auction"]) && $name!=$sender){
//		$chatBot->send("<orange>Auction in progress, try again later.<end>",$sender);
//		$chatBot->send("<orange>Access denied, try again later.<end>",$sender);
//		return;
//	}
	$topic = Setting::get("raid_topic");
	if ($topic!="" && $name!=$sender){
		$chatBot->send("<orange>Raid in progress, try again later.<end>",$sendto);
		return;
	}
	$points = Tara::get_points($name);
	if ($points!==false) {
		$msg = "{$name} has {$points} points.";
		$history = Tara::get_points_blob($name);
		if ($history!==false) $msg .= " " . $history;
		if ($points<0) $msg .= " " . Text::make_link("Why?", "<header>:::: Negative points ::::<end>\n\n<orange>Points can go negative if a player registers an alt that has ever received www.omnihq.net 20 points bonus. This only happens if player registered multiple forum accounts, which is inappropriate. Person will not be able to bid untill he gets out of the debt.<end>", 'blob');
		$chatBot->send($msg,$sendto);
	} else $chatBot->send("{$name} is not registered.",$sendto);
} else {
	$syntax_error = true;
}

?>