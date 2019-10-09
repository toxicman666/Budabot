<?php

if (preg_match("/^replacemods ([a-z0-9- ]+)$/i", $message, $arr) && $sender == "Warleaders"){

	$tara_mods_group = 8;
	// remove old mods (make them rl)
	forEach ($chatBot->admins as $who => $data){
		if ($chatBot->admins[$who]["level"] == 3){
			if ($who != "") {
				$db->exec("UPDATE admin_<myname> SET `adminlevel` = 2 WHERE `name` = '$who'");
				$chatBot->admins[$who]["level"] = 2;

				if (Setting::get("moderator_forums")==1){
					// attempt to remove from forums
					$db->query("SELECT ag.id, ag.email, orgname, orgrank FROM omnihqdb.jos_users jo JOIN omnihqdb.jos_agora_users ag ON jo.username = ag.username WHERE jo.name = '$who';");
					if($db->numrows()>0){
						$row = $db->fObject();
						$db->query("SELECT id, group_id FROM omnihqdb.jos_agora_user_group WHERE user_id = {$row->id} AND group_id = {$tara_mods_group};");
						if($db->numrows()>0){
							$db->exec("DELETE FROM omnihqdb.jos_agora_user_group WHERE user_id = {$row->id} AND group_id = {$tara_mods_group};");
						}
					}
				}
			}
		}
	}

	// add new mods (or promote)
	$newmods = array();
	$newmods = explode (" ",$arr[1]);
	foreach($newmods as $mod){
		$mod = ucfirst(strtolower($mod));
		$id = $chatBot->get_uid($mod);
		if($id) {
			if (isset($chatBot->admins[$mod]["level"]) && $chatBot->admins[$mod]["level"] >= 2) {
				if($chatBot->admins[$mod]["level"] == 2){
					$db->exec("UPDATE admin_<myname> SET `adminlevel` = 3 WHERE `name` = '$mod';");
				}
			} else {
					$db->exec("INSERT INTO admin_<myname> (`adminlevel`, `name`) VALUES (3, '$mod')");
			}
			$chatBot->admins[$mod]["level"] = 3;
			$chatBot->send("You got moderator access to <myname>", $mod);
			
			if (Setting::get("moderator_forums")==1){
				// attempt to add to forums
				$db->query("SELECT ag.id, ag.email, orgname, orgrank FROM omnihqdb.jos_users jo JOIN omnihqdb.jos_agora_users ag ON jo.username = ag.username WHERE jo.name = '$mod';");
				if($db->numrows()>0){
					$row = $db->fObject();
					$db->query("SELECT id, group_id FROM omnihqdb.jos_agora_user_group WHERE user_id = {$row->id} AND group_id = {$tara_mods_group};");
					if($db->numrows()===0){
						$db->exec("INSERT INTO omnihqdb.jos_agora_user_group (user_id, group_id, role_id) VALUES ({$row->id}, {$tara_mods_group}, 2);");
						$chatBot->send("You got access to <myname> Moderators forum", $mod);
					}
				}
			}
		}
	}

}
?>