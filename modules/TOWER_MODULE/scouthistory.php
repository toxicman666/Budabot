<?php

if (preg_match("/^scouthistory ([0-9a-z]+) ([0-9]+)$/i", $message, $arr)) {
	$playfield_name = $arr[1];
	$site_number = $arr[2];
	
	$playfield = Playfields::get_playfield_by_name($playfield_name);
	if ($playfield === null) {
		$msg = "Invalid playfield.";
		$chatBot->send($msg, $sendto);
		return;
	}
	
	$tower_info = Towers::get_tower_info($playfield->id, $site_number);
	if ($tower_info === null) {
		$msg = "Invalid site number.";
		$chatBot->send($msg, $sendto);
		return;
	}
	
	$db->query("SELECT * FROM scout_info_history WHERE playfield_id = {$playfield->id} AND site_number = {$site_number} ORDER BY id DESC LIMIT 10");
	if ($db->numrows() === 0) {
		$msg = "No scout history entries are available.";
	} else {
		$blob = "<highlight>Last 10 scouts for $playfield_name $site_number:<end>";
		while (($row = $db->fObject()) != FALSE) {
			$blob.= "\n\n" . $row->scouted_by . " updated\n<highlight>on " . $row->scouted_on . "<end>";
			$hours= floor($row->close_time/3600);
			$minutes= floor(($row->close_time%3600)/60);
			if ($minutes<10) $minutes= "0".$minutes;
			$seconds= ($row->close_time)%60;
			if ($seconds<10) $seconds= "0".$seconds;
			$blob.= "\n" . $hours . ":" . $minutes . ":" . $seconds . " " . $row->ct_ql . " " . $row->guild_name;
		}
		$msg = Text::make_link("Scout history for $playfield->short_name $site_number", $blob, 'blob');
	}
	
	$chatBot->send($msg, $sendto);
} else if (preg_match("/^scouthistory$/i", $message, $arr)) {
	
	$db->query("SELECT * FROM scout_info_history sc JOIN playfields p ON sc.playfield_id=p.id ORDER BY sc.id DESC LIMIT 30;");
	if ($db->numrows() === 0) {
		$msg = "No scout history entries are available.";
	} else {
		$blob = "<highlight>Last 30 scouts<end>";
		while (($row = $db->fObject()) != FALSE) {
			$blob.= "\n\n" . $row->scouted_by . " updated <highlight>" . $row->short_name . " " . $row->site_number . "\non " . $row->scouted_on . "<end>";
			$hours= floor($row->close_time/3600);
			$minutes= floor(($row->close_time%3600)/60);
			if ($minutes<10) $minutes= "0".$minutes;
			$seconds= ($row->close_time)%60;
			if ($seconds<10) $seconds= "0".$seconds;
			$blob.= "\n" . $row->short_name . " " . $row->site_number . " " . $hours . ":" . $minutes . ":" . $seconds . " " . $row->ct_ql . " " . $row->faction . " " . $row->guild_name;
		}
		$msg = Text::make_link("Scout history", $blob, 'blob');
	}
	
	$chatBot->send($msg, $sendto);	
} else {
	$syntax_error = true;
}

?>