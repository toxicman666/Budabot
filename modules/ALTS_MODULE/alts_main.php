<?php

if (preg_match("/^alts main ([a-z0-9-]+)$/i", $message, $arr)) {
	$alt = $sender;
	$new_main = ucfirst(strtolower($arr[1]));

	$uid = $chatBot->get_uid($new_main);
	if (!$uid) {
		$msg = "Player <highlight>$new_main<end> does not exist.";
		$chatBot->send($msg, $sendto);
		return;
	}
	
	$current_main = Alts::get_main($sender);
	if ($current_main == $new_main) {
		$msg = "You are already registered as an alt of {$new_main}.";
		$chatBot->send($msg, $sendto);
		return;
	}
	
	$alts = Alts::get_alts($current_main);
	if (count($alts) > 0) {
		$msg = "You must not have any alts already registered.";
		$chatBot->send($msg, $sendto);
		return;
	}
	
	// let them know if they are changing the main for this char
	if ($current_main != $sender) {
		Alts::rem_alt($current_main, $sender);
		$msg = "You have been removed as an alt of $current_main.";
		if(Setting::get('alts_wc_members')==1){
			$db->exec("DELETE FROM members_<myname> WHERE `name` = '{$sender}'");
			Buddylist::remove($sender, 'member');
			$db->exec("UPDATE alts SET `wc`=0 WHERE `alt`='{$sender}';");	
		}
		$chatBot->send($msg, $sendto);
	}

	Alts::add_alt($new_main, $sender);
	$msg = "You have been registered as an alt of {$new_main}.";
	if(Setting::get('alts_wc_members')==1){
		$db->query("SELECT * FROM members_<myname> WHERE `name` = '{$new_main}'");
		if ($db->numrows() == 0) {
			$db->exec("INSERT INTO members_<myname> (`name`, `autoinv`) VALUES ('{$new_main}', 1)");
		}
		$db->exec("UPDATE alts SET `wc`=1 WHERE `alt`='{$new_main}';");
		Buddylist::add($toadd, 'member');
	}	
	$chatBot->send($msg, $sendto);
} else {
	$syntax_error = true;
}

?>
