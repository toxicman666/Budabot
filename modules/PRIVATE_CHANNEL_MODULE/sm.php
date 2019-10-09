<?php

if (preg_match("/^sm (org|orgs)$/i", $message)){
	if (count($chatBot->chatlist) > 0) {
		$playersdb = Player::get_players_db();
		$db->query("SELECT p.*, o.name as name, o.afk FROM online_<myname> o LEFT JOIN {$playersdb} p ON o.name = p.name WHERE `channel_type` = 'priv' AND added_by = '<myname>' ORDER BY `profession`, `level` DESC");

	    while ($row = $db->fObject()) {
			$players[]=$row;
		}
		$orgs=array();
		foreach($players as $row){
			if($row->guild!=null){
				if(!isset($orgs[$row->guild]))
					$orgs[$row->guild]=1;
				else
					$orgs[$row->guild]+=1;
			}
	    }
		ksort($orgs);
		foreach($orgs as $key => $value){
			$list .= Text::make_link("{$key} ({$value})","/tell <myname> !sm {$key}","chatcmd") . "\n";
		}
		$numorgs = count($orgs);
		if($numorgs>0){
			$list = "<header> {$numorgs} org(s) currently in chat<end>\n\n" . $list;
		} else {
			$chatBot->send("No members of any orgs in the channel.", $sendto);
			return;
		}
		
		$msg = Text::make_link("Orgs in chat ({$numorgs})", $list);
		$chatBot->send($msg, $sendto);
	} else {
		$chatBot->send("No players are in the private channel.", $sendto);
	}	
} else if (preg_match("/^sm$/i", $message) || preg_match("/^sm (.+)$/i", $message, $arr)) {
	if($arr[1]!=null) $org_match = $arr[1];
	if (count($chatBot->chatlist) > 0) {
		$playersdb = Player::get_players_db();
		$db->query("SELECT p.*, o.name as name, o.afk FROM online_<myname> o LEFT JOIN {$playersdb} p ON o.name = p.name WHERE `channel_type` = 'priv' AND added_by = '<myname>' ORDER BY `profession`, `level` DESC");
		$numguest = $db->numrows();

		$afkcount=0;
		$count_inorg=0;
	    while ($row = $db->fObject()) {
			$players[]=$row;
		}

		usort($players,"name_sort");
		foreach($players as $row){
			if($org_match==null || $row->guild==$org_match){
				$main = Alts::get_main($row->name);
				$altsblob = "";
				if(count($alts)>0){
					if($main!=$row->name) $altsblob = Text::make_link("Alts of $main", "/tell <myname> !alts {$main}", 'chatcmd');
					else $altsblob = Text::make_link("Alts", "/tell <myname> !alts {$main}", 'chatcmd');
				}
				
				$dash = false;
				if(Setting::get('show_prof_in_sm')==1||$org_match!=null){
					if ($row->profession == null) {
						$list .= "<white>$row->name<highlight> - Unknown<end>";
					} else {
						$list .= "<white>$row->name<highlight> - $row->level<end><green>/$row->ai_level<end><highlight> $row->profession<end>";
					}
					$dash = true;
				} else {
					$list .= "<white>$row->name<end>";
				}
				if(Setting::get('show_org_in_sm')==1&&$org_match==null){
					if ($row->guild != null){
						if($dash){
							$list .= "<highlight>, $row->guild<end>";
						} else {
							$list .= " - <highlight>$row->guild<end>";
						}
					}
				}
				if ($alts!==null && (Setting::get('show_alts_in_sm')==1 || $org_match!=null)) $list .= " " . $altsblob;

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
				$count_inorg++;
			}
	    }
		if($org_match==null){
			$list = "<header> {$numguest} player(s) currently in chat<end>\n\n" . $list;
		} else if ($count_inorg>0){
			$list = "<header> {$count_inorg} member(s) of <highlight>{$org_match}<end> currently in chat<end>\n\n" . $list;
		} else {
			$chatBot->send("No members of <highlight>{$org_match}<end> are in the channel.", $sendto);
			return;
		}
		if($org_match!=null&&$afkcount>0)
			$msg = Text::make_link("{$org_match} ({$count_inorg}), {$afkcount} afk", $list);
		else if ($org_match!=null&&$afkcount==0)
			$msg = Text::make_link("{$org_match} ({$count_inorg})", $list);
		else if($afkcount>0)
			$msg = Text::make_link("Chatlist ({$numguest}), {$afkcount} afk", $list);
		else
			$msg = Text::make_link("Chatlist ({$numguest})", $list);
		$chatBot->send($msg, $sendto);
	} else {
		$chatBot->send("No players are in the private channel.", $sendto);
	}
} else {
	$syntax_error = true;
}
?>
