<?php

if (preg_match("/^basetopic ([0-9a-z]+) ([0-9]+)$/i", $message, $arr) || preg_match("/^basetopic ([0-9a-z]+) ([0-9]+) (.+)$/i", $message, $arr)) {
	$playfield_name = $arr[1];
	$site_number = $arr[2];
	$additional_comment = $arr[3];
	
	$sql = "SELECT * FROM playfields WHERE `long_name` LIKE '{$playfield_name}' OR `short_name` LIKE '{$playfield_name}' LIMIT 1";
		
	$db->query($sql);	
	$playfield = $db->fObject();
	
	if ($playfield === null) {
		$msg = "Playfield '$playfield_name' could not be found";
		$chatBot->send($msg, $sendto);
		return;
	}

	$sql = "SELECT * FROM tower_site t1
			JOIN scout_info s ON (t1.playfield_id = s.playfield_id AND t1.site_number = s.site_number)
			JOIN tower_info t2 ON (t1.playfield_id = t2.playfield_id AND t1.site_number = t2.site_number)
			JOIN playfields p ON (t1.playfield_id = p.id)
			WHERE t1.playfield_id = $playfield->id AND t1.site_number = $site_number";

	$db->query($sql);
	if (($row = $db->fObject()) !== null) {
		$topic = "{$playfield->short_name} {$site_number}";
		
		if ($row->topic != '') {
			$topic .= ' - ' . $row->topic;
		}
		
		if (!is_null($row->x_rally) && !is_null($row->y_rally)) {
			$rally = "{$row->playfield_id} {$row->x_rally} {$row->y_rally}";
			Setting::save('rally', $rally);
			
			$rally_arr=explode(' ',$rally);
			$rally="(Rally: <a href=\"text://<center><a href='chatcmd:///waypoint {$rally_arr[1]} {$rally_arr[2]} {$rally_arr[0]}' style='text-decoration:none'><font color=CCInfoHeader>Get waypoint<br><font color=CCLinkColor><img src='rdb://11336'><br>{$rally_arr[1]}x{$rally_arr[2]}</font></a></center>\">{$rally_arr[1]}x{$rally_arr[2]}</a>) ";			
		} else {
			Setting::save('rally', "");
		}
		
		if ($additional_comment != '') {
			$topic .= " - $additional_comment";
		}
		
		Setting::save("topic_time", time());
		Setting::save("topic_setby", $sender);
		Setting::save("topic", $topic);
		
		$msg = "Updated topic: {$topic} {$rally} [set by <highlight>{$chatBot->settings["topic_setby"]}<end>]";
	} else {
		$msg = "Invalid site number.";
	}
	
	$chatBot->send($msg, $sendto);
	if($type!='priv') $chatBot->send($msg, 'priv');	
} else {
	$syntax_error = true;
}

?>