<?php

$colorlabel = "<font color=#00DE42>";
$colorvalue = "<font color=#63AD63>";

$listcount = 20;
$page_label = 1;
$search = '';

if (preg_match("/^attacks (\\d+)$/i", $message, $arr) || preg_match("/^attacks$/i", $message, $arr)) {
	if (isset($arr[1])) {
		$page_label = $arr[1];
		if ($page_label < 1) {
			$msg = "You must choose a page number greater than 0";
			$chatBot->send($msg, $sendto);
			return;
		}
	}
} else if (preg_match("/^attacks ([a-z0-9]+) (\\d+) (\\d+)$/i", $message, $arr) || preg_match("/^attacks ([a-z0-9]+) (\\d+)$/i", $message, $arr)) {
	if (isset($arr[3])) {
		$page_label = $arr[3];
		if ($page_label < 1) {
			$msg = "You must choose a page number greater than 0";
			$chatBot->send($msg, $sendto);
			return;
		}
	}
	
	$playfield = Playfields::get_playfield_by_name($arr[1]);
	if ($playfield === null) {
		$msg = "Please enter a valid playfield.";
		$chatBot->send($msg, $sendto);
		return;
	}
	
	$tower_info = Towers::get_tower_info($playfield->id, $arr[2]);
	if ($tower_info === null) {
		$msg = "Invalid site number.";
		$chatBot->send($msg, $sendto);
		return;
	}

	$search = "WHERE a.`playfield_id` = {$tower_info->playfield_id} AND a.`site_number` = {$tower_info->site_number}";
} else if (preg_match("/^attacks org (.+) (\\d+)$/i", $message, $arr) || preg_match("/^attacks org (.+)$/i", $message, $arr)) {
	if (isset($arr[2])) {
		$page_label = $arr[2];
		if ($page_label < 1) {
			$msg = "You must choose a page number greater than 0";
			$chatBot->send($msg, $sendto);
			return;
		}
	}

	$value = str_replace("'", "''", $arr[1]);
	$search = "WHERE a.`att_guild_name` LIKE '$value' OR a.`def_guild_name` LIKE '$value'";
} else if (preg_match("/^attacks player (.+) (\\d+)$/i", $message, $arr) || preg_match("/^attacks player (.+)$/i", $message, $arr)) {
	if (isset($arr[2])) {
		$page_label = $arr[2];
		if ($page_label < 1) {
			$msg = "You must choose a page number greater than 0";
			$chatBot->send($msg, $sendto);
			return;
		}
	}
	
	$value = str_replace("'", "''", $arr[1]);
	$search = "WHERE a.`att_player` LIKE '$value'";
} else {
	$syntax_error = true;
	return;
}

$page = $page_label - 1;
$offset = $page*$listcount;
$sql = 
	"SELECT
		*
	FROM
		tower_attack a
		LEFT JOIN playfields p ON (a.playfield_id = p.id)
		LEFT JOIN tower_site s ON (a.playfield_id = s.playfield_id AND a.site_number = s.site_number)
	$search
	ORDER BY
		a.`time` DESC
	LIMIT
		$offset, $listcount";

$db->query($sql);

if ($db->numrows() == 0) {
	$msg = "No tower attacks found.";
} else {
	$list = "<header>::::: The last $listcount Tower Attacks (page $page_label) :::::<end>\n\n" . $colorvalue;

	while ($row = $db->fObject()) {
		$list .= $colorlabel."Time:<end> ".gmdate("M j, Y, G:i", $row->time)." (GMT)\n";
		if ($row->att_faction == '') {
			$att_faction = "unknown";
		} else {
			$att_faction = strtolower($row->att_faction);
		}

		if ($row->def_faction == '') {
			$def_faction = "unknown";
		} else {
			$def_faction = strtolower($row->def_faction);
		}

		if ($row->att_profession == 'Unknown') {
			$list .= $colorlabel."Attacker:<end> <{$att_faction}>{$row->att_player}<end> ({$row->att_faction})\n";
		} else if ($row->att_guild_name == '') {
			$list .= $colorlabel."Attacker:<end> <{$att_faction}>{$row->att_player}<end> ({$row->att_level}/<green>{$row->att_ai_level}<end> {$row->att_profession}) ({$row->att_faction})\n";
		} else {
			$list .= $colorlabel."Attacker:<end> {$row->att_player} ({$row->att_level}/<green>{$row->att_ai_level}<end> {$row->att_profession}) <{$att_faction}>{$row->att_guild_name}<end> ({$row->att_faction})\n";
		}
		
		$base = Text::make_link("{$row->short_name} {$row->site_number}", "/tell <myname> lc {$row->short_name} {$row->site_number}", 'chatcmd');
		$base .= " ({$row->min_ql}-{$row->max_ql})";

		$list .= $colorlabel."Defender:<end> <{$def_faction}>{$row->def_guild_name}<end> ({$row->def_faction})\n";
		$list .= "Site: $base\n\n";
	}
	$msg = Text::make_link("Tower Attacks", $list);
}

$chatBot->send($msg, $sendto);

?>
