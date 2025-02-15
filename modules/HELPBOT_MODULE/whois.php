<?php
   /*
   ** Author: Derroylo (RK2)
   ** Description: Shows infos about players
   ** Version: 1.0
   **
   ** Developed for: Budabot(http://sourceforge.net/projects/budabot)
   **
   ** Date(created): 10.12.2005
   ** Date(last modified): 10.12.2005
   ** 
   ** Copyright (C) 2005 Carsten Lohmann
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

$msg = "";
if (preg_match("/^whois (.+)$/i", $message, $arr)) {
    $uid = $chatBot->get_uid($arr[1]);
    $name = ucfirst(strtolower($arr[1]));
    if ($uid) {
        $whois = Player::get_by_name($arr[1]);
        if ($whois === null) {
        	$msg = "Could not find character info for {$name}.";
        } else {
	        $msg = Player::get_info($whois);

	        $list = "<header> :::::: Detailed info for {$name} :::::: <end>\n\n";
	        $list .= "Name: <highlight>{$whois->firstname} \"{$name}\" {$whois->lastname}<end>\n";
			if ($whois->guild) {
				$list .= "Guild: <highlight>{$whois->guild}<end>\n"; // ({$whois->guild_id})<end>\n";
				$list .= "Guild Rank: <highlight>{$whois->guild_rank} ({$whois->guild_rank_id})<end>\n";
			}
			$list .= "Breed: <highlight>{$whois->breed}<end>\n";
			$list .= "Gender: <highlight>{$whois->gender}<end>\n";
			$list .= "Profession: <highlight>{$whois->profession} ({$whois->prof_title})<end>\n";
			$list .= "Level: <highlight>{$whois->level}<end>\n";
			$list .= "AI Level: <highlight>{$whois->ai_level} ({$whois->ai_rank})<end>\n";
			$list .= "Faction: <highlight>{$whois->faction}<end>\n\n";
			if(Setting::get('hide_omni_scout')==0){
				$list .= "Character ID: <highlight>{$whois->charid}<end>\n";
				if(isset($whois->oldcharid)){
					$id = intval($whois->oldcharid);
					if($id<=0){
						$id=$id+2147483647+147483649;
						if($id>999999999) $id=(intval(substr($id,0,1))+2) . substr($id,1);
						else $id="2".$id;
					}
					$list .= "Old (RK2) Char ID: <highlight>{$id}<end>\n\n";
				}
			}
			
			$list .= "Source: $whois->source\n\n";
			
	        $list .= "<a href='chatcmd:///tell <myname> history $name'>Check $name's History</a>\n";
	        $list .= "<a href='chatcmd:///tell <myname> is $name'>Check $name's online status</a>\n";
	    //    if ($whois->guild) {
		//        $list .= "<a href='chatcmd:///tell <myname> whoisorg $whois->guild_id'>Show info about {$whois->guild}</a>\n";
		//		$list .= "<a href='chatcmd:///tell <myname> orglist $whois->guild_id'>Orglist for {$whois->guild}</a>\n";
		//	}
	        $list .= "<a href='chatcmd:///cc addbuddy $name'>Add to buddylist</a>\n";
	        $list .= "<a href='chatcmd:///cc rembuddy $name'>Remove from buddylist</a>";
			
	        $msg .= " :: " . Text::make_link("More info", $list, 'blob');
			
			$blob = Alts::get_alts_blob($name);
			if ($blob) {
				$msg .= " :: " . $blob;
			}
	    }
    } else {
        $msg = "Player <highlight>{$name}<end> does not exist.";
	}

    $chatBot->send($msg, $sendto);
} else if (preg_match("/^whoisall (.+)$/i", $message, $arr)) {
    $name = ucfirst(strtolower($arr[1]));
    for ($i = 1; $i <= 3; $i ++) {
        if ($i == 1) {
            $server = "Atlantean";
        } else if ($i == 2) {
            $server = "Rimor";
        } else {
            $server = "Die Neue Welt";
		}
        $msg = "";
        $whois = Player::lookup($name, $i);
		if($whois->name != $name) $whois = Player::lookup_auno($name,$i);
        if ($whois->name == $name) {
            $msg = Player::get_info($whois);

			$list = "<header> :::::: Detailed info for {$name} :::::: <end>\n\n";
	        $list .= "Name: <highlight>{$whois->firstname} \"{$name}\" {$whois->lastname}<end>\n";
			if ($whois->guild) {
				$list .= "Guild: <highlight>{$whois->guild} ({$whois->guild_id})<end>\n";
				$list .= "Guild Rank: <highlight>{$whois->guild_rank} ({$whois->guild_rank_id})<end>\n";
			}
			$list .= "Breed: <highlight>{$whois->breed}<end>\n";
			$list .= "Gender: <highlight>{$whois->gender}<end>\n";
			$list .= "Profession: <highlight>{$whois->profession} ({$whois->prof_title})<end>\n";
			$list .= "Level: <highlight>{$whois->level}<end>\n";
			$list .= "AI Level: <highlight>{$whois->ai_level} ({$whois->ai_rank})<end>\n";
			$list .= "Faction: <highlight>{$whois->faction}<end>\n\n";
			
		//	$list .= "Source: $whois->source\n\n";

            $list .= "<a href='chatcmd:///tell <myname> history $name'>Check $name's History</a>\n";
            $list .= "<a href='chatcmd:///tell <myname> is $name'>Check $name's online status</a>\n";
            $list .= "<a href='chatcmd:///cc addbuddy $name'>Add to buddylist</a>\n";
            $list .= "<a href='chatcmd:///cc rembuddy $name'>Remove from buddylist</a>";
			
            $msg .= " :: ".Text::make_link("More info", $list, 'blob');
            $msg = "<highlight>Server $server:<end> ".$msg;
        } else {
            $msg = "Server $server: Player <highlight>{$name}<end> does not exist.";
		}

        $chatBot->send($msg, $sendto);
    }
} else {
	$syntax_error = true;
}

?>
