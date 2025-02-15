<?php

if (preg_match("/^The (Clan|Neutral|Omni) organization (.+) attacked the (Clan|Neutral|Omni) (.+) at their base in (.+). The attackers won!!$/i", $message, $arr)) {
	$win_faction = $arr[1];
	$win_guild_name = $arr[2];
	$lose_faction = $arr[3];
	$lose_guild_name = $arr[4];
	$playfield_name = $arr[5];
} else if (preg_match("/^Notum Wars Update: The (Clan|Neutral|Omni) organization (.+) lost their base in (.+).$/i", $message, $arr)) {
	$win_faction = '';
	$win_guild_name = '';
	$lose_faction = ucfirst(strtolower($arr[1]));
	$lose_guild_name = $arr[2];
	$playfield_name = $arr[3];
} else {
	return;
}
	
$playfield = Playfields::get_playfield_by_name($playfield_name);
if ($playfield === null) {
	Logger::log('error', 'Towers', "Could not find playfield for name '$playfield_name'");
	return;
}

$last_attack = Towers::get_last_attack($win_faction, $win_guild_name, $lose_faction, $lose_guild_name, $playfield->id);
if ($last_attack !== null) {
/*	$sql = "DELETE FROM scout_info WHERE `playfield_id` = {$last_attack->playfield_id} AND `site_number` = {$last_attack->site_number}";
	$db->exec($sql);  */
	$sql = "UPDATE scout_info SET `is_current` = 0 WHERE `playfield_id` = {$last_attack->playfield_id} AND `site_number` = {$last_attack->site_number} LIMIT 1";
} else {
	$last_attack = new stdClass;
	$last_attack->att_guild_name = $win_guild_name;
	$last_attack->def_guild_name = $lose_guild_name;
	$last_attack->att_faction = $win_faction;
	$last_attack->def_faction = $lose_faction;
	$last_attack->playfield_id = $playfield->id;
	$last_attack->id = NULL;
	$sql = "UPDATE scout_info SET `is_current` = 0 WHERE `playfield_id` = {$last_attack->playfield_id} AND `faction` = '{$last_attack->def_faction}' AND `guild_name` = '{$last_attack->def_guild_name}'";	
}
$db->exec($sql);

Towers::record_victory($last_attack);

?>
