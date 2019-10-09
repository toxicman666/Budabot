<?php

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

		/* check if 100+ */
		if($whois->level<100){
				$underlevel[] = $name;
				continue;
		}
		
		/* check if player is already an alt */
		if (in_array($name, $alts)) {
			$self_registered []= $name;
			continue;
		}
		if ($name==$sender) {
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
	if ($underlevel){
		$window .= "Too low level (bot is 100+): " . implode(' ', $underlevel) . "\n\n";
	}
	
	/* create a link */
	$link = "";
	if (count($names_succeeded) > 0) {
		$link .= 'Added '.count($names_succeeded).' alts.';
		$instructions = " <yellow>To finish alt registration in bot use !register from the toon you want to be added.<end>";
	}
	$failed_count = count($other_registered) + count($names_not_existing) + count($self_registered) + count($clans) + count($underlevel);
	if ($failed_count > 0) {
		$link .= ' Failed adding '.$failed_count.' alts.';
	}
	$msg = Text::make_link($link, $window);
	
	$msg .= $instructions;

	$chatBot->send($msg, $sendto); 
} else if (preg_match("/^alts (rem|del|remove|delete) ([a-z0-9-]+)$/i", $message, $arr)) { 
	$name = ucfirst(strtolower($arr[2]));
	
	$main = Alts::get_main($sender);
	$alts = Alts::get_alts($main);
	$tara = Alts::get_tara_alts($main);
	
	if (in_array($name, $tara)){
		$chatBot->send("<highlight>{$name}<end> is your alt <red>permanently<end>",$sendto);
		return;
	}
	if (!in_array($name, $alts)) {
		$msg = "<highlight>{$name}<end> is not registered as your alt.";
	} else {
		Alts::rem_alt($main, $name);
		$msg = "<highlight>{$name}<end> has been deleted from your alt list.";
	}
	$chatBot->send($msg, $sendto); 
} else if (preg_match("/^alts ([a-z0-9-]+)$/i", $message, $arr) || preg_match("/^alts$/i", $message, $arr)) {
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
