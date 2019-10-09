<?php

if ($type == "joinPriv") {
	$whois = Player::get_by_name($sender);
	
	$alts = Alts::get_alts_blob($sender);
	
	if ($whois !== null) {
		$count = 0;
		if (Setting::get('show_prof_in_sm')==1){
			$prof = "{$whois->level} {$whois->profession}";
			$count++;
		}
		if (Setting::get('show_org_in_sm')==1){
			$guild = $whois->guild;
			$count++;
		}
		if ($count>1) $separator = ", ";
		if ($alts !== null && (Setting::get('show_alts_in_sm')==1)) {
			$msg = "{$sender} (<highlight>{$prof}{$separator}{$guild}<end>) has joined. {$alts}";
		} else {
			$msg = "{$sender} (<highlight>{$prof}{$separator}{$guild}<end>) has joined.";
		}
	} else {
		if ($alts !== null) {
			$msg .= "$sender has joined. {$alts}";
		} else {
			$msg = "$sender has joined.";
		}
	}

	if ($chatBot->settings["guest_relay"] == 1) {
		$chatBot->send($msg, "guild", true);
	}
	$chatBot->send($msg, "priv", true);
	

}

?>