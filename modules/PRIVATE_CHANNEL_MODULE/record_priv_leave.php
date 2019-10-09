<?php

if ($type == "leavePriv") {
	$db->exec("DELETE FROM online_<myname> WHERE `name` = '$sender' AND `channel_type` = 'priv' AND added_by = '<myname>'");
	if(Setting::get('add_to_members_on_join')==1){
		$db->exec("DELETE FROM members_<myname> WHERE `name` = '$sender'");
		Buddylist::remove($sender, 'member');	
	}
}

?>