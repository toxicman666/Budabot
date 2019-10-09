<?php

if (preg_match("/^editbasetopic ([0-9a-z]+) ([0-9]+) (.+)$/i", $message, $arr)) {
	$playfield_name = $arr[1];
	$site_number = $arr[2];
	$new_topic = str_replace("'","''",$arr[3]);
	
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
		$sql = "UPDATE tower_info SET topic='{$new_topic}', topic_by='{$sender}' WHERE playfield_id={$playfield->id} AND site_number={$site_number};";
		$db->exec($sql);
		
		$topic = "{$playfield->short_name} {$site_number} - {$new_topic}";
		$msg = "Updated base topic: {$topic}";
	} else {
		$msg = "Invalid site number.";
	}
	
	$chatBot->send($msg, $sendto);
} else if (preg_match("/^editbaserally ([0-9a-z]+) ([0-9]+) ([0-9]+) ([0-9]+)$/i", $message, $arr)) {
	$playfield_name = $arr[1];
	$site_number = $arr[2];
	$new_x_rally = $arr[3];
	$new_y_rally = $arr[4];
	
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
		$sql = "UPDATE tower_info SET x_rally='{$new_x_rally}', y_rally='{$new_y_rally}' WHERE playfield_id={$playfield->id} AND site_number={$site_number};";
		$db->exec($sql);
		
		$rally="Rally: <a href=\"text://<center><a href='chatcmd:///waypoint {$new_x_rally} {$new_y_rally} {$playfield->id}' style='text-decoration:none'><font color=CCInfoHeader>Get waypoint<br><font color=CCLinkColor><img src='rdb://11336'><br>{$new_x_rally}x{$new_y_rally}</font></a></center>\">{$new_x_rally}x{$new_y_rally}</a>";
		
		$msg = "Updated base rally: {$rally}";
	} else {
		$msg = "Invalid site number.";
	}
	
	$chatBot->send($msg, $sendto);
} else {
	$syntax_error = true;
}

?>