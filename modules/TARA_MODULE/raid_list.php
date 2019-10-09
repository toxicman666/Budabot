<?php

if (preg_match("/^raidlist$/i", $message)) {
	$topic=Setting::get("raid_topic");
	if ($topic == ""){
		$chatBot->send("No raid in progress.",$sendto);
		return;	
	}

	$db = DB::get_instance();
	$db->query("SELECT * FROM tara_raidlist ORDER BY name ASC;");
	if ($db->numrows()===0){
		$chatBot->send("Raidlist is empty.",$sendto);
		return;
	}
	$status=Setting::get("raid_status");
	if ($status==2) $closed = "<red> [Closed]<end>";
	else $closed = "";
	$blob = "";
	$count=0;
	while ($row=$db->fObject()){
		$blob .= "<white>{$row->name}<end>";
		$count++;
		if ($row->category == 1) $blob .= " no points (too low)";
		else if ($row->category == 2) $blob .= " no pvp points, half regular points";
		else if ($row->category == 3) $blob .= " half points";
		$blob .= "\n";
	}
	$blob = "<header>:::: Raidlist ({$count})<end>{$closed}<header> ::::<end>\n\n" . $blob;	
	$msg = Text::make_link("Raidlist ({$count})",$blob,'blob') . $closed;
	$chatBot->send($msg,$sendto);
} else if (preg_match("/^raidadd all$/i", $message)) {
	$topic=Setting::get("raid_topic");
	if ($topic == ""){
		$chatBot->send("No raid in progress.",$sendto);
		return;	
	}
	$status = Setting::get("raid_status");
	if($status!=1){
		$chatBot->send("<orange>Raid is closed.<end>",$sendto);
		return;
	}
	$db->query("SELECT name FROM online_<myname> WHERE added_by = '<myname>' AND channel_type = 'priv'");
	$data = $db->fObject('all');
	$added=array();
	$limited=array();
	$error=array();
	forEach ($data as $row) {
		$inraid=Tara::in_raidlist($row->name);
		if ($inraid===false){
			$cat=Tara::raid_add($row->name);
			if($cat){
				$added[]=$row->name;
				if ($cat!=4) $limited[]=$row->name;
			} else {
				$error[]=$row->name;
			}
		} else if ($inraid!=$row->name) {
			$error[]=$row->name;
		}
	}
	$blob = "<header>:::: Added to Raidlist ::::<end>\n\n";
	foreach($added as $player){
		$blob .= "{$player}";
		if (in_array($player,$limited)) $blob .= " <red>limited points<end>";
		$blob .= "\n";
	}
	if (count($added)>0) $msg = Text::make_link(count($added) . " added",$blob,'blob') . "<yellow> to raidlist<end> by {$sender}";
	else $msg = "";
	if (count($error)>0){
		$msg .= " <orange>Error adding: <end><white>";
		foreach($error as $er){
			$msg .= "{$er} ";
		}
		$msg .= "<end>";
	}
	if ($msg =="") $msg = "Everyone is already added.";
	$chatBot->send($msg,'priv');
} else if (preg_match("/^raidadd ([a-z0-9-]+)$/i", $message, $arr)) {
	$topic = Setting::get("raid_topic");
	if ($topic == ""){
		$chatBot->send("No raid in progress.",$sendto);
		return;	
	}
	$name = ucfirst(strtolower($arr[1]));
	$status = Setting::get("raid_status");
	if($status!=1){
		if($status==0){
			$account = Tara::get_account_name($name);
			$raid_id = Tara::last_raid_id();
			$db->query("SELECT * FROM tara_points_history WHERE raid_id='{$raid_id}' AND account='{$account}';");
			if($db->numrows()==0){
				$msg = "<orange>Cannot add to raid: points were already given.<end>";
				$chatBot->send($msg, $sendto);
				return;
			}
		} else {
			$chatBot->send("<orange>Raid is closed.<end>",$sendto);
			return;
		}
	}
	
	$inraid=Tara::in_raidlist($name);
	if ($inraid!==false) {
		$msg = "<orange>{$name} is already in raidlist<end>";
		if($inraid!=$name) $msg .= " on {$inraid}";
		$chatBot->send($msg,$sendto);
		return;
	}
	$db->query("SELECT name FROM online_<myname> WHERE name='{$name}' AND added_by = '<myname>' AND channel_type = 'priv'");
	if ($db->numrows()===0){
		$chatBot->send("{$name} is not in channel.",$sendto);
		return;	
	}
	$cat = Tara::raid_add($name);
	if ($cat){
		$msg = "<yellow>{$name} was " . ($status==0?"<end>re<yellow>":"") . "added to raidlist<end>";
		if ($cat != 4 ) $msg .= " <red>[Limited points]<end>";
		$msg .= " by {$sender}";
	} else {
		$msg = "<orange>Failed adding {$name} to raidlist<end>";
	}
	$chatBot->send($msg,'priv');
	
} else if (preg_match("/^forceadd ([a-z0-9-]+)$/i", $message, $arr)) {
	$topic = Setting::get("raid_topic");
	if ($topic == ""){
		$chatBot->send("No raid in progress.",$sendto);
		return;	
	}

	$name = ucfirst(strtolower($arr[1]));
	$inraid=Tara::in_raidlist($name);
	if ($inraid!==false) {
		$msg = "<orange>{$name} is already in raidlist<end>";
		if($inraid!=$name) $msg .= " on {$inraid}";
		$chatBot->send($msg,$sendto);
		return;
	}
	
	$status = Setting::get("raid_status");
	if ($status == 0) {
		$account = Tara::get_account_name($name);
		$raid_id = Tara::last_raid_id();
		$db->query("SELECT * FROM tara_points_history WHERE raid_id='{$raid_id}' AND account='{$account}';");
		if($db->numrows()==0){
			$msg = "<orange>{$name} was not awarded points for this raid and cannot enter auctions.<end>";
			$chatBot->send($msg, $sendto);
			return;
		}
	}

	$cat = Tara::raid_add($name);
	if ($cat){
		$msg = "<yellow>{$name} was added to raidlist<end>";
		if ($cat != 4 ) $msg .= " <red>[Limited points]<end>";
			$msg .= " by {$sender}";
	} else {
		$msg = "<orange>Failed adding {$name} to raidlist<end>";
	}
	$chatBot->send($msg,'priv');
	
} else if (preg_match("/^raidkick$/i", $message)) {
	$topic=Setting::get("raid_topic");
	if ($topic == ""){
		$chatBot->send("No raid in progress.",$sendto);
		return;	
	}
	$inraid = Tara::in_raidlist($sender);
	if ($inraid===false || $inraid!=$sender) {
		$chatBot->send("You are not in raidlist.",$sender);
		return;
	}
	$data = $db->fObject('all');
	unset($chatBot->data["TARA_MODULE"]["raidlist"][$sender]);
	$db->exec("DELETE FROM tara_raidlist WHERE name LIKE '{$sender}';");
	$chatBot->send("<yellow>{$sender} has kicked himself from raidlist<end>",'priv');
	if ($sendto!='prv') $chatBot->send("<yellow>You have removed yourself from raidlist<end>.",$sender);
} else if (preg_match("/^raidkick ([a-z0-9- ]+)$/i", $message, $arr)) {
	$topic=Setting::get("raid_topic");
	if ($topic == ""){
		$chatBot->send("No raid in progress.",$sendto);
		return;	
	}
	$name = ucfirst(strtolower($arr[1]));
	$inraid = Tara::in_raidlist($name);
	if ($inraid===false || $inraid!=$name) {
		$chatBot->send("{$name} is not in raidlist.",$sendto);
		return;
	}

	unset($chatBot->data["TARA_MODULE"]["raidlist"][$name]);
	$db->exec("DELETE FROM tara_raidlist WHERE name LIKE '{$name}';");
	$chatBot->send("<yellow>{$name} was kicked from raidlist<end> by {$sender}",'priv');
} else if (preg_match("/^check$/i", $message)) {
	$topic=Setting::get("raid_topic");
	if ($topic == ""){
		$chatBot->send("No raid in progress.",$sendto);
		return;	
	}
	$list = "<header>::::: Raid Check :::::<end>\n\n";
	$db->query("SELECT name FROM tara_raidlist");
	if ($db->numrows()===0) {
		$chatBot->send("Raidlist is empty.",$sendto);
		return;
	}
	$data = $db->fObject('all');
	forEach ($data as $row) {
		$content .= " \\n /assist $row->name";
	}

	$list .= "<a href='chatcmd:///text AssistAll: $content'>Click to check who is here</a>";
	$msg = Text::make_link("Raidlist Check", $list);
	$chatBot->send($msg, $sendto);
} else {
	$syntax_error = true;
}
