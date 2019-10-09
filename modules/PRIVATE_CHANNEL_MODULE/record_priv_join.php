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
}

?>