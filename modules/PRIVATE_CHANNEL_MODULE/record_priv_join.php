<?php

if ($type == "joinPriv") {
	$db->query("SELECT name FROM online_<myname> WHERE `name` = '$sender' AND `channel_type` = 'priv' AND added_by = '<myname>'");
	if ($db->numrows() == 0) {
	    $db->exec("INSERT INTO online_<myname> (`name`, `channel`,  `channel_type`, `added_by`, `dt`) VALUES ('$sender', '<myguild> Guests', 'priv', '<myname>', " . time() . ")");
	}
	if(Setting::get('add_to_members_on_join')==1){
		$db->query("SELECT * FROM members_<myname> WHERE `name` = '$sender'");
		if ($db->numrows() == 0){
			$db->exec("INSERT INTO members_<myname> (`name`, `autoinv`) VALUES ('$sender', 1)");
		}
		Buddylist::add($sender, 'member');
	}
	
	$inraid = Tara::in_raidlist($sender);
	if($inraid!==false){
		if($inraid != $sender){
			// remove old
			unset($chatBot->data["TARA_MODULE"]["raidlist"][$inraid]);
			$db->exec("DELETE FROM tara_raidlist WHERE name LIKE '{$inraid}';");
			// add to raid
			$cat = Tara::raid_add($sender);
			$msg = "<yellow>{$inraid} was replaced with {$sender} in raidlist.<end>";
			if ($cat){
				if ($cat != 4 ) $msg .= " <red>[Limited points]<end>";
			} else {
				$msg = " <orange>Failed adding {$sender} to raidlist<end>";
			}
			$chatBot->send($msg,'priv');
		}
		
		$db->exec("UPDATE tara_raidlist SET leave_time=-1 WHERE name='{$sender}';");
		if (isset($chatBot->data["TARA_MODULE"]["raidlist"][$sender])){
			unset($chatBot->data["TARA_MODULE"]["raidlist"][$sender]);
		}
	}
	
	$account = Tara::get_account_name($sender);
	$db->query("SELECT * FROM tara_points WHERE name='{$account}';");
	$row = $db->fObject();
	if($row->forums==1){
		$sql = "SELECT ag.id, jo.email,ag.last_visit FROM omnihqdb.jos_users jo JOIN omnihqdb.jos_agora_users ag ON jo.username = ag.username LEFT JOIN omnihqdb.jos_agora_user_group gr ON gr.user_id=ag.id WHERE jo.name='$account' AND gr.group_id = 6 AND gr.role_id!=1;";
		$db->query($sql);
		$visit = $db->fObject()->last_visit;
	}
	if( $row->forums!=1 || $visit < (time()-31*24*3600) ){
		if ($row->forums==1 && $visit!==NULL) $reason = "You haven't logged to website for over a month";
		else $reason = "You do not have forum account or it is not updated with Taratime";
		
		$info = "<header>:::: Warning :::<end>\n\nYou must register and make sure you have access to omnihq.net Taratime section before server merge in order for your points to be translated to new bot.\n\nYou are getting this message because:\n<yellow>{$reason}<end>\n\nGo to " . Text::make_link("www.omnihq.net","/start http://www.omnihq.net",'chatcmd') . " and fix it or " . Text::make_link("contact admin","/tell Toxicmen",'chatcmd') . " if you cannot work it out.\n\nAdditional information and discussion thread available at: " . Text::make_link("http://www.omnihq.net/forums/","/start http://www.omnihq.net/forums/topic?id=218",'chatcmd');
		$msg = "<red>Warning: You have problems with your forum registration - must be resolved before server merge in order for your points to be translated.<end> (" . Text::make_link("More info",$info,'blob') . ")";
		$chatBot->send($msg,$sender);
	}
}

?>