<?php

if (preg_match("/^clans add ([a-z0-9-]+) ([a-z0-9-]+)$/i", $message, $arr)) {
	$name = ucfirst(strtolower($arr[1]));
	$main = ucfirst(strtolower($arr[2]));
	
	if(!$chatBot->get_uid($name)){
		$msg = "Invalid character name: {$name}";
		$chatBot->send($msg,$sendto);
		return;
	}
	if(!$chatBot->get_uid($main)&&$main!=""){
		$msg = "Invalid character name: {$main}";
		$main = "";
		$chatBot->send($msg,$sendto);
	}
	
	// check if omni
	$whois = Player::get_by_name($name);
	if ($whois->faction=="Omni"){
		$msg = "Error: {$name} is omni. Not added.";
		$chatBot->send($msg,$sendto);
		return;
	}
	if($main!=""){
		$whois = Player::get_by_name($main);
		if ($whois->faction=="Omni"){
			$msg = "Error: {$main} is omni. Not added.";
			$chatBot->send($msg,$sendto);
			return;
		}
	}
	
	// check if already in base
	$sql = "SELECT * FROM clans WHERE name='{$name}' OR main='{$name}';";
	$db = DB::get_instance();	
	$db->query($sql);
	
	if ($db->numrows() === 0) { // add a new entry
		if($main!="")
			$sql = "INSERT INTO clans (name, main) VALUES ('{$name}','{$main}');";
		else
			$sql = "INSERT INTO clans (name) VALUES ('{$name}');";
		$db->exec($sql);
//		Buddylist::add($name, 'clan_online'); // add to friends
		if ($main!="") Buddylist::add($main, 'clan_online'); // add main to friends
		$msg = "Record added.";
	} else {
		$row = $db->fObject();
		if($main!="" && $row->main==""){
			$sql = "UPDATE clans SET main='{$main}' WHERE name='{$name}';";
			$db->exec($sql);
			$msg = "Main updated for {$name}";
		} else if ($main!="" && $row->main!="" && $main!=$row->main){
			$msg = "{$name} is already registered as {$main}'s alt";
		} else {
			$msg = "{$name} is already in base. No changes made.";
		}
		
	}
	$chatBot->send($msg, $sendto);
	
} else if (preg_match("/^clans add ([a-z0-9-]+)$/i", $message, $arr)) {	
	$name = ucfirst(strtolower($arr[1]));
	
	if(!$chatBot->get_uid($name)){
		$msg = "Invalid character name: {$name}";
		$chatBot->send($msg,$sendto);
		return;
	}

	// check if omni
	$whois = Player::get_by_name($name);
	if ($whois->faction=="Omni"){
		$msg = "Error: {$name} is omni. Not added.";
		$chatBot->send($msg,$sendto);
		return;
	}
	
	// check if already in base
	$sql = "SELECT * FROM clans WHERE name='{$name}' OR main='{$name}';";
	$db = DB::get_instance();	
	$db->query($sql);
	
	if ($db->numrows() === 0) { // add a new entry
		$sql = "INSERT INTO clans (name) VALUES ('{$name}');";
		$db->exec($sql);
//		Buddylist::add($name, 'clan_online'); // add to friends
		$msg = "Record added.";
	}
	else {
		$msg = "{$name} is already in base. No changes made.";
	}
	$chatBot->send($msg, $sendto);	
	
} else if (preg_match("/^clans rem ([a-z0-9-]+)$/i", $message, $arr)) {	

	if(Clans::rem($arr[1]))	
		$msg = "Entry {$arr[1]} removed.";
	else
		$msg = "Entry {$arr[1]} not found.";
	$chatBot->send($msg,$sendto);
//	Buddylist::remove($name, 'clan_online');
} else {
	$syntax_error = true;
}

?>
