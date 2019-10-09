<?php
if (preg_match("/^sync$/i", $message, $arr)) {
//	$chatBot->send("Sync attempt: " . (Player::sync_attempt()?"Success":"Failure"),$sender);
	
	if(Player::sync_attempt()) $chatBot->send("Sync succesful.",$sender);
	else $chatBot->send("Sync failed.",$sender);
	
	
	$db = DB::get_instance();
	$sql = "SELECT name FROM players WHERE last_update<" . (time() - 604800) . " ORDER BY last_update ASC LIMIT 1;";
	$db->query($sql);
	if($db->numrows()===0){
		$chatBot->send("All players are up to date.",$sender);
		return;
	}
	$result = $db->fObject();
	$name = $result->name;
	
	$charid = $chatBot->get_uid($name);
	if ($charid !== null && !empty($charid)) {
		$player = Player::lookup($name, $chatBot->vars['dimension']);
		if ($player->name===$name) {
			$player->charid = $charid;
			Player::update($player);
			$chatBot->send("Updated player <highlight>{$name}<end> with XML",$sender);
		} else {
			$chatBot->send("Could not access XML for player <highlight>{$name}<end>",$sender);
		}
	} else {
		$db->exec("DELETE FROM {$playersdb} WHERE name LIKE '{$name}';");
		if($db->numrows()!==0)
			$chatBot->send("Could not find player <highlight>{$name}<end>. Deleted",$sender);
		else
			$chatBot->send("Failed deleting player <highlight>{$name}<end>.",$sender);
	}

} else if (preg_match("/^sync ([a-z0-9- ]+)$/i", $message, $arr)) {
//	$chatBot->send("Sync attempt: " . (Player::sync_attempt()?"Success":"Failure"),$sender);
	$name = ucfirst(strtolower($arr[1]));
	
	$charid = $chatBot->get_uid($name);
	if ($charid !== null && !empty($charid)) {
		$player = Player::lookup($name, $chatBot->vars['dimension']);
		if ($player->name===$name) {
			$player->charid = $charid;
			Player::update($player);
			$chatBot->send("Updated player <highlight>{$name}<end> with XML",$sender);
		} else {
			$chatBot->send("Could not access XML for player <highlight>{$name}<end>",$sender);
		}
	} else {
		$db->exec("DELETE FROM {$playersdb} WHERE name LIKE '{$name}';");
		if($db->numrows()!==0)
			$chatBot->send("Could not find player <highlight>{$name}<end>. Deleted",$sender);
		else
			$chatBot->send("Failed deleting player <highlight>{$name}<end>.",$sender);
	}

}	

?>
