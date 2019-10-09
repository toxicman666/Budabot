<?php

if (preg_match("/^addmod (.+)$/i", $message, $arr)){
	$who = ucfirst(strtolower($arr[1]));

	if ($chatBot->get_uid($who) == NULL){
		$chatBot->send("<red>Sorry the player you wish to add doesn't exist.<end>", $sendto);
		return;
	}
	
	if ($who == $sender) {
		$chatBot->send("<red>You can't add yourself to another group.<end>", $sendto);
		return;
	}


	if ($chatBot->admins[$who]["level"] == 3) {
		$chatBot->send("<red>Sorry but $who is already a moderator.<end>", $sendto);
		return;
	}
	
	if ($chatBot->vars["SuperAdmin"] != $sender && (int)$chatBot->admins[$sender]["level"] <= (int)$chatBot->admins[$who]["level"]){
		$chatBot->send("<red>You must have a rank higher then $who.<end>", $sendto);
		return;
	}

	if (isset($chatBot->admins[$who]["level"]) && $chatBot->admins[$who]["level"] >= 2) {
		if($chatBot->admins[$who]["level"] > 3) {
			$chatBot->send("<highlight>$who<end> has been demoted to a moderator.", $sendto);
			$chatBot->send("You have been demoted to a moderator", $who);
		} else {
			$chatBot->send("<highlight>$who<end> has been promoted to a moderator.", $sendto);
			$chatBot->send("You have been promoted to a moderator", $who);
		}
		$db->exec("UPDATE admin_<myname> SET `adminlevel` = 3 WHERE `name` = '$who'");
		$chatBot->admins[$who]["level"] = 3;
	} else {
		$db->exec("INSERT INTO admin_<myname> (`adminlevel`, `name`) VALUES (3, '$who')");
		$chatBot->admins[$who]["level"] = 3;
		$chatBot->send("<highlight>$who<end> has been added as a moderator", $sendto);
		$chatBot->send("You got moderator access to <myname>", $who);
	}

	if (Setting::get("moderator_forums")==1){
		// attempt to add to forums
		$db->query("SELECT ag.id, ag.email, orgname, orgrank FROM omnihqdb.jos_users jo JOIN omnihqdb.jos_agora_users ag ON jo.username = ag.username WHERE jo.name = '$who';");
		if($db->numrows()>0){
			$row = $db->fObject();
			$db->query("SELECT id, group_id FROM omnihqdb.jos_agora_user_group WHERE user_id = {$row->id} AND group_id = 2;");
			if($db->numrows()===0){
				$db->exec("INSERT INTO omnihqdb.jos_agora_user_group (user_id, group_id, role_id) VALUES ({$row->id}, 2, 2);");
				$chatBot->send("You got access to <myname> Moderators forum", $who);
			}
		}
	}

	Buddylist::add($who, 'admin');
} else {
	$syntax_error = true;
}
?>