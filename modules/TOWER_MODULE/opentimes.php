<?php


if (preg_match("/^(opentimes|opentimesomni) (\\d+)$/i", $message, $arr) || (preg_match("/^(opentimes|opentimesomni) ([0-9a-z]+)$/i", $message, $arr)) || preg_match("/^(opentimes|opentimesomni) (\\d+) (\\d+)$/i", $message, $arr) || preg_match("/^(opentimes|opentimesomni)$/i", $message, $arr)) {


	$title_pf="";
	if (!$arr[2] || is_numeric($arr[2])){
		if($arr[2]){
			$lowql = $arr[2];
			if ($arr[3]) {
				$highql = $arr[3];
			} else {
				$highql = $arr[2];
			}

			if ($highql<$lowql || $highql>300 || $lowql>300 || $highql<1 || $lowql<1){
				$msg = "<font color=#FFFF00>open:: Check levels</font>";
				$chatBot->send($msg,$sendto);
				return;
			}
		} else {
			if(Setting::get('server_tl7')==1){
				$lowql = 200;
				$highql = 300;
			} else {
				$lowql = 1;
				$highql = 200;
			}
		}
	} else {
		$playfield = Playfields::get_playfield_by_name($arr[2]);
			if ($playfield === null) {
			$msg = "Playfield '" . $arr[2] . "' could not be found";
			$chatBot->send($msg, $sendto);
			return;
		}
		$lowql = 1;
		$highql = 300;
		$playfield_sql = "AND t.playfield_id = $playfield->id";
		$title_pf = " in " . $playfield->short_name;
	}
	$db = DB::get_instance();	
	
	if (strtolower($arr[1]) == 'opentimesomni') {
		$title = "Scouted Omni {$lowql}-{$highql} bases{$title_pf}";
		$side_sql = "AND (s.faction = 'Omni' OR s.faction = 'Neut')";
	} else {
		$title = "Scouted Clan {$lowql}-{$highql} bases{$title_pf}";
		$side_sql = "AND (s.faction = 'Clan')";
	}
	
	$openTimeSql = getOpenTimeSql(time() % 86400);
	
	$sql = "
		SELECT
			*
		FROM
			tower_site t
			JOIN scout_info s ON (t.playfield_id = s.playfield_id AND s.site_number = t.site_number)
			JOIN playfields p ON (t.playfield_id = p.id)
		WHERE
			(s.ct_ql BETWEEN $lowql AND $highql)
			$side_sql
			$playfield_sql
		ORDER BY
			close_time";
	$db->query($sql);
	$numrows = $db->numrows();
	
	$blob = '';
	while (($row = $db->fObject()) != FALSE) {
		$gas_level = getGasLevel($row->close_time);
		$site_link = Text::make_link("$row->short_name $row->site_number", "/tell <myname> lc $row->short_name $row->site_number", "chatcmd");
		$gas_change_string = "$gas_level->color $gas_level->gas_level - $gas_level->next_state in " . gmdate('H:i:s', $gas_level->gas_change) . "<end>";
		
		$blob .= "$site_link <white>- {$row->min_ql}-{$row->max_ql}, $row->ct_ql CT, $row->guild_name,<end>$gas_change_string <white>[by $row->scouted_by]<end>";
		if ($row->is_current==0) $blob .= " <red>(Out of date!)<end>";
		$blob .= "\n";
	}
	
	if ($numrows > 0) {
		$msg = Text::make_link("{$title} ({$numrows})", $title . "\n\n" . $blob);
	} else {
		$msg = "No sites found.";
	}
	$chatBot->send($msg, $sendto);
} else {
	$syntax_error = true;
}
?>