<?php

if (isset($chatBot->guildmembers[$sender])) {
  	$db->query("SELECT name FROM `online_<myname>` WHERE `name` = '$sender' AND `channel_type` = 'guild' AND added_by = '<myname>'");
	if ($db->numrows() == 0) {
		$sql = "INSERT INTO `online_<myname>` (`name`, `channel`,  `channel_type`, `added_by`, `dt`) VALUES ('$sender', '<myguild>', 'guild', '<myname>', " . time() . ")";
	    $db->exec($sql);
	}

	// update info for player
	Player::get_by_name($sender);
}

?>
