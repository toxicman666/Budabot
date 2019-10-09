<?php


if (preg_match("/^remmod ([a-z0-9-]+)$/i", $message, $arr)){
	$who = ucfirst(strtolower($arr[1]));
	
	if ($chatBot->admins[$who]["level"] != 3) {
		$chatBot->send("<red>$who is not a Moderator of this Bot.<end>", $sendto);
		return;
	}
	
	if ((int)$chatBot->admins[$sender]["level"] <= (int)$chatBot->admins[$who]["level"]){
		$chatBot->send("<red>You must have a rank higher then $who.", $sendto);
		return;
	}
	
	unset($chatBot->admins[$who]);
	$db->exec("DELETE FROM admin_<myname> WHERE `name` = '$who'");
	
	Buddylist::remove($who, 'admin');

	$chatBot->send("<highlight>$who<end> has been removed as a moderator.", $sendto);
	$chatBot->send("Your moderator access to <myname> has been removed.", $who);

	if (Setting::get("moderator_forums")==1){
		// attempt to remove from forums
		$db->query("SELECT ag.id, ag.email, orgname, orgrank FROM omnihqdb.jos_users jo JOIN omnihqdb.jos_agora_users ag ON jo.username = ag.username WHERE jo.name = '$who';");
		if($db->numrows()>0){
			$row = $db->fObject();
			$db->query("SELECT id, group_id FROM omnihqdb.jos_agora_user_group WHERE user_id = {$row->id} AND group_id = 2;");
			if($db->numrows()>0){
				$db->exec("DELETE FROM omnihqdb.jos_agora_user_group WHERE user_id = {$row->id} AND group_id = 2;");
			}
		}
	}
	
} else {
	$syntax_error = true;
}

?>