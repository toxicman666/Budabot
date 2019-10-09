<?php

if (preg_match("/^scoutneeded$/i", $message, $arr)) {
	$sql = "SELECT * FROM scout_info s
		JOIN playfields p ON (s.playfield_id = p.id)
		WHERE s.is_current = 0
		ORDER BY p.long_name ASC, s.site_number ASC";

	$db->query($sql);
	if ($db->numrows() == 0) {
		$msg = "No bases require scouting.";
		$chatBot->send($msg, $sendto);
		return;
	}
	
	$blob = "<header>:::::: Bases which require scouting ::::::<end>\n<white>";
	$current_playfield_id = -1;
	$count;
	while (($row = $db->fObject()) !== null) {
		if ($current_playfield_id != $row->playfield_id) {
			$playfield_long_name = Text::make_link($row->long_name, "/tell <myname> lc $row->short_name", "chatcmd");
			$blob .= "\n$playfield_long_name ($row->short_name): ";
			$current_playfield_id = $row->playfield_id;
		}
		$blob .= "$row->site_number ";
		$count++;
	}
	
	$msg = Text::make_link("Bases which require scouting ($count)", $blob, 'blob');
	$chatBot->send($msg, $sendto);
} else {
	$syntax_error = true;
}

?>