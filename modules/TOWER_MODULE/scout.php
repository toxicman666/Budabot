<?php
if (preg_match("/^(scout|forcescout) ([a-z0-9]+) ([0-9]+) ([0-9]{1,2}:[0-9]{2}:[0-9]{2}) ([0-9]+) ([a-z]+) (.*)$/i", $message, $arr)) {
	if (strtolower($arr[1]) == 'forcescout') {
		$skip_checks = true;
	} else {
		$skip_checks = false;
	}

	$playfield_name = $arr[2];
	$site_number = $arr[3];
	$closing_time = $arr[4];
	$ct_ql = $arr[5];
	$faction = ucfirst(strtolower($arr[6]));
	$guild_name = trim($arr[7]);
	
	if ($faction != 'Omni' && $faction != 'Neutral' && $faction != 'Clan') {
		$msg = "Valid values for faction are: 'Omni', 'Neutral', and 'Clan'.";
		$chatBot->send($msg, $sendto);
		return;
	}

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
	
	if ($ct_ql < $tower_info->min_ql || $ct_ql > $tower_info->max_ql) {
		$msg = "$playfield->short_name $tower_info->site_number can only accept ct ql of {$tower_info->min_ql}-{$tower_info->max_ql}";
		$chatBot->send($msg, $sendto);
		return;
	}
	
	$closing_time_array = explode(':', $closing_time);
	
	if($closing_time_array[0]>23 || $closing_time_array[1]>59 || $closing_time_array[2]>59 || 
		$closing_time_array[0]<0 || $closing_time_array[1]<0 || $closing_time_array[2]<0){
		$msg = "Closing time is invalid";
		$chatBot->send($msg,$sendto);
		return;
	}
	
	$closing_time_seconds = $closing_time_array[0] * 3600 + $closing_time_array[1] * 60 + $closing_time_array[2];
	
	if (!$skip_checks) {  // && $chatBot->settings['check_close_time_on_scout'] == 1
		$last_victory = Towers::get_last_victory($tower_info->playfield_id, $tower_info->site_number);
		if ($last_victory !== null) {
			$victory_time_of_day = $last_victory->win_time % 86400;
			if ($victory_time_of_day > $closing_time_seconds + 120) { 
				$victory_time_of_day -= 86400;        // add 2 minutes if funcom time isn't sync
			}

			if ($closing_time_seconds - $victory_time_of_day > 3600) {
				$check_blob .= "- <green>Closing time<end> The closing time you have specified is more than 1 hour after the site was destroyed.";
				$check_blob .= " Please verify that you are using the closing time and not the gas change time and that the closing time is correct.\n\n";
			}
		}
	}
	
	if (!$skip_checks) {  //  && $chatBot->settings['check_guild_name_on_scout'] == 1
		if (!Towers::check_guild_name($guild_name)) {
			$check_blob .= "- <green>Org name<end> The org name you entered has never attacked or been attacked.\n\n";
		}
	}
	
	if (!$skip_checks) {  // check if already is_current
		$db = DB::get_instance();
		$sql = "SELECT * FROM scout_info sc JOIN playfields p ON sc.playfield_id=p.id WHERE p.short_name LIKE '$playfield_name' AND sc.site_number=$site_number AND sc.is_current=1;";
		$db->query($sql);
		if($db->numrows()>0){
			$check_blob .= "- <green>Already scouted<end> The site you are trying to scout is already up to date.\n\n";
		}
	}
	
	if ($check_blob) {
		$check_blob = "<header>:::::: Scouting problems <end>\n\n" . $check_blob;
		$check_blob .= "Please correct these errors, or, if you are sure the values you entered are correct, use !forcescout to bypass these checks\n\nP.S. Forcescouts will not be included into statistics.";
		$msg = Text::make_link('Scouting problems', $check_blob, 'blob');
	} else {
		Towers::add_scout_site($playfield->id, $site_number, $closing_time_seconds, $ct_ql, $faction, $guild_name, $sender,$skip_checks);
		$msg = "Tower site has been updated successfully.";
		if($skip_checks) $msg .= " Forcescouts are not included into statistics.";
	}
	$chatBot->send($msg, $sendto);
} else {
	$chatBot->send("usage: !scout &lt;zone&gt; &lt;#&gt; &lt;close&gt; &lt;ct&gt; &lt;side&gt; &lt;org&gt;",$sendto);
}

?>
