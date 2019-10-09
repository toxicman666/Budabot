<?php

if (preg_match("/^iamtwink$/i", $message)) {
	$main = Alts::get_main($sender);
	if ($main == NULL) $main = $sender;
	$sql = "SELECT name FROM twinks WHERE name='{$main}';";
	$db->query($sql);
	if($db->numrows() == 0){
		$sql = "INSERT INTO twinks (name) VALUES ('{$main}');";
		$db->exec($sql);
		$chatBot->send("You have been added to twinks list.",$sendto);
	} else {
		$chatBot->send("You are already registered as twink.",$sendto);
	}
} else if (preg_match("/^iamtwink not$/i", $message)) {
	$main = Alts::get_main($sender);
	if ($main == NULL) $main = $sender;
	$sql = "SELECT name FROM twinks WHERE name='{$main}' OR name='{$sender}';";
	$db->query($sql);
	if($db->numrows() == 0){
		$chatBot->send("You are not in the list.",$sendto);
	} else {
		$sql = "DELETE FROM twinks WHERE name='{$main}' OR name='{$sender}';";
		$db->exec($sql);
		$chatBot->send("You were removed from twinks list.",$sendto);
	}
} else if (preg_match("/^twink rem ([a-z0-9-]+)$/i", $message, $arr)) {
	$name = $arr[1];
	$main = Alts::get_main($name);
	if ($main == NULL) $main = $name;
	$sql = "SELECT name FROM twinks WHERE name='{$main}' OR name='{$name}';";
	$db->query($sql);
	if($db->numrows() == 0){
		$chatBot->send("{$name} is not in the list.",$sendto);
	} else {
		$sql = "DELETE FROM twinks WHERE name='{$main}' OR name='{$name}';";
		$db->exec($sql);
		$chatBot->send("{$name} and all alts were removed from twinks list.",$sendto);
	}
} else {
	$syntax_error = true;
}