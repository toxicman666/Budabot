<?php


if (preg_match("/^leave$/i", $message) || preg_match("/^kick$/i", $message)) {
	if($chatBot->data["TARA_MODULE"]["raid_status"]!=0){
		if($db->exec("DELETE FROM tara_raidlist WHERE name='{$sender}';")!==0){
			unset($chatBot->data["TARA_MODULE"]["raidlist"][$sender]);
			$chatBot->send("<yellow>You have been removed from raidlist<end>.",$sender);
			$kick=true;
		}
	}
	$chatBot->privategroup_kick($sender);
	if ($kick) $chatBot->send("<yellow>{$sender} has been removed from raidlist<end> (left)",'priv');
}

?>
