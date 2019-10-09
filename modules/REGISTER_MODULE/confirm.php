<?php
if (preg_match("/^confirm (.+)$/i", $message, $arr)) {
	$email = strtolower(trim($arr[1]));
	$group_id_confirmed = 4;  // confirmed users
	$group_id_150 = 7; // 150+
	$group_id_tara = 6; // tara	
	$group_id_205 = 5; // 205+
	$group_id_wc = 3;  // warleaders
	$role_id = 2;
	
	$whois = Player::get_by_name($sender);
	if ($whois->faction ==clan) {
		$chatBot->send("<orange>Clans can not register in this bot.<end>",$sender);
		return;
	}	
	if ($whois->level <10) {
		$chatBot->send("<orange>You have to be level 10 or higher in order to confirm your account and see private forums.<end>", $sendto);
		return;
	}
	
	// check bans
	$sql = "SELECT name FROM taratime.banlist_<myname> WHERE name = '$sender';";
	$db->query($sql);
	if ($db->numrows() >0) {
		$chatBot->send("<orange>You are banned from this network.<end>");
		return;
	}
	
  	$main = Alts::get_main($sender);
	$alts = Alts::get_alts($main);
	if(count($alts)>0){
		foreach($alts as $alt){
			if ($alt==$sender) continue;
			$sql = "SELECT ag.id, jo.email FROM omnihqdb.jos_users jo JOIN omnihqdb.jos_agora_users ag ON jo.username = ag.username LEFT JOIN omnihqdb.jos_agora_user_group gr ON gr.user_id=ag.id WHERE jo.name='$alt' AND gr.group_id = $group_id_confirmed;";
			$db->query($sql);
			if ($db->numrows() != 0) {
				$chatBot->send("<orange>Your alt $alt is already registered. Aborting<end>",$sender);
				return;
			}
		}
	}
	if($main!=$sender){
		$sql = "SELECT ag.id, jo.email FROM omnihqdb.jos_users jo JOIN omnihqdb.jos_agora_users ag ON jo.username = ag.username LEFT JOIN omnihqdb.jos_agora_user_group gr ON gr.user_id=ag.id WHERE jo.name='$main' AND gr.group_id = $group_id_confirmed;";
		$db->query($sql);
		if ($db->numrows() != 0) {
			$chatBot->send("<orange>Your main $main is already registered. Aborting<orange>",$sender);
			return;
		}
	}
	
	// find a full name in jos_users table and put id in $row->id
	$sql = "SELECT ag.id, jo.email FROM omnihqdb.jos_users jo JOIN omnihqdb.jos_agora_users ag ON jo.username = ag.username WHERE jo.name='$sender';";
	$db->query($sql);
	if ($db->numrows() == 0) {
		$chatBot->send("Name '$sender' is not in the DB yet. Does the name you registered with match your ingame name? Did you log into website after activation?", $sendto);
		return;
	} else {
		$row = $db->fObject();
		if ($email == strtolower($row->email)) {
			$ag_id = $row->id;
			$sql = "SELECT id, role_id FROM omnihqdb.jos_agora_user_group WHERE user_id = $ag_id AND group_id = $group_id_confirmed;";
			$db->query($sql);
			if ($db->numrows() == 0) {
				$sql = "INSERT INTO omnihqdb.jos_agora_user_group (user_id, group_id, role_id) VALUES ($ag_id, $group_id_confirmed, $role_id);";
				$db->exec($sql);
				if ($whois->level >=150) {
					$sql = "INSERT INTO omnihqdb.jos_agora_user_group (user_id, group_id, role_id) VALUES ($ag_id, $group_id_150, $role_id);";
					$db->exec($sql);
					
					$sql = "SELECT * FROM taratime.tara_points WHERE name='{$sender}' AND forums=0;";
					$db->query($sql);
					if ($db->numrows()>0){
						$db->exec("UPDATE taratime.tara_points SET forums=1 WHERE name='{$sender}';");
						$db->exec("INSERT INTO omnihqdb.jos_agora_user_group (`user_id`, `group_id`, `role_id`) VALUES ($ag_id, $group_id_tara, $role_id);");
						$chatBot->send("<green>You were added to Taratime section at www.omnihq.net and awarded 20 bonus points.<end>",$sender);
					}					
					
					if ($whois->level >=205) {
						$sql = "INSERT INTO omnihqdb.jos_agora_user_group (user_id, group_id, role_id) VALUES ($ag_id, $group_id_205, $role_id);";
						$db->exec($sql);
					}
					$sql = "SELECT id, role_id FROM omnihqdb.jos_agora_user_group WHERE user_id = $ag_id AND group_id = $group_id_wc;";
					$db->query($sql); 
					if ($db->numrows()==0){
						$sql = "SELECT * FROM warbot.members_warleaders WHERE name = $ag_id;";
						$db->query($sql);
						if ($db->numrows()!=0){
							$sql = "INSERT INTO omnihqdb.jos_agora_user_group (user_id, group_id, role_id) VALUES ($ag_id, $group_id_wc, $role_id);";
							$db->exec($sql);
						}
					}
					$org=str_replace("'","''",$whois->guild);
					$sql = "UPDATE omnihqdb.jos_agora_users Set orgname='$org', orgrank='$whois->guild_rank' WHERE id='$ag_id';";
					$db->exec($sql);
					$chatBot->send("You have been confirmed.", $sendto);
					return;
				}
			return;
			} else {
				$sql = "SELECT id, role_id FROM omnihqdb.jos_agora_user_group WHERE user_id = '$ag_id' AND group_id = $group_id_205;";
				$db->query($sql);
				if (($db->numrows() == 0)&&($whois->level >=205)){
					$chatBot->send("You have been updated as 205+.", $sendto);
					return;
				}
				else {
					$chatBot->send("You are already confirmed. Use /tell <myname> !update", $sendto);
					return;
				}
			}
		} else {
			$chatBot->send("E-mail '$email' is not the one you have registered with.", $sendto);
			return;
		}
	}
} else {
	$syntax_error = true;
}

?>
