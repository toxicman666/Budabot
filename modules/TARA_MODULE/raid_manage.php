<?php

if (preg_match("/^(raidstart|raidupdate|forceraid) ([a-z]+)$/i", $message, $arr)) {
	// check if raid running
	$topic = Setting::get("raid_topic");
	if (strcasecmp($arr[1],"forceraid") != 0){
		if (strcasecmp($arr[1],"raidupdate") != 0) {
			if ($topic!=""){
				$chatBot->send("Raid in progress. Use '!raidupdate' or '!raidend'",$sendto);
				return;
			}
		} else {
			if ($topic==""){
				$chatBot->send("No raid in progress. Use '!raidstart'",$sendto);
				return;
			}
			// update
		}
	}
	$raid_name = $arr[2];
	// get raid id
	$db->query("SELECT * FROM tara_raids WHERE short_name LIKE '{$raid_name}';");
	if($db->numrows()==0){
		$db->query("SELECT * FROM tara_raids;");
		$raids=array();
		while($row = $db->fObject()){
			$raids[]=$row;
		}
		// create blob
		$blob = "<header>:::: <myname> Raids ::::<end>\n\n";
		$blob .= "<green>name|points<end>\n";
		$blob .= "category 1|2|3|4\n";
		$blob .= "<highlight>(100-149|150-174|175-199|200+)<end>\n\n";
		foreach($raids as $raid){
			$blob .= "<highlight>{$raid->short_name}<end>   |{$raid->points_cat_1}|{$raid->points_cat_2}|{$raid->points_cat_3}|{$raid->points_cat_4}|{$raid_long_name}\n";
		}
		$chatBot->send("Invalid raid specified. (" . Text::make_link("Raids",$blob,'blob') . ")" ,$sendto);
		return;
	}
	$raid = $db->fObject();
	
	$spawntime = Tara::spawntime();
	if($spawntime->state==1)
		$tleft = $spawntime->manual-time();
	else
		$tleft = $spawntime->time-time();
			
	// check if it's too early (if not forceraid) or raid points were already given
	if (strcasecmp($arr[1],"forceraid")!=0 && (empty($topic) || Setting::get("raid_status")==0)){
		if ($raid->short_name == "tara" || $raid->short_name == "pvpwin" || $raid->short_name == "pvploss"){
			if($tleft>3600) {
				$chatBot->send("<orange>Unable to start <end><highlight>{$raid->short_name}<end><orange> raid until it's 1 hour to spawn. <end>(use !forceraid if tara has spawned sooner)",$sendto);
				return;
			}
		} else if ($raid->short_name == "eb" && !isset($chatBot->admins[$sender])) {
			$chatBot->send("Only listed raidleaders can start EB raid.",$sendto);
			return;
		}
	}
	

	
	// set topic/state
	Setting::save("raid_topic",$raid->short_name);
	$chatBot->data["TARA_MODULE"]["raid_topic"] = $raid->short_name;
	Setting::save("raid_topic_time",time());
	Setting::save("raid_topic_long",$raid->long_name);
	Setting::save("raid_status",1);
	$chatBot->data["TARA_MODULE"]["raid_status"] = 1;
	Setting::save("raid_by",$sender);
	
	$chatBot->send("<yellow>Updated topic: {$raid->long_name} (by {$sender})",'priv');

//	if ($raid->short_name == "eb" && strcasecmp($arr[1],"raidupdate")!=0) {
	if (strcasecmp($arr[1],"raidupdate")!=0 && strcasecmp($arr[1],"forceraid")!=0) {
		$msg = $raid->long_name;
		if($tleft >= 901 && $tleft < 1801 && $raid->short_name == "tara") {
			$warn = "Raid leader may close the raid in 15 minutes if there is no pvp.";
			$chatBot->data["TARA_MODULE"]["linknet"]=time();
			$chatBot->send("<yellow>$warn<end>",'priv');
			$msg .= " " . $warn;
		}
		$chatBot->send("spam $sender $msg", $this->settings['otspambot']);
		$orders = "100%, /lft tara";
		$chatBot->data["BASIC_CHAT_MODULE"]["orders"] = $orders;
		Setting::save("orders",$orders);
	}
	
	if (strcasecmp($arr[1],"forceraid")==0){
		if($raid->short_name == "tara" || $raid->short_name == "pvpwin" || $raid->short_name == "pvploss"){
			$spawn = time() + 299;
			Setting::save("tara_spawntime",$spawn);
			Setting::save("tara_spawntime_by",$sender);
			Setting::save("tara_spawntime_set",time());

			$str = Util::unixtime_to_readable($run_time);
			$msg = "Spawntime was set to <highlight>5 minutes<end> from now. (in order to return to old timer use !resetspawntime)";
			$chatBot->send($msg, 'priv');
			unset($chatBot->data["TARA_MODULE"]["spawntime"]);
			
			$msg = $raid->long_name;

			$warn = "";
			if($raid->short_name == "tara"){
				$warn = "Raid leader may close the raid in 5 minutes.";
				$chatBot->send("<yellow>$warn<end>",'priv');
				$warn = " " . $warn;
			}
			$chatBot->data["TARA_MODULE"]["linknet"]=time()-601;
			
			$msg .= " (Force started raid)" . $warn;

			$chatBot->send("spam $sender $msg", $this->settings['otspambot']);
		}
	}
	
	
} else if (preg_match("/^raidpoints$/i", $message)) {
	// check state
	$status = Setting::get("raid_status");
	if ($status == 0){
		$chatBot->send("<orange>Points were already given<end>",$sendto);
		return;
	}
	
	// check time (if not admin)
	if (!isset($chatBot->admins[$sender])){
		$spawntime = Tara::spawntime();
		$topic = Setting::get("raid_topic");
		if($spawntime->state==1)
			$tleft = $spawntime->manual - time();
		else
			$tleft = $spawntime->time - time();
		if ($topic == "tara" || $topic == "pvpwin" || $topic == "pvploss"){
			if($tleft>0) {
				$chatBot->send("Unable to give points for <highlight>{$topic}<end> raid until Tarasque spawned.",$sendto);
				return;
			}
		}
	}	
	// award points + record points history
	Setting::save("raid_status",0);
	$chatBot->data["TARA_MODULE"]["raid_status"] = 0;
	$msg = Tara::award_points(Setting::get("raid_topic"),$sender);
	$chatBot->send($msg,'priv');
	
	$spawntime = Tara::spawntime();
	if($spawntime->state == 0)
		$chatBot->data["TARA_MODULE"]["spawntime"] = $spawntime->time;
	else 
		$chatBot->data["TARA_MODULE"]["spawntime"] = $spawntime->manual;
	
	unset($chatBot->data["TARA_MODULE"]["linknet"]);

} else if (preg_match("/^raidclose$/i", $message)) {
	$topic = Setting::get("raid_topic");
	if ($topic==""){
		$chatBot->send("No raid in progress.",$sendto);
		return;
	}
	$status = Setting::get("raid_status");
	if($status==1){
		// check if it's too early 
		$spawntime = Tara::spawntime();
		if($spawntime->state==1)
			$tleft = $spawntime->manual-time();
		else
			$tleft = $spawntime->time-time();
		if ($topic == "tara"){
			if($tleft>900) {
				$chatBot->send("Unable to close the raid until it's 15 minutes to spawn.",$sendto);
				return;
			}
		} else if ($topic == "pvpwin" || $topic == "pvploss") {
			$chatBot->send("Cannot close PVP raid.",$sendto);
			return;
		} else if ($topic == "eb") {
			$chatBot->send("Cannot close EB raid.",$sendto);
			return;
		}
		
		if (!isset($chatBot->data["TARA_MODULE"]["linknet"])){
			$chatBot->send("Cannot close the raid. There was no Linknet notification.",$sendto);
			return;
		} else if (time() - $chatBot->data["TARA_MODULE"]["linknet"] < 899) {
			$chatBot->send("Cannot close the raid until " . Util::unixtime_to_readable(900 - (time() - $chatBot->data["TARA_MODULE"]["linknet"])) . " from now (Have to wait 15 minutes after Linknet notification).",$sendto);
			return;
		}
		
		Setting::save("raid_status",2);
		$chatBot->data["TARA_MODULE"]["raid_status"] = 2;
		$msg = "<yellow>Raid was closed by {$sender}<end>";
	} else {
		$msg = "Raid is already closed.";
	}
	$chatBot->send($msg,'priv');
} else if (preg_match("/^raidopen$/i", $message)) {
	$topic = Setting::get("raid_topic");
	if ($topic==""){
		$chatBot->send("No raid in progress.",$sendto);
		return;
	}
	$status = Setting::get("raid_status");
	if($status==2){
		Setting::save("raid_status",1);
		$chatBot->data["TARA_MODULE"]["raid_status"] = 1;
		$msg = "<yellow>Raid was reopened by {$sender}<end>";
	} else if ($status==0){
		$msg = "Raid cannot be opened after awarding points.";
	} else {
		$msg = "Raid is not closed.";
	}
	$chatBot->send($msg,'priv');	
} else if (preg_match("/^raidend$/i", $message)) {
	// check if there's raid running
	$topic = Setting::get("raid_topic");
	if ($topic==""){
		$chatBot->send("No raid in progress.",$sendto);
		return;
	}
	// clean topic/state
	Setting::save("raid_topic","");
	$chatBot->data["TARA_MODULE"]["raid_topic"] = "";
	Setting::save("raid_topic_long","");
	Setting::save("raid_status",0);
	$chatBot->data["TARA_MODULE"]["raid_status"] = 0;
	Setting::save("raid_by",$sender);
	Setting::save('assist', '');
	Setting::save('teamassist', '');	
	Setting::save('healassist', '');	
	$chatBot->data["BASIC_CHAT_MODULE"]["orders"] = "";
	Setting::save("orders","");
	
	// clean raidlist
	Tara::clean_raidlist();
	$chatBot->send("<yellow>Topic removed by <end><highlight>{$sender}<end>",'priv');
	$chatBot->send("<yellow>Raid ended.<end>",'priv');	
} else if (preg_match("/^raids$/i", $message)){
		$db->query("SELECT * FROM tara_raids;");
		$raids=array();
		while($row = $db->fObject()){
			$raids[]=$row;
		}
		// create blob
		$blob = "<header>:::: <myname> Raids ::::<end>\n\n";
		$blob .= "<green>name|points<end>\n";
		$blob .= "category 1|2|3|4\n";
		$blob .= "<highlight>(100-149|150-174|175-199|200+)<end>\n\n";
		foreach($raids as $raid){
			$blob .= "<highlight>{$raid->short_name}<end>   |{$raid->points_cat_1}|{$raid->points_cat_2}|{$raid->points_cat_3}|{$raid->points_cat_4}|{$raid_long_name}\n";
		}
		$chatBot->send(Text::make_link("<myname> Raids",$blob,'blob'),$sendto);
} else {
	$syntax_error = true;
}

?>