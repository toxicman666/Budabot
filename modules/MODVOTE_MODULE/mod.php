<?php

if (preg_match("/^mod (taratime|warbot) ([a-z0-9-]+)$/i", $message, $arr)) {
	$bot = ucfirst(strtolower($arr[1]));
	$newmod = ucfirst(strtolower($arr[2]));
	$whoismod = Player::get_by_name($newmod);
	if ($whoismod === null) {
	    $msg = "<orange>Unable to get '$newmod' character info. Please check name and try again.<end>";
	    $chatBot->send($msg, $sender);
	    return;
	}
	$main = Alts::get_main($sender);
	$whois = Player::get_by_name($main);
	if ($whois === null) {
	    $msg = "<orange>Unable to get your main ($main) character info. Please try again later.<end>";
	    $chatBot->send($msg, $sender);
	    return;
	}
	
	if ($whoismod->guild != $whois->guild){
	    $msg = "<orange>Your main must be in $newmod's org to assign him as a mod.<end>";
	    $chatBot->send($msg, $sender);
	    return;	
	}
	
	$fromorg = null;
	$entrants = Modvote::get_entrants($bot);
	foreach ($entrants as $entrant){
		if ($entrant["org"]==$whoismod->guild) {
			$fromorg = $entrant;
			continue;
		}
	}
	if ($fromorg!==null){ // if that org already applied mod for that bot
		if ($whois->guild_rank_id==0 || $fromorg["sponsor"]==$main) {
			Modvote::add_entrant($bot,$whoismod->guild,$newmod,$main);
			$chatBot->send("<highlight>{$fromorg["name"]}<end> was replaced with <highlight>{$newmod}<end> to <highlight>{$bot}<end> moderators vote",$sendto);
			$vote_end = intval(Setting::get('mod_vote_end_time'));
			if($vote_end>time() && $vote_end-time()<259200) {			
				$chatBot->send("<yellow>{$newmod} was added to vote for {$bot} moderator by {$main}<end>",'priv');
				Modvote::display_vote();
			}
		} else {
			$chatBot->send("<orange>Only a previous sponsor or org leader can change moderators for your org.<end>",$sendto);
		}
	} else { // add new w/o check
		Modvote::add_entrant($bot,$whoismod->guild,$newmod,$main);
		$chatBot->send("Added <highlight>{$newmod}<end> to <highlight>{$bot}<end> moderators vote",$sendto);
		$vote_end = intval(Setting::get('mod_vote_end_time'));
		if($vote_end>time() && $vote_end-time()<259200) {
			$chatBot->send("<yellow>{$newmod} was added to vote for {$bot} moderator by {$main}<end>",'priv');
			Modvote::display_vote();
		}
	}
} else if (preg_match("/^modclear (taratime|warbot)$/i", $message, $arr) || preg_match("/^modclear (taratime|warbot) (.+)$/i", $message, $arr)) {
	$bot = ucfirst(strtolower($arr[1]));
	$admin = false;
	if ($arr[2]) {
		if ($chatBot->admins[$sender]["level"] >= 3) {
			$whois->guild = $arr[2];
			$admin = true;
		} else {
			$chatBot->send("<orange>Only admins can clear mod applicants for other orgs.<end>",$sendto);
			return;
		}
	} else {
		$main = Alts::get_main($sender);
		$whois = Player::get_by_name($main);
		if ($whois === null) {
			$msg = "<orange>Unable to get your main ($main) character info. Please try again later.<end>";
			$chatBot->send($msg, $sender);
			return;
		}
	}
	$fromorg = null;
	$entrants=Modvote::get_entrants($bot);
	foreach ($entrants as $entrant){
		if ($entrant["org"]==$whois->guild) {
			$fromorg = $entrant;
			continue;
		}
	}
	
	if ($fromorg!==null){ // if that org already applied mod for that bot
		if ($whois->guild_rank_id==0 || $fromorg["sponsor"]==$main || $admin) {
			Modvote::add_entrant($bot,$whois->guild);
			$n=$fromorg["name"];
			$chatBot->send("<highlight>{$n}<end> was removed from <highlight>{$bot}<end> moderators vote for <highlight>$whois->guild<end>",$sendto);			
		} else {
			$chatBot->send("<orange>Only a previous sponsor or org leader can change moderators for your org.<end>",$sendto);
		}
	} else { // add new w/o check
		$chatBot->send("There is no moderator assigned for <highlight>{$whois->guild}<end>",$sendto);
	}
} else {
	$chatBot->send("usage: !mod 'bot' 'new_mod'",$sendto);
}

?>