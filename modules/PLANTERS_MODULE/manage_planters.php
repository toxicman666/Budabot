<?php

if (preg_match("/^iamplanter$/i", $message)) {
	$main = Alts::get_main($sender);
	if ($main == NULL) $main = $sender;
	$sql = "SELECT name FROM planters WHERE name='{$main}';";
	$db->query($sql);
	if($db->numrows() == 0){
		$sql = "INSERT INTO planters (name) VALUES ('{$main}');";
		$db->exec($sql);
		$chatBot->send("You have been added to planters list.",$sendto);
	} else {
		$chatBot->send("You are already registered as planter.",$sendto);
	}
} else if (preg_match("/^iamplanter not$/i", $message)) {
	$main = Alts::get_main($sender);
	if ($main == NULL) $main = $sender;
	$sql = "SELECT name FROM planters WHERE name='{$main}' OR name='{$sender}';";
	$db->query($sql);
	if($db->numrows() == 0){
		$chatBot->send("You are not in the list.",$sendto);
	} else {
		$sql = "DELETE FROM planters WHERE name='{$main}' OR name='{$sender}';";
		$db->exec($sql);
		$chatBot->send("You were removed from planters list.",$sendto);
	}
} else if (preg_match("/^remplanter ([a-z0-9-]+)$/i", $message, $arr)) {
	$name = ucfirst(strtolower($arr[1]));
	$uid = $chatBot->get_uid($name);
	if (!$uid) {
		$chatBot->send("Player <highlight>{$name}<end> does not exist.",$sendto);
		return;
	}
	$main = Alts::get_main($name);
	if ($main == NULL) $main = $name;
	$sql = "SELECT name FROM planters WHERE name='{$main}' OR name='{$sender}';";
	$db->query($sql);
	if($db->numrows() == 0){
		$chatBot->send("{$name} is not on the planters list.",$sendto);
	} else {
		$sql = "DELETE FROM planters WHERE name='{$main}' OR name='{$sender}';";
		$db->exec($sql);
		$chatBot->send("{$name} was removed from planters list.",$sendto);
	}
} else {
	$syntax_error = true;
}