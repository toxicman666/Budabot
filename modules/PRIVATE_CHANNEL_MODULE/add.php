<?php


if (preg_match("/^adduser ([a-z0-9-]+)$/i", $message, $arr)) {
	$uid = $chatBot->get_uid($arr[1]);
	$name = ucfirst(strtolower($arr[1]));
	if (!$uid) {
		$msg = "Player <highlight>$name<end> does not exist.";
	} else {
		$db->query("SELECT * FROM members_<myname> WHERE `name` = '$name'");
		if ($db->numrows() != 0) {
			$msg = "<highlight>$name<end> is already a member of this bot.";
		} else {
			$whois = Player::get_by_name($name);
			if($whois->faction=="Clan"){
				$msg = "<orange>Clanners have their own bot.<end>";
				$chatBot->send($msg, $sendto);
				return;
			} else {
				$forum="";
				if(Setting::get("alts_wc_members")>0){
					$main = Alts::get_main($name);
					if (Alts::is_wc_member($name)) {
						$chatBot->send("One of <highlight>$name<end>'s alts is already a member of this bot.",$sender);
						return;
					}
					$alts = Alts::get_alts($main);
					$alts[]=$main;
					$group_id_wc = 3; // warleaders forums group
					$group_id_confirmed = 4;  // confirmed users
					$role_id = 2;
					foreach($alts as $alt){
						$sql = "SELECT ag.id FROM omnihqdb.jos_users jo JOIN omnihqdb.jos_agora_users ag ON jo.username = ag.username LEFT JOIN omnihqdb.jos_agora_user_group gr ON gr.user_id=ag.id WHERE jo.name='$alt' AND gr.group_id = $group_id_confirmed;";
						$db->query($sql);
						if ($db->numrows() !== 0) {
							$row = $db->fObject();
							$ag_id = $row->id;
							$db->exec("INSERT INTO omnihqdb.jos_agora_user_group (user_id, group_id, role_id) VALUES ($ag_id, $group_id_wc, $role_id);");
							$forum=" <highlight>$alt<end> was added to WC forums.";
						}					
					}
					$name = $main;
				}
				$db->exec("INSERT INTO members_<myname> (`name`, `autoinv`) VALUES ('$name', 1)");
				$msg = "<highlight>$name<end> has been added as a member of this bot.$forum";
			}
		}

		// always add in case 
		Buddylist::add($name, 'member');
	}

	$chatBot->send($msg, $sendto);
} else {
	$syntax_error = true;
}

?>