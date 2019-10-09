<?php
/*
	$db = DB::get_instance();
		
	$db->query("SELECT * FROM banlist_<myname>");
	$data = $db->fObject('all');
	forEach ($data as $row) {
		if($row->org_ban==0) $name = Player::get_by_id($row->char_id);
		else $name = Guild::get_by_id($row->char_id);
		if ($name!=$row->name)  // replace name if it changed
			$db->exec("UPDATE banlist SET name='{$name}' WHERE char_id='{$row->id}';");
	}
*/

?>