<?php

if (preg_match("/^replaceraid ([0-9]+) ([a-z]+)$/i", $message, $arr)) {

	$raid_id = $arr[1];
	
	$db->query("SELECT * FROM tara_raid_history WHERE id={$raid_id};");
	if($db->numrows()==0){
		$chatBot->send("Raid {$raid_id} not found.",$sendto);		
		return;
	}
	$raid = $db->fObject();

	$raid_name = strtolower($arr[2]);
	// get raid id
	$db->query("SELECT * FROM tara_raids WHERE short_name LIKE '{$raid_name}';");
	if($db->numrows()==0){
		$chatBot->send("Invalid raid specified.",$sendto);
		return;
	}
	$new_raid = $db->fObject();
	
	$db->exec("UPDATE tara_raid_history SET type='{$new_raid->short_name}' WHERE id={$raid->id};");
	$db->query("SELECT * FROM tara_points_history WHERE raid_id={$raid->id} AND item='';");
	
	$data=$db->fObject('all');
	foreach($data as $row){
		// remove invalid points given
		$db->exec("UPDATE tara_points SET points=points-{$row->change} WHERE name='{$row->account}';");

		// get raid category
		$whois = Player::get_by_name($row->name);
		if ($whois){
			$level = $whois->level;
		} else $level = 1;
		$db->query("SELECT * FROM tara_raid_categories WHERE min_level<={$whois->level} AND max_level>={$whois->level};");
		if ($db->numrows()===0) $cat_row->cat_id = 0;
		else $cat_row = $db->fObject();
		
		$category = 'points_cat_' . $cat_row->cat_id;
		$points = $new_raid->$category;
		
		// add new correct points
		$db->exec("UPDATE tara_points SET points=points+{$points} WHERE name='{$row->account}';");
		
		// record change to history
		$db->exec("UPDATE tara_points_history SET `change`={$points} WHERE id={$row->id};");
	}

	$chatBot->send("Replaced raid successfully.",$sendto);
} else if (preg_match("/^undoraid ([0-9]+)$/i", $message, $arr)) {

	$raid_id = $arr[1];
	
	$db->query("SELECT * FROM tara_raid_history WHERE id={$raid_id};");
	if($db->numrows()==0){
		$chatBot->send("Raid {$raid_id} not found.",$sendto);		
		return;
	}
	$raid = $db->fObject();
	$db->query("SELECT * FROM tara_points_history WHERE raid_id={$raid->id} AND item!='';");
	if($db->numrows()!=0){
		$chatBot->send("Cannot remove raid where items were won.",$sendto);		
		return;
	}
	
	$db->exec("DELETE FROM tara_raid_history WHERE id={$raid->id};");
	$db->query("SELECT * FROM tara_points_history WHERE raid_id={$raid->id};");
	
	$data=$db->fObject('all');
	foreach($data as $row){
		// remove invalid points given
		$db->exec("UPDATE tara_points SET points=points-{$row->change} WHERE name='{$row->account}';");
	}
	$db->query("DELETE FROM tara_points_history WHERE raid_id={$raid->id};");
	
	$chatBot->send("Raid removed successfully.",$sendto);
	
} else if (preg_match("/^mergetomain ([a-z0-9-]+)$/i", $message, $arr)) {

	$name = ucfirst(strtolower($arr[1]));
	// check if character owns the points account
	$account = Tara::get_account_name($name);
	if($name!=$account){
		$chatBot->send("{$name} is not owner of the account {$account}.",$sendto);
		return;
	}
	
	$main = Alts::get_main($name);
	if($name==$main){
		$chatBot->send("{$name} is main character.",$sendto);
		return;
	}
	
	// check if character registered
	$points = Tara::get_points($name);
	if ($points===false) {
		$chatBot->send("{$name} is not registered.",$sendto);
		return;
	}
	// check if main registered
	$mainpoints = Tara::get_points($main);
	if ($points===false) {
		$chatBot->send("Main '{$main}' is not registered.",$sendto);
		return;
	}
	
	// check if items were won
	$db->query("SELECT * FROM tara_points_history WHERE account LIKE '{$name}' AND item!='';");
	if($db->numrows()!=0){
		$chatBot->send("Cannot remove raid where items were won.",$sendto);		
		return;
	}
	
	// find sum of pts to remove
	$db->query("SELECT h1.* FROM tara_points_history h1 LEFT JOIN (SELECT raid_id FROM tara_points_history WHERE account LIKE '{$main}') h2 ON h1.raid_id=h2.raid_id WHERE h1.account LIKE '{$name}' AND h2.raid_id IS NOT NULL;");
	
	$data=$db->fObject('all');
	// raids where both toons were present
	foreach($data as $row){
		// remove points given
		$db->exec("UPDATE tara_points SET points=points-{$row->change} WHERE name='{$name}';");
		// delete from history
		$db->exec("DELETE FROM tara_points_history WHERE id={$row->id};");
	}
	$chatBot->send("Points removed successfully.",$sendto);
	
} else {
	$syntax_error = true;
}

?>