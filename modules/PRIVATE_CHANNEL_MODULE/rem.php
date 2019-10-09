<?php


if (preg_match("/^remuser (.+)$/i", $message, $arr)) {
	$uid = $chatBot->get_uid($arr[1]);
	$name = ucfirst(strtolower($arr[1]));
    if (!$uid) {
        $msg = "Player <highlight>{$name}<end> does not exist.";
    } else {
	  	$db->query("SELECT * FROM members_<myname> WHERE `name` = '$name';");
	  	if ($db->numrows() == 0) {
	  		$msg = "<highlight>$name<end> is not a member of this bot.";
	  	} else {
			if(Setting::get("alts_wc_members")>0){
				$main = Alts::get_main($name);
			//	if($main==$name){
				$db->exec("UPDATE alts SET wc=0 WHERE main='{$main}';");
				$alts = Alts::get_alts($main);
				$alts[]=$main;
				$group_id_wc = 3; // warleaders forums group
				$forum="";
				foreach($alts as $alt){
					$db->exec("DELETE FROM members_<myname> WHERE `name` = '{$alt}'");
					Buddylist::remove($alt, 'member');
					
					$sql = "SELECT ag.id FROM omnihqdb.jos_users jo JOIN omnihqdb.jos_agora_users ag ON jo.username = ag.username LEFT JOIN omnihqdb.jos_agora_user_group gr ON gr.user_id=ag.id WHERE jo.name='$alt' AND gr.group_id = $group_id_wc;";
					$db->query($sql);
					if ($db->numrows() !== 0) {
						$row = $db->fObject();
						$ag_id = $row->id;
						$db->exec("DELETE FROM omnihqdb.jos_agora_user_group WHERE user_id={$ag_id} AND group_id={$group_id_wc};");
						$forum=" <highlight>$alt<end> was removed from WC forums.";
					}					
				}
				$chatBot->send("All alts of <highlight>$name<end> were removed from bot.$forum",$sendto);
				return;
			//	}
			}
			
		//	$db->exec("UPDATE alts SET wc=0 WHERE alt='{$name}';");
		    $db->exec("DELETE FROM members_<myname> WHERE `name` = '$name';");
		    $msg = "<highlight>$name<end> has been removed from bot.";
			Buddylist::remove($name, 'member');
		}
	}

	$chatBot->send($msg, $sendto);
} else {
	$syntax_error = true;
}

?>