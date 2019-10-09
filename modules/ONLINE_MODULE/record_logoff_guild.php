<?php

$db->query("SELECT * FROM members_<myname> WHERE name='$sender';");
if ($db->numrows()!=0) {
	if ($chatBot->is_ready())
		$db->exec("UPDATE members_<myname> SET `logged_off` = '".time()."' WHERE `name` = '$sender';");
}

if (isset($chatBot->guildmembers[$sender])) {
    $db->exec("DELETE FROM `online_<myname>` WHERE `name` = '$sender' AND `channel_type` = 'guild' AND added_by = '<myname>'");
    if ($chatBot->is_ready())
        $db->exec("UPDATE org_members_<myname> SET `logged_off` = '".time()."' WHERE `name` = '$sender';");
}
?>
