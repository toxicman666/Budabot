<?php

if (preg_match("/^unban (.+)$/i", $message, $arr)){
	$who = ucfirst(strtolower($arr[1]));
	$banned = Ban::is_banned($who);
	if (!$banned) {
		$chatBot->send("<red>Sorry the player you wish to remove doesn't exist or isn't on the banlist.", $sendto);
		return;
	} else if ($banned===1) {
		$chatBot->send("<orange>The player is banned by Warbot.<end>",$sender);
		return;
	}
		
	$success = Ban::remove($who,0,$sender);
	if ($success>0){
		$chatBot->send("You have unbanned <highlight>$who<end> from this bot.", $sendto);
		$chatBot->send("You have been unbanned from this bot by $sender.", $who);
	} else {
		$chatBot->send("<orange>Player is banned by Org.<end>", $sendto);
	}
} else if (preg_match("/^unbanorg (.+)$/i", $message, $arr)) {
	$who = ucwords(strtolower($arr[1]));
	$banned = Ban::is_banned($who,1,$sender);
	if (!$banned) {
		$chatBot->send("<red>Sorry the org you wish to remove doesn´t exist or isn´t on the banlist.", $sender);
		return;		  
	} else if ($banned===1) {
		$chatBot->send("<orange>The org is banned by Warbot.<end>",$sender);
		return;
	}
		
	Ban::remove($who,1);

	$chatBot->send("You have unbanned the org <highlight>$who<end> from this bot.", $sendto);
} else {
	$syntax_error = true;
}

?>