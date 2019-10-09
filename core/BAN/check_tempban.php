<?php

forEach ($chatBot->banlist as $ban){
	if ($ban->banend != null && ((time() - $ban->banend) >= 0)) {
	 	 $db->exec("DELETE FROM banlist WHERE name = '{$ban->name}'");
		 
		// unban on forums
		$main = Alts::get_main($ban->name);		
		$alts = Alts::get_alts($main);
		// forums
		// check if main registered
		$sql = "SELECT ag.id, ag.email FROM omnihqdb.jos_users jo JOIN omnihqdb.jos_agora_users ag ON jo.username = ag.username WHERE jo.name='$main'";
		$db->query($sql);
		if ($db->numrows() != 0) {
			$row = $db->fObject();
			$ag_id = $row->id;
			$sql = "UPDATE omnihqdb.jos_agora_user_group SET role_id=2 WHERE user_id={$ag_id} AND role_id=1;";
			$db->exec($sql);
		}
		// check if alts registered
		foreach($alts as $alt){
			$sql = "SELECT ag.id, ag.email FROM omnihqdb.jos_users jo JOIN omnihqdb.jos_agora_users ag ON jo.username = ag.username WHERE jo.name='$alt'";
			$db->query($sql);
			if ($db->numrows() != 0) {
				$row = $db->fObject();
				$ag_id = $row->id;
				$sql = "UPDATE omnihqdb.jos_agora_user_group SET role_id=2 WHERE user_id={$ag_id} AND role_id=1;";
				$db->exec($sql);
			}
		}
	}
}

Ban::upload_banlist();

?>