<?php

if (preg_match("/^stats$/i", $message)) {
	$points = Tara::get_points($sender);
	if ($points!==false) {
		$account = Tara::get_account_name($sender);
		$stats = "<header>:::: {$account}'s Statistics ::::<end>";
		$stats .= "\n\nPoints: <yellow>{$points}<end> (" . Text::make_link("points","/tell <myname> !points {$account}",'chatcmd') . ")\n";
		$stats .= Tara::get_stats($account);
		
		$msg = Text::make_link("{$account}'s Statistics",$stats,'blob');
		$chatBot->send($msg,$sendto);
	} else $chatBot->send("You are not registered.",$sendto);	
} else if (preg_match("/^stats ([a-z0-9-]+)$/i", $message, $arr)) {
	$name = ucfirst(strtolower($arr[1]));
/*	if(isset($chatBot->data["TARA_MODULE"]["auction"]) && $name!=$sender){
		$chatBot->send("<orange>Auction in progress, try again later.<end>",$sender);
		return;
	}	*/
	$topic = Setting::get("raid_topic");
	if ($topic!="" && $name!=$sender){
		$chatBot->send("<orange>Raid in progress, try again later.<end>",$sendto);
		return;
	}
	$points = Tara::get_points($name);
	if ($points!==false) {
		$account = Tara::get_account_name($name);
		$stats = "<header>:::: {$account}'s Statistics ::::<end>";
		$stats .= "\n\nPoints: <yellow>{$points}<end> (" . Text::make_link("points","/tell <myname> !points {$account}",'chatcmd') . ")\n";
		$stats .= Tara::get_stats($account);
		
		$msg = Text::make_link("{$account}'s Statistics",$stats,'blob');
		$chatBot->send($msg,$sendto);
	} else $chatBot->send("{$name} is not registered.",$sendto);
} else if (preg_match("/^top$/i", $message) || preg_match("/^top points$/i", $message)) {
	$topic = Setting::get("raid_topic");
	if ($topic!=""){
		$chatBot->send("<orange>Raid in progress, try again later.<end>",$sendto);
		return;
	}
/*	if(isset($chatBot->data["TARA_MODULE"]["auction"]) && $name!=$sender){
		$chatBot->send("<orange>Auction in progress, try again later.<end>",$sender);
		return;
	}	*/
	$db = DB::get_instance();
	$db->query("SELECT name, IF(forums=1,points+20,points) as points FROM tara_points ORDER BY points DESC LIMIT 50;");
	if ($db->numrows()===0) {
		$chatBot->send("No users registered!",$sendto);
		return;
	}
	
	$blob = "";
	$i=1;
	while ($row=$db->fObject()){
		if ($row->forums==1) $row->points +=20;
		$blob .= "<highlight>" . $i++ . " {$row->name}<end> {$row->points}\n";
	}
	$db->query("SELECT COUNT(name) as count FROM tara_points WHERE forums=1;");
	$row=$db->fObject();
	$on_forums=$row->count;
	$db->query("SELECT COUNT(name) as count FROM tara_points;");
	$row=$db->fObject();
	$total_users=$row->count;
	
	$blob = "<header>:::: Top 50 <myname> points ::::<end>\n<yellow>::<end>Total: {$total_users} users, {$on_forums} on forums\n\n" . $blob;
	$msg = Text::make_link("Top 50 points",$blob,'blob');
	$chatBot->send($msg,$sendto);
} else if (preg_match("/^top (raids|raiders|stats|stat)$/i", $message)) {
	$topic = Setting::get("raid_topic");
	if ($topic!=""){
		$chatBot->send("<orange>Raid in progress, try again later.<end>",$sendto);
		return;
	}
	$db = DB::get_instance();
	$blob = "<header>:::: Top 25 <myname> statistics ::::<end>\n";
	
	$blob .= "\n<yellow>This month:<end>";
	$blob .= "\n<white>:name: :raids attended: :(%):<end>\n";
	$db->query("SELECT t.account AS name,count(t.id) AS raids_attended, (SELECT count(id) AS total_raids FROM tara_raid_history WHERE time>" . gmmktime(0,0,0,date('m'),1,date('Y')) . ") AS total_raids FROM tara_points_history t LEFT JOIN tara_raid_history r ON t.raid_id=r.id WHERE r.time>" . gmmktime(0,0,0,date('m'),1,date('Y')) . " AND t.item='' GROUP BY t.account ORDER BY raids_attended DESC LIMIT 25;");
	$i=1;
	while ($row=$db->fObject()){
		$blob .= "<highlight>" . $i++ . " {$row->name}<end> {$row->raids_attended} (" . round(100*$row->raids_attended/$row->total_raids) . "%)\n";
	}	

	$blob .= "\n<yellow>Since beginning:<end>";
	$blob .= "\n<white>:name: :raids attended:<end>\n";	
	$db->query("SELECT t.account AS name,count(t.id) AS raids_attended, (SELECT count(id) AS total_raids FROM tara_raid_history) AS total_raids FROM tara_points_history t LEFT JOIN tara_raid_history r ON t.raid_id=r.id WHERE t.item='' GROUP BY t.account ORDER BY raids_attended DESC LIMIT 25;");
	$i=1;
	while ($row=$db->fObject()){
		$blob .= "<highlight>" . $i++ . " {$row->name}<end> {$row->raids_attended}\n";
	}
	
	$blob .= "\n<yellow>Raid Leaders:<end>";
	$blob .= "\n<white>:name: :raids led:<end>\n";	
	$i=1;
	$db->query("SELECT IF(a.main IS NULL,h.leader,a.main) AS mainn, count(id) AS raid_count FROM tara_raid_history h LEFT JOIN alts a ON h.leader=a.alt OR (a.alt IS NULL AND h.leader IS NOT NULL) GROUP BY mainn ORDER BY raid_count DESC LIMIT 25;");
	while ($row=$db->fObject()){
		$blob .= "<highlight>" . $i++ . " {$row->mainn}<end> {$row->raid_count}\n";
	}	

	$msg = Text::make_link("Top statistics",$blob,'blob');
	$chatBot->send($msg,$sendto);	

} else {
	$syntax_error = true;
}

?>