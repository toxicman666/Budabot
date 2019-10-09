<?php

if(Setting::get('alts_wc_members')==1){
	if (preg_match("/^alts wc add ([a-z0-9-]+)$/i", $message, $arr)) {
		$toadd = ucfirst(strtolower($arr[1]));
		$max_wc_alts=Setting::get('max_wc_alts');
		$name = $sender;
		$sql = "SELECT main FROM alts WHERE main LIKE '{$name}' OR alt LIKE '{$name}' LIMIT 1;";
		$db = DB::get_instance();
		$db->query($sql);		
		if ($db->numrows() !== 0) {
			$row = $db->fObject();
			$main = $row->main;
			$sql = "SELECT * FROM alts WHERE main LIKE '{$main}';";
			$db->query($sql);
			$count=0;
			$inalts=false;
			while($row = $db->fObject()){
				if($row->wc==1) {
					if($row->alt==$toadd){
						$chatBot->send("<orange>{$toadd} already in list<end>",$sendto);
						return;
					}
					$count++;				
				}
				if($row->alt==$toadd) $inalts=true;
			}
			if(!$inalts) {
				$chatBot->send("{$toadd} is not in your alts, use '!alts add'",$sendto);
				return;
			}
			if($count<$max_wc_alts){
				$db->query("SELECT * FROM members_<myname> WHERE `name` = '{$toadd}'");
				if ($db->numrows() == 0) {
					$db->exec("INSERT INTO members_<myname> (`name`, `autoinv`) VALUES ('{$toadd}', 1)");
				}
				$db->exec("UPDATE alts SET `wc`=1 WHERE `alt`='{$toadd}';");
				Buddylist::add($toadd, 'member');
				$chatBot->send("{$toadd} was added successfully",$sendto);
				return;
			} else {
				$chatBot->send("<orange>You exceeded limit ({$max_wc_alts} alts), use '!alts wc rem'<end>",$sendto);
				return;
			}
		} else {
			$msg = "No alts registered";
			$chatBot->send($msg,$sendto);
			return;
		}
	} else if (preg_match("/^alts wc (rem|del) ([a-z0-9-]+)$/i", $message, $arr)) {
		$todel = ucfirst(strtolower($arr[2]));
		$max_wc_alts=Setting::get('max_wc_alts');
		$name = $sender;
		$sql = "SELECT main FROM alts WHERE main LIKE '{$name}' OR alt LIKE '{$name}' LIMIT 1;";
		$db = DB::get_instance();
		$db->query($sql);		
		if ($db->numrows() !== 0) {
			$row = $db->fObject();
			$main = $row->main;
			$sql = "SELECT * FROM alts WHERE main LIKE '{$main}';";
			$db->query($sql);
			$inalts=false;
			$inwc=false;
			while($row = $db->fObject()){
				if($row->wc==1) {
					if($row->alt==$todel){
						$inwc=true;
					}
				}
				if($row->alt==$todel) $inalts=true;
			}
			if(!$inalts) {
				$chatBot->send("{$todel} is not in your alts, use '!alts add'",$sendto);
				return;
			}
			if($inwc){
				$db->exec("DELETE FROM members_<myname> WHERE `name` = '{$todel}'");
				Buddylist::remove($todel, 'member');
				$db->exec("UPDATE alts SET `wc`=0 WHERE `alt`='{$todel}';");
				$chatBot->send("{$todel} was removed successfully",$sendto);
				return;
			} else {
				$chatBot->send("<orange>{$todel} is not in WC list<end>",$sendto);
				return;
			}
		} else {
			$msg = "No alts registered";
			$chatBot->send($msg,$sendto);
			return;
		}
	} else if (preg_match("/^alts wc ([a-z0-9-]+)$/i", $message, $arr)) {
		$name = ucfirst(strtolower($arr[1]));
		$db = DB::get_instance();
		$db->query("SELECT * FROM members_<myname> WHERE `name` = '{$name}';");
	  	if ($db->numrows() === 0) {
			$msg = "<highlight>$name<end> is not a member of this bot.";
	  		$chatBot->send($msg,$sendto);
			return;
	  	}	
		$max_wc_alts=Setting::get('max_wc_alts');
		$sql = "SELECT main FROM alts WHERE main LIKE '{$name}' OR alt LIKE '{$name}' LIMIT 1;";
		$db = DB::get_instance();
		$db->query($sql);		
		if ($db->numrows() !== 0) {
			$row = $db->fObject();
			$main = $row->main;
			$sql = "SELECT * FROM alts WHERE main LIKE '{$main}';";
			$db->query($sql);
			$blob = ":::: <orange>Alts WC channel access<end> ::::\n<tab>(<hjighlight>{$max_wc_alts} alts maximum<end>)\n\n";
			$blob .= "Main:\n<green>{$main}<end> on\n\nAlts:\n";
			while($row = $db->fObject()){
				if($row->wc==1)
					$blob .= "<green>{$row->alt} on<end>";
				else $blob .= "<red>{$row->alt} off<end>";
				$blob .= "\n";
			}
			$chatBot->send(Text::make_link("{$name}'s alts WC access",$blob,'blob'),$sendto);
		} else {
			$msg = "No alts registered";
			$chatBot->send($msg,$sendto);
		}
		return;
	} else if (preg_match("/^alts wc$/i", $message, $arr)) {
		$name = $sender;
		$db = DB::get_instance();
		$db->query("SELECT * FROM members_<myname> WHERE `name` = '{$name}';");
	  	if ($db->numrows() === 0) {
			$msg = "You are not a member of this bot.";
	  		$chatBot->send($msg,$sendto);
			return;
	  	}
		$max_wc_alts=Setting::get('max_wc_alts');
		$sql = "SELECT main FROM alts WHERE main LIKE '{$name}' OR alt LIKE '{$name}' LIMIT 1;";
		$db->query($sql);		
		if ($db->numrows() !== 0) {
			$row = $db->fObject();
			$main = $row->main;
			$sql = "SELECT * FROM alts WHERE main LIKE '{$main}';";
			$db->query($sql);
			$blob = ":::: <orange>Alts WC channel access<end> ::::\n<tab>(<hjighlight>{$max_wc_alts} alts maximum<end>)\n\n";
			$blob .= "Main:\n<green>{$main}<end> on\n\nAlts:\n";
			while($row = $db->fObject()){
				if($row->wc==1)
					$blob .= "<green>{$row->alt} on<end> " . Text::make_link("rem","/tell <myname> alts wc rem {$row->alt}",'chatcmd');
				else $blob .= "<red>{$row->alt} off<end> " . Text::make_link("add","/tell <myname> alts wc add {$row->alt}",'chatcmd');
				$blob .= "\n";
			}
			$chatBot->send(Text::make_link("Alts WC access",$blob,'blob'),$sendto);
		} else {
			$msg = "No alts registered";
			$chatBot->send($msg,$sendto);
		}
		return;
	}
}
if (preg_match("/^alts add ([a-z0-9- ]+)$/i", $message, $arr)) {
	/* get all names in an array */
	$names = explode(' ', $arr[1]);
	
	$sender = ucfirst(strtolower($sender));
	
	$main = Alts::get_main($sender);
	$alts = Alts::get_alts($main);
	
	/* Pop a name from the array until none are left (checking for null) */
	while (null != ($name = array_pop($names))) {
		$name = ucfirst(strtolower($name));
		$uid = $chatBot->get_uid($name);
		/* check if player exists */
		if (!$uid) {
			$names_not_existing []= $name;
			continue;
		}
		
		/* check if clanner */
		$whois = Player::get_by_name($name);
		if($whois->faction=="Clan"){
				$clans[] = $name;
				continue;
		}
		
		/* check if player is already an alt */
		if (in_array($name, $alts)) {
			$self_registered []= $name;
			continue;
		}
		
		/* check if player is already a main or assigned to someone else */
		$temp_alts = Alts::get_alts($name);
		$temp_main = Alts::get_main($name);
		if (count($temp_alts) != 0 || $temp_main != $name) {
			$other_registered []= $name;
			continue;
		}
		
		if(Alts::is_wc_member($name)){
			$wc[]=$name;
			continue;
		}

		/* insert into database */
		Alts::add_alt($main, $name);
		$names_succeeded []= $name;
		
		// update character info
		Player::get_by_name($name);
	}
	
	$window = '';
	if ($names_succeeded) {
		$window .= "Alts added:\n" . implode(' ', $names_succeeded) . "\n\n";
	}
	if ($self_registered) {
		$window .= "Alts already registered to yourself:\n" . implode(' ', $self_registered) . "\n\n";
	}
	if ($other_registered) {
		$window .= "Alts already registered to someone else:\n" . implode(' ', $other_registered) . "\n\n";
	}
	if ($names_not_existing) {
		$window .= "Alts not existing:\n" . implode(' ', $names_not_existing) . "\n\n";
	}
	if ($clans){
		$window .= "<orange>Cannot register clanners: " . implode(' ', $clans) . "<end>\n\n";
	}
	if ($wc){
		$window .= "Cannot add WC members to alts: " . implode(' ', $wc) . "\n\n";
	}
	
	/* create a link */
	if (count($names_succeeded) > 0) {
		$link = 'Added '.count($names_succeeded).' alts to your list. ';
	}
	$failed_count = count($other_registered) + count($names_not_existing) + count($self_registered) + count($clans) + count($wc);
	if ($failed_count > 0) {
		$link .= 'Failed adding '.$failed_count.' alts to your list.';
	}
	$msg = Text::make_link($link, $window);

	$chatBot->send($msg, $sendto);
} else if (preg_match("/^alts (rem|del|remove|delete) ([a-z0-9-]+)$/i", $message, $arr)) {
	$name = ucfirst(strtolower($arr[2]));
	
	$main = Alts::get_main($sender);
	$alts = Alts::get_alts($main);
	
	if (!in_array($name, $alts)) {
		$msg = "<highlight>{$name}<end> is not registered as your alt.";
	} else {
		$db->query("SELECT * FROM alts WHERE `alt`='{$name}' AND `wc`=1;");
		if ($db->numrows()===0){
			Alts::rem_alt($main, $name);
			$msg = "<highlight>{$name}<end> has been deleted from your alt list.";
		} else if(Setting::get('alts_wc_members')==1){
			Alts::rem_alt($main, $name);
			$db->exec("DELETE FROM members_<myname> WHERE `name` = '{$name}'");
			Buddylist::remove($name, 'member');
			$db->exec("UPDATE alts SET `wc`=0 WHERE `alt`='{$name}';");	
			$msg = "<highlight>{$name}<end> has been deleted from your alt list.";
		} else {
			$msg = "<orange>{$name} is in your WC alts, use WC bot in order to remove<end>";
		}
	}
	$chatBot->send($msg, $sendto);
}
/* else if (preg_match('/^alts setmain ([a-z0-9-]+)$/i', $message, $arr)) {
	// check if new main exists
	$new_main = ucfirst(strtolower($arr[1]));
	$uid = $chatBot->get_uid($new_main);
	if (!$uid) {
		$msg = "Player <highlight>{$new_main}<end> does not exist.";
		$chatBot->send($msg, $sendto);
		return;
	}
	
	$current_main = Alts::get_main($sender);
	$alts = Alts::get_alts($current_main);
	
	if (!in_array($new_main, $alts)) {
		$msg = "<highlight>{$new_main}<end> must first be registered as your alt.";
		$chatBot->send($msg, $sendto);
		return;
	}

	$db->beginTransaction();

	// remove all the old alt information
	$db->exec("DELETE FROM `alts` WHERE `main` = '{$current_main}'");

	// add current main to new main as an alt
	Alts::add_alt($new_main, $current_main);
	
	// add current alts to new main
	forEach ($alts as $alt) {
		if ($alt != $new_main) {
			Alts::add_alt($new_main, $alt);
		}
	}
	
	$db->commit();
	
	$db->exec("DELETE FROM members_<myname> WHERE `name` = '{$alt}'");
	Buddylist::remove($current_main, 'member');
	$db->exec("UPDATE alts SET `wc`=0 WHERE `main`='{$current_main}';");	
	$db->query("SELECT * FROM members_<myname> WHERE `name` = '{$new_main}'");
	if ($db->numrows() == 0) {
		$db->exec("INSERT INTO members_<myname> (`name`, `autoinv`) VALUES ('{$new_main}', 1)");
	}
	$db->exec("UPDATE alts SET `wc`=1 WHERE `alt`='{$new_main}';");
	Buddylist::add($toadd, 'member');
	
	$msg = "Successfully set your new main as <highlight>{$new_main}<end>.";
	$chatBot->send($msg, $sendto);
}
*/ else if (preg_match("/^alts ([a-z0-9-]+)$/i", $message, $arr) || preg_match("/^alts$/i", $message, $arr)) {
	if (isset($arr[1])) {
		$name = ucfirst(strtolower($arr[1]));
	} else {
		$name = $sender;
	}

	$msg = Alts::get_alts_blob($name,true);
	
	if ($msg === null) {
		$msg = "No alts are registered for <highlight>{$name}<end>.";
	}

	$chatBot->send($msg, $sendto);
} else {
	$syntax_error = true;
}

?>
