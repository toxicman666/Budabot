<?php
   /*
   ** Author: Sebuda (RK2)
   ** Description: Adds a Player to the banlist
   ** Version: 0.1
   **
   ** Developed for: Budabot(http://sourceforge.net/projects/budabot)
   **
   ** Date(created): 01.10.2005
   ** Date(last modified): 21.11.2006
   **
   ** Copyright (C) 2005, 2006 J Gracik
   **
   ** Licence Infos:
   ** This file is part of Budabot.
   **
   ** Budabot is free software; you can redistribute it and/or modify
   ** it under the terms of the GNU General Public License as published by
   ** the Free Software Foundation; either version 2 of the License, or
   ** (at your option) any later version.
   **
   ** Budabot is distributed in the hope that it will be useful,
   ** but WITHOUT ANY WARRANTY; without even the implied warranty of
   ** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   ** GNU General Public License for more details.
   **
   ** You should have received a copy of the GNU General Public License
   ** along with Budabot; if not, write to the Free Software
   ** Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
   */
if ($chatBot->vars["name"]=="Warleaders" && (!isset($chatBot->admins[$sender])||(int)$chatBot->admins[$sender]["level"]<4)){
	$chatBot->send("<orange>Cannot ban from this bot. Use !remuser<end>", $sendto);
	return;
}

if (preg_match("/^(quickban|fastban) ([a-z0-9-]+)$/i", $message, $arr)) {
	$who = ucfirst(strtolower($arr[2]));
	if ($chatBot->get_uid($who) == NULL) {
		$chatBot->send("<orange>Sorry the player you wish to ban does not exist.<end>", $sendto);
		return;
	}
	
	// check if high enough rank
	$main = Alts::get_main($who);
	if (isset($chatBot->admins[$main]))
		if (!isset($chatBot->admins[$sender])||(int)$chatBot->admins[$sender]["level"]<=(int)$chatBot->admins[$main]["level"]){
			$chatBot->send("<orange>Player $who ($main) has too high rank for you to ban.<end>", $sendto);
			return;			
		}
		
	// check alts
	$alts = Alts::get_alts($main);
	foreach($alts as $alt)
		if (isset($chatBot->admins[$alt]))
			if (!isset($chatBot->admins[$sender])||(int)$chatBot->admins[$sender]["level"]<=(int)$chatBot->admins[$alt]["level"]){
				$chatBot->send("<orange>Player $who ($alt) has too high rank for you to ban.<end>", $sendto);
				return;			
			}
	
	$banned = Ban::is_banned($who);
	if ($banned && $banned!==1) {
	  	$chatBot->send("<orange>Player $who is already banned.<end>", $sendto);
		return;
	}	
	Ban::add($who, $sender, 1800, "Quick 30 min ban");
	$chatBot->privategroup_kick($who);
	$chatBot->send("You have banned <highlight>$who<end> from this bot.", $sendto);
} else if (preg_match("/^ban ([a-z0-9-]+) ([0-9]+)(w|week|weeks|m|month|months|d|day|days) (for|reason) (.+)$/i", $message, $arr)) {
	$who = ucfirst(strtolower($arr[1]));
	$reason = $arr[5];

	if ($chatBot->get_uid($who) == NULL) {
		$chatBot->send("<orange>Sorry the player you wish to ban does not exist.", $sendto);
		return;
	}
	
	// check if high enough rank
	$main = Alts::get_main($who);
	if (isset($chatBot->admins[$main]))
		if (!isset($chatBot->admins[$sender])||(int)$chatBot->admins[$sender]["level"]<=(int)$chatBot->admins[$main]["level"]){
			$chatBot->send("<orange>Player $who ($main) has too high rank for you to ban.<end>", $sendto);
			return;			
		}
		
	// check alts
	$alts = Alts::get_alts($main);
	foreach($alts as $alt)
		if (isset($chatBot->admins[$alt]))
			if (!isset($chatBot->admins[$sender])||(int)$chatBot->admins[$sender]["level"]<=(int)$chatBot->admins[$alt]["level"]){
				$chatBot->send("<orange>Player $who ($alt) has too high rank for you to ban.<end>", $sendto);
				return;			
			}
		
	$banned = Ban::is_banned($who);
	if ($banned && $banned!==1) {
	  	$chatBot->send("<orange>Player $who is already banned.<end>", $sendto);
		return;
	}
	
	if (($arr[3] == "w" || $arr[3] == "week" || $arr[3] == "weeks") && $arr[2] > 0) {
	    $length = ($arr[2] * 604800);
	} else if (($arr[3] == "d" || $arr[3] == "day" || $arr[3] == "days") && $arr[2] > 0) {
	    $length = ($arr[2] * 86400);
	} else if (($arr[3] == "m" || $arr[3] == "month" || $arr[3] == "months") && $arr[2] > 0) {
	    $length = ($arr[2] * 18144000);
	}
	Ban::add($who, $sender, $length, $reason);
	$chatBot->privategroup_kick($who);	
	$chatBot->send("You have banned <highlight>$who<end> from this bot.", $sendto);
//	$chatBot->send("You have been banned from this bot by $sender. Reason: $reason", $who);
} else if (preg_match("/^ban ([a-z0-9-]+) ([0-9]+)(w|week|weeks|m|month|months|d|day|days)$/i", $message, $arr)) {
	$who = ucfirst(strtolower($arr[1]));
	
	if ($chatBot->get_uid($who) == NULL) {
		$chatBot->send("<orange>Sorry the player you wish to ban does not exist.", $sendto);
		return;
	}
	
	// check if high enough rank
	$main = Alts::get_main($who);
	if (isset($chatBot->admins[$main]))
		if (!isset($chatBot->admins[$sender])||(int)$chatBot->admins[$sender]["level"]<=(int)$chatBot->admins[$main]["level"]){
			$chatBot->send("<orange>Player $who ($main) has too high rank for you to ban.<end>", $sendto);
			return;			
		}
		
	// check alts
	$alts = Alts::get_alts($main);
	foreach($alts as $alt)
		if (isset($chatBot->admins[$alt]))
			if (!isset($chatBot->admins[$sender])||(int)$chatBot->admins[$sender]["level"]<=(int)$chatBot->admins[$alt]["level"]){
				$chatBot->send("<orange>Player $who ($alt) has too high rank for you to ban.<end>", $sendto);
				return;			
			}
	
	$banned = Ban::is_banned($who);
	if ($banned && $banned!==1) {
	  	$chatBot->send("<orange>Player $who is already banned.<end>", $sendto);
		return;
	}
	
	if (($arr[3] == "w" || $arr[3] == "week" || $arr[3] == "weeks") && $arr[2] > 0) {
	    $length = ($arr[2] * 604800);
	} else if (($arr[3] == "d" || $arr[3] == "day" || $arr[3] == "days") && $arr[2] > 0) {
	    $length = ($arr[2] * 86400);
	} else if (($arr[3] == "m" || $arr[3] == "month" || $arr[3] == "months") && $arr[2] > 0) {
	    $length = ($arr[2] * 18144000);
	}
	
	Ban::add($who, $sender, $length, '');
	$chatBot->privategroup_kick($who);	
	$chatBot->send("You have banned <highlight>$who<end> from this bot.", $sendto);
//	$chatBot->send("You have been banned from this bot by $sender.", $who);
} else if (preg_match("/^ban ([a-z0-9-]+) (for|reason) (.+)$/i", $message, $arr)) {
	$who = ucfirst(strtolower($arr[1]));
	$reason = $arr[3];
	
	if ($chatBot->get_uid($who) == NULL) {
		$chatBot->send("<orange>Sorry player you wish to ban does not exist.", $sendto);
		return;
	}

	// check if high enough rank
	$main = Alts::get_main($who);
	if (isset($chatBot->admins[$main]))
		if (!isset($chatBot->admins[$sender])||(int)$chatBot->admins[$sender]["level"]<=(int)$chatBot->admins[$main]["level"]){
			$chatBot->send("<orange>Player $who ($main) has too high rank for you to ban.<end>", $sendto);
			return;			
		}
		
	// check alts
	$alts = Alts::get_alts($main);
	foreach($alts as $alt)
		if (isset($chatBot->admins[$alt]))
			if (!isset($chatBot->admins[$sender])||(int)$chatBot->admins[$sender]["level"]<=(int)$chatBot->admins[$alt]["level"]){
				$chatBot->send("<orange>Player $who ($alt) has too high rank for you to ban.<end>", $sendto);
				return;			
			}
		
	$banned = Ban::is_banned($who);
	if ($banned && $banned!==1) {
	  	$chatBot->send("<orange>Player $who is already banned.<end>", $sendto);
		return;
	}
	
	Ban::add($who, $sender, null, $reason);
	$chatBot->privategroup_kick($who);	
	$chatBot->send("You have banned <highlight>$who<end> from this bot.", $sendto);
//	$chatBot->send("You have been banned from this bot by $sender. Reason: $reason", $who);
} else if (preg_match("/^ban ([a-z0-9-]+)$/i", $message, $arr)) {
	$who = ucfirst(strtolower($arr[1]));
	
	if ($chatBot->get_uid($who) == NULL) {
		$chatBot->send("<orange>Sorry player you wish to ban does not exist.", $sendto);
		return;
	}
	
	// check if high enough rank
	$main = Alts::get_main($who);
	if (isset($chatBot->admins[$main]))
		if (!isset($chatBot->admins[$sender])||(int)$chatBot->admins[$sender]["level"]<=(int)$chatBot->admins[$main]["level"]){
			$chatBot->send("<orange>Player $who ($main) has too high rank for you to ban.<end>", $sendto);
			return;			
		}
		
	// check alts
	$alts = Alts::get_alts($main);
	foreach($alts as $alt)
		if (isset($chatBot->admins[$alt]))
			if (!isset($chatBot->admins[$sender])||(int)$chatBot->admins[$sender]["level"]<=(int)$chatBot->admins[$alt]["level"]){
				$chatBot->send("<orange>Player $who ($alt) has too high rank for you to ban.<end>", $sendto);
				return;			
			}
		
	$banned = Ban::is_banned($who);
	if ($banned && $banned!==1) {
	  	$chatBot->send("<orange>Player $who is already banned.<end>", $sendto);
		return;
	}
	
	Ban::add($who, $sender, null, '');
	$chatBot->privategroup_kick($who);	
	$chatBot->send("You have banned <highlight>$who<end> from this bot.", $sendto);
//	$chatBot->send("You have been banned from this bot by $sender.", $who);
} else if (preg_match("/^banorg (.+)$/i", $message, $arr)) {
	$who = $arr[1];
	
	if (Ban::is_banned($who,1)) {
	  	$chatBot->send("<orange>The organization '<highlight>$who<end>' is already banned.<end>", $sendto);
		return;
	}

	Ban::add($who, $sender, null, 'Org ban', 1);

	$chatBot->send("You have banned the org '<highlight>$who<end>' from this bot.", $sendto);
} else if (preg_match("/^banhistory$/i", $message) || preg_match("/^banhistory full$/i", $message)) {
	if(preg_match("/^banhistory full$/i", $message))
		$db->query("SELECT * FROM banhistory ORDER BY id DESC;");
	else
		$db->query("SELECT * FROM banhistory ORDER BY id DESC LIMIT 30;");
	
	$blob = "<header>:::: Ban History ::::<end>\n\n";
	while($row = $db->fObject()){
		$blob .= "<white>::<end> " . date("d-M-Y H:i", $row->time) . " <highlight>{$row->name}<end> " . ($row->wasbannedby==NULL?"<red>":"<green>") . ($row->wasbannedby==NULL?"banned":"unbanned") . "<end> by <highlight>{$row->admin}<end> <white>";
		if($row->wasbannedby != NULL) $blob .= "(time left: " . ($row->length==NULL?"N/A":Util::unixtime_to_readable($row->banend-time())) . " / " . ($row->length==NULL?"N/A":Util::unixtime_to_readable($row->length)) . " by {$row->wasbannedby})";
		else $blob .= "(length: " . ($row->length==NULL?"Permanent":Util::unixtime_to_readable($row->length)) . ")";
		$blob .= "<end>\nfor: ". ($row->reason==NULL?"none":$row->reason) . "\n\n";
	}
	$msg = Text::make_link('Ban History',$blob,'blob');
	$chatBot->send($msg, $sendto);
	
} else {
	$syntax_error = true;
}

?>