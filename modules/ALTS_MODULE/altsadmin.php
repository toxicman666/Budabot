<?php

if (preg_match("/^altsadmin add ([a-z0-9-]+) ([a-z0-9-]+)$/i", $message, $names)) {
	if ($names[1] == '' || $names[2] == '') {
		$syntax_error = true;
		return;
	}

	$name_main = ucfirst(strtolower($names[1]));
	$name_main = Alts::get_main($name_main);
	$name_alt = ucfirst(strtolower($names[2]));
	$uid_main = $chatBot->get_uid($name_main);
	$uid_alt = $chatBot->get_uid($name_alt);

	if ($name_main==$name_alt){
		$msg = "Cannot register player as alt of itself.";
		$chatBot->send($msg, $sendto);
		return;
	}
	if (!$uid_alt) {
		$msg = "Player <highlight>$name_alt<end> does not exist.";
		$chatBot->send($msg, $sendto);
		return;
	}
/*	if (!$uid_main) {
		$msg = " Player <highlight>$name_main<end> does not exist.";
		$chatBot->send($msg, $sendto);
		return;
	}	*/
	$whois = Player::get_by_name($name_alt);
	if($whois->faction=="Clan"){
		$msg = "<orange>Cannot add a clanner to alts.<end>";
		$chatBot->send($msg, $sendto);
		return;
	}

	$main = Alts::get_main($name_alt);
	if ($main != $name_alt) {
		$msg = "Player <highlight>$name_alt<end> is already registered as an alt of <highlight>$main<end>.";
		$chatBot->send($msg, $sendto);
		return;
	}
	
	$alts = Alts::get_alts($name_alt);
	if (count($alts) > 0) {
		$msg = "Player <highlight>$name_alt<end> is already registered as main.";
	}

	Alts::add_alt($name_main, $name_alt);
	$msg = "<highlight>$name_alt<end> has been registered as an alt of $name_main.";
	$chatBot->send($msg, $sendto);
} else if (preg_match("/^altsadmin rem ([a-z0-9-]+) ([a-z0-9-]+)$/i", $message, $names)) {
	if ($names[1] == '' || $names[2] == '') {
		$syntax_error = true;
		return;
	}

	$name_main = ucfirst(strtolower($names[1]));
	$name_alt = ucfirst(strtolower($names[2]));
	$name_main = Alts::get_main($name_main);
	$tara = Alts::get_tara_alts($name_main);
	if (in_array($name_alt, $tara)){
		$chatBot->send("<highlight>{$name_alt}<end> is <highlight>{$name_main}<end>'s alt <white>permanently<end>",$sendto);
		return;
	}
	if (Alts::rem_alt($name_main, $name_alt) == 0) {
		$msg = "Player <highlight>$name_alt<end> not listed as an alt of Player <highlight>$name_main<end>.  Please check the player's !alts listings.";
	} else {
		$msg = "<highlight>$name_alt<end> has been deleted from the alt list of <highlight>$name_main.<end>";
	}
	$chatBot->send($msg, $sendto);
} else {
	$syntax_error = true;
}

?>