<?php

	if (count($chatBot->chatlist) > 0) {
		$playersdb = Player::get_players_db();
		$db->query("SELECT p.*, o.name as name, o.afk FROM online_<myname> o LEFT JOIN {$playersdb} p ON o.name = p.name WHERE `channel_type` = 'priv' AND added_by = '<myname>' ORDER BY `profession`, `level` DESC");
		$numguest = $db->numrows();

		$list = "<header> {$numguest} player(s) currently in chat<end>\n\n";
		$afkcount=0;
	    while ($row = $db->fObject()) {
			$players[]=$row;
		}
		
		usort($players,"name_sort");		
		foreach($players as $row){
			$main = Alts::get_main($row->name);
			$alts = Alts::get_alts($main);	
			
			$altsblob = "";
			if(count($alts)>0){
				if($main!=$row->name) $altsblob = Text::make_link("Alts of $main", "/tell <myname> !alts {$main}", 'chatcmd');
				else $altsblob = Text::make_link("Alts", "/tell <myname> !alts {$main}", 'chatcmd');
			}
			
			$dash = false;
			if(Setting::get('show_prof_in_sm')==1){
				if ($row->profession == null) {
					$list .= "<white>$row->name<highlight> - Unknown\n<end>";
				} else {
					$list .= "<white>$row->name<highlight> - $row->level<end><green>/$row->ai_level<end><highlight> $row->profession<end>";
				}
				$dash = true;
			} else {
				$list .= "<white>$row->name<end>";
			}
			if(Setting::get('show_org_in_sm')==1){
				if ($row->guild != null){
					if($dash){
						$list .= "<highlight>, $row->guild<end>";
					} else {
						$list .= " - <highlight>$row->guild<end>";
					}
				}
			}
			if ($alts!==null && Setting::get('show_alts_in_sm')==1) $list .= " " . $altsblob;

			if ($row->afk == '1') {
				$afk = " <highlight>::<end> <red>AFK<end>";
				$afkcount++;
			} else if ($row->afk != '') {
				$afk = " <highlight>::<end> <red>AFK - {$row->afk}<end>";
				$afkcount++;
			} else {
				$afk = "";
			}
			$list .= $afk . "\n";
	    }
		if($afkcount>0)
			$msg = Text::make_link("Chatlist ({$numguest}), {$afkcount} afk", $list);
		else
			$msg = Text::make_link("Chatlist ({$numguest})", $list);
		$chatBot->send($msg, $sender);
	} else {
		$chatBot->send("No players are in the private channel.", $sender);
	}

?>