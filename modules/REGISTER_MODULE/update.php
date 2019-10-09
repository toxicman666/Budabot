<?php
$group_id_confirmed = 4;  // confirmed users
$group_id_150 = 7; // 150+
$group_id_205 = 5; // 205+
$group_id_wc = 3; // warleaders
$group_id_mod = 8; // moderators
$group_id_tara = 6; // tara
$role_id = 2;

if (preg_match("/^update$/i", $message)) {
	$upd=0;
	if(Player::sync_attempt($sender)){
		$chatBot->send("Your character bio has been updated with FunCom XML.",$sender);
		$upd++;
	} else {
		$chatBot->send("<orange>Failed loading FunCom XML</orange>",$sender);
		$upd++;
	}
	
	$whois = Player::get_by_name($sender);
	if ($whois->faction == 'clan') {
		$chatBot->send("<orange>Clans can not register in this bot.<end>",$sender);
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
				if ($upd == 0) $chatBot->send("<orange>Your alt $alt is already registered with OmniHQ. Aborting<end>",$sender);
				return;
			}
		}
	}
	if($main!=$sender){
		$sql = "SELECT ag.id, jo.email FROM omnihqdb.jos_users jo JOIN omnihqdb.jos_agora_users ag ON jo.username = ag.username LEFT JOIN omnihqdb.jos_agora_user_group gr ON gr.user_id=ag.id WHERE jo.name='$main' AND gr.group_id = $group_id_confirmed;";
		$db->query($sql);
		if ($db->numrows() != 0) {
			if ($upd == 0) $chatBot->send("<orange>Your main $main is already registered with OmniHQ. Aborting<end>",$sender);
			return;
		}
	}
	
	// warbot bans
	$sql = "SELECT name FROM taratime.banlist_<myname> WHERE name = '$sender';";
	$db->query($sql);
	if ($db->numrows() > 0) {
		$chatBot->send("<orange>You are banned from this network.<end>");
		return;
	}
	$sql = "SELECT ag.id, jo.email, orgname, orgrank FROM omnihqdb.jos_users jo JOIN omnihqdb.jos_agora_users ag ON jo.username = ag.username WHERE jo.name = '$sender';";
	$db->query($sql);
	if($db->numrows()===0){
		$chatBot->send("<orange>{$sender} is not registered at www.omnihq.net<end>",$sendto);
		return;
	}
	$row = $db->fObject();
	$ag_id = $row->id;
	
	$sql = "SELECT id, group_id FROM omnihqdb.jos_agora_user_group WHERE user_id = $ag_id AND group_id = $group_id_confirmed AND role_id!=1;";
	$db->query($sql);
	// check if the user is confirmed
	if ($db->numrows() == 0) {
		$chatBot->send("Username '$sender' had not been confirmed yet. Use: !confirm &lt;e-mail&gt;", $sendto);
		return;
	} else {
		// check if the org info is correct
		if ($row->orgname != $whois->guild || $row->orgrank != $whois->guild_rank) {
			$org=str_replace("'","''",$whois->guild);
			$sql = "UPDATE omnihqdb.jos_agora_users Set orgname = '$org', orgrank = '$whois->guild_rank' WHERE id = '$ag_id';";
			$db->exec($sql);
			$chatBot->send("Your org info has been updated.", $sendto);
			$upd++;
		}
		if ($whois->level >= 150) {
			$sql = "SELECT id, group_id FROM omnihqdb.jos_agora_user_group WHERE user_id = $ag_id AND group_id = $group_id_150;";
			$db->query($sql);
			// if the user is not in 150+ group, add him
			if ($db->numrows() == 0){
				$sql = "INSERT INTO omnihqdb.jos_agora_user_group (user_id, group_id, role_id) VALUES ($ag_id, $group_id_150, $role_id);";
				$db->exec($sql);
				$chatBot->send("You have been updated as 150+.", $sendto);
				$upd++;
			}	
			$sql = "SELECT * FROM taratime.tara_points WHERE name='{$sender}' AND forums=0;";
			$db->query($sql);
			if ($db->numrows() > 0){
				$db->exec("UPDATE taratime.tara_points SET forums=1 WHERE name='{$sender}';");
				$db->exec("INSERT INTO omnihqdb.jos_agora_user_group (`user_id`, `group_id`, `role_id`) VALUES ($ag_id, $group_id_tara, $role_id);");
				$chatBot->send("<white>You were added to Taratime section at www.omnihq.net and awarded 20 bonus points.<end>",$sender);
				$upd++;
			}
				
			if ($whois->level >= 205){
				$sql = "SELECT id, group_id FROM omnihqdb.jos_agora_user_group WHERE user_id = $ag_id AND group_id = $group_id_205;";
				$db->query($sql);
				// if the user is not in 205+ group, add him
				if ($db->numrows() == 0){
					$sql = "INSERT INTO omnihqdb.jos_agora_user_group (user_id, group_id, role_id) VALUES ($ag_id, $group_id_205, $role_id);";
					$db->exec($sql);				
					$chatBot->send("You have been updated as 205+.", $sendto);
					$upd++;
				}
			}
		} 
		$sql = "SELECT * FROM warbot.members_warleaders WHERE name = '$sender';";
		$db->query($sql); 
		if ($db->numrows()!=0){
			$sql = "SELECT id, role_id FROM omnihqdb.jos_agora_user_group WHERE user_id = $ag_id AND group_id = $group_id_wc;";
			$db->query($sql);
			if ($db->numrows()==0){
			  $sql = "INSERT INTO omnihqdb.jos_agora_user_group (user_id, group_id, role_id) VALUES ($ag_id, $group_id_wc, $role_id);";
			  $db->exec($sql);
					  $chatBot->send("You have been added to WarCouncil forum.", $sendto);
			  $upd++;
			}
		}
		$sql = "SELECT * FROM warbot.admin_warbot WHERE `name` = '$sender' AND `adminlevel` = 3;";
		$db->query($sql); 
		if ($db->numrows()!=0){
			$sql = "SELECT id, role_id FROM omnihqdb.jos_agora_user_group WHERE user_id = $ag_id AND group_id = $group_id_mod;";
			$db->query($sql);
			if ($db->numrows()==0){
			  $sql = "INSERT INTO omnihqdb.jos_agora_user_group (user_id, group_id, role_id) VALUES ($ag_id, $group_id_mod, $role_id);";
			  $db->exec($sql);
					  $chatBot->send("You have been added to Moderators forum.", $sendto);
			  $upd++;
			}
		}
		$sql = "SELECT * FROM taratime.admin_taratime WHERE `name` = '$sender' AND `adminlevel` = 3;";
		$db->query($sql); 
		if ($db->numrows()!=0){
			$sql = "SELECT id, role_id FROM omnihqdb.jos_agora_user_group WHERE user_id = $ag_id AND group_id = $group_id_mod;";
			$db->query($sql);
			if ($db->numrows()==0){
			  $sql = "INSERT INTO omnihqdb.jos_agora_user_group (user_id, group_id, role_id) VALUES ($ag_id, $group_id_mod, $role_id);";
			  $db->exec($sql);
					  $chatBot->send("You have been added to Moderators forum.", $sendto);
			  $upd++;
			}
		}				
	if ($upd == 0) {
		$chatBot->send("You have nothing to update.", $sendto);
	}
	return;
	}
} else if (preg_match("/^update all$/i", $message)) {
	$sql = "SELECT ag.id, orgrank, orgname, jo.name FROM omnihqdb.jos_users jo JOIN omnihqdb.jos_agora_users ag ON jo.username = ag.username LEFT JOIN omnihqdb.jos_agora_user_group gr ON gr.user_id=ag.id WHERE gr.group_id = $group_id_confirmed AND gr.role_id!=1;";
	$db->query($sql);
	$users = $db->fObject('all');
	$upd=0;
	$tara=0;
	forEach ($users as $user) {
		$whois = Player::get_by_name($user->name);
		if ($whois->level >= 150) {
			// check tara member
			$sql = "SELECT * FROM tara_points WHERE `name` = '{$user->name}';";
			$db->query($sql);
			if ($db->numrows()!==0){
				$sql = "SELECT id, group_id FROM omnihqdb.jos_agora_user_group WHERE user_id = $user->id AND group_id = $group_id_tara;";
				$db->query($sql);
				// if not in  tara, add
				if ($db->numrows() == 0){			
					$db->exec("INSERT INTO omnihqdb.jos_agora_user_group (`user_id`, `group_id`, `role_id`) VALUES ($user->id, $group_id_tara, $role_id);");
					$tara++;
				}
			}		
			$sql = "SELECT id, group_id FROM omnihqdb.jos_agora_user_group WHERE user_id = $user->id AND group_id = $group_id_150;";
			$db->query($sql);
			// if the user is not in 150+ group, add him
			if ($db->numrows() == 0){
				$sql = "INSERT INTO omnihqdb.jos_agora_user_group (user_id, group_id, role_id) VALUES ($user->id, $group_id_150, $role_id);";
				$db->exec($sql);
				$upd++;
				if ($whois->level >= 205){
					$sql = "SELECT id, group_id FROM omnihqdb.jos_agora_user_group WHERE user_id = $user->id AND group_id = $group_id_205;";
					$db->query($sql);
					// if the user is not in 205+ group, add him
					if ($db->numrows() == 0){
						$sql = "INSERT INTO omnihqdb.jos_agora_user_group (user_id, group_id, role_id) VALUES ($user->id, $group_id_205, $role_id);";
						$db->exec($sql);				
						$upd++;
					}
				}
			}
		} 		
		$sql = "UPDATE omnihqdb.jos_agora_users SET orgname = '$whois->guild', orgrank = '$whois->guild_rank' WHERE id = $user->id;";
		$db->exec($sql);
	}
	$t="";
	if ($tara!=0) $t=" Added {$tara} users to Tara group.";
	$chatBot->send("Updated " . count($users) . " orgs, ranks and {$upd} other entries.$t", $sendto);
} else {
	$syntax_error = true;
}

?>
