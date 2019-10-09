<?php

class Ban {
	public static function add($char, $sender, $length, $reason, $orgban=0) {
		$db = DB::get_instance();
		$group_id_tara = 6; // tara	
		
		if ($orgban==0){
			// ban from forums (make guest in all groups)
			$main = Alts::get_main($char);
			$char = $main;
			$alts = Alts::get_alts($main);
			// forums
			// check if main registered
			$sql = "SELECT ag.id, ag.email FROM omnihqdb.jos_users jo JOIN omnihqdb.jos_agora_users ag ON jo.username = ag.username WHERE jo.name='$main'";
			$db->query($sql);
			if ($db->numrows() != 0) {
				$row = $db->fObject();
				$ag_id = $row->id;
				$sql = "UPDATE omnihqdb.jos_agora_user_group SET role_id=1 WHERE user_id={$ag_id} AND group_id='{$group_id_tara}';";
				$db->exec($sql);
			}
			// check if alts registered
			foreach($alts as $alt){
				$sql = "SELECT ag.id, ag.email FROM omnihqdb.jos_users jo JOIN omnihqdb.jos_agora_users ag ON jo.username = ag.username WHERE jo.name='$alt'";
				$db->query($sql);
				if ($db->numrows() != 0) {
					$row = $db->fObject();
					$ag_id = $row->id;
					$sql = "UPDATE omnihqdb.jos_agora_user_group SET role_id=1 WHERE user_id={$ag_id} AND group_id='{$group_id_tara}';";
					$db->exec($sql);
				}
			}
		}
		
		if ($length == null) {
			$ban_end = "NULL";
		} else {
			$ban_end = time() + $length;
		}
		$reason = str_replace("'", "''", $reason);
		if($orgban==0){
			$whois = Player::get_by_name($char);
			$charid = $whois->charid;
		} else {
			$guild_name = str_replace("'", "''", $char);
			
			$playersdb = Player::get_players_db();
			$sql = "SELECT DISTINCT guild, guild_id, CASE WHEN guild_id = '' THEN 0 ELSE 1 END AS sort FROM {$playersdb} WHERE guild LIKE '%{$guild_name}%' ORDER BY sort DESC, guild ASC LIMIT 1";
			$db->query($sql);
			if ($db->numrows() != 0) {
				$row = $db->fObject();
				if ($row->guild_id != '') $charid = $row->guild_id;
			}
		}
		
		$sql = "INSERT INTO banlist_<myname> (`name`, `admin`, `time`, `reason`, `banend`, `char_id`, `org_ban`) VALUES ('{$char}', '{$sender}', '".time()."', '{$reason}', {$ban_end}, {$charid}, {$orgban})";
		$numrows = $db->exec($sql);
		$sql = "INSERT INTO banhistory_<myname> (`name`, `admin`, `time`, `reason`, `banend`, `length`, `char_id`, `org_ban`) VALUES ('{$char}', '{$sender}', '".time()."', '{$reason}', '{$ban_end}', " . ($length==NULL?"NULL":$length) . ", " . ($charid==NULL?"NULL":$charid) . ", {$orgban})";
		$db->exec($sql);
		
		Ban::upload_banlist();
		
		return $numrows;
	}
	
	public static function remove($char,$orgban=0,$sender="N/A") {
		$db = DB::get_instance();
		$db->query("SELECT * FROM banlist_<myname> WHERE name='{$char}' AND org_ban={$orgban};");
		if($db->numrows()>0){
			$row = $db->fObject();
			$sql = "INSERT INTO banhistory_<myname> (`name`, `admin`, `time`, `reason`, `banend`, `length`, `char_id`, `org_ban`, `wasbannedby`) VALUES ('{$char}', '{$sender}', '".time()."', '{$row->reason}', " . ($row->banend==NULL?"NULL":$row->banend) . ", " . ($row->banend==NULL?"NULL":($row->banend - $row->time)) . ", {$row->char_id}, {$row->org_ban}, '{$row->admin}')";
			$db->exec($sql);
		}
		
		$group_id_tara = 6; // tara	
		$group_id_150 = 7; // 150+
		
		if($orgban==0){
			// unban on forums
			$main = Alts::get_main($char);
			$char = $main;
			$alts = Alts::get_alts($main);
			// forums
			// check if main registered
			$sql = "SELECT ag.id, ag.email FROM omnihqdb.jos_users jo JOIN omnihqdb.jos_agora_users ag ON jo.username = ag.username WHERE jo.name='$main'";
			$db->query($sql);
			if ($db->numrows() != 0) {
				$row = $db->fObject();
				$ag_id = $row->id;
				$db->query("SELECT * FROM omnihqdb.jos_agora_user_group WHERE user_id={$ag_id} AND role_id=1 AND group_id='{$group_id_150}';");
				if ($db->numrows()===0){	// if not banned on other forums, unban
					$sql = "UPDATE omnihqdb.jos_agora_user_group SET role_id=2 WHERE user_id={$ag_id} AND role_id=1 AND group_id='{$group_id_tara}';";
					$db->exec($sql);
				}
			}
			// check if alts registered
			foreach($alts as $alt){
				$sql = "SELECT ag.id, ag.email FROM omnihqdb.jos_users jo JOIN omnihqdb.jos_agora_users ag ON jo.username = ag.username WHERE jo.name='$alt'";
				$db->query($sql);
				if ($db->numrows() != 0) {
					$row = $db->fObject();
					$ag_id = $row->id;
					$db->query("SELECT * FROM omnihqdb.jos_agora_user_group WHERE user_id={$ag_id} AND role_id=1 AND group_id='{$group_id_150}';");
					if ($db->numrows()===0){	// if not banned on other forums, unban
						$sql = "UPDATE omnihqdb.jos_agora_user_group SET role_id=2 WHERE user_id={$ag_id} AND role_id=1 AND group_id='{$group_id_tara}';";
						$db->exec($sql);
					}
				}
			}
		}

		$sql = "DELETE FROM banlist_<myname> WHERE name = '{$char}' AND org_ban={$orgban};";
		$numrows = $db->exec($sql);		
		Ban::upload_banlist();
		
		return $numrows;
	}
	
	public static function upload_banlist() {
		$db = DB::get_instance();
		global $chatBot;
		
		$chatBot->banlist = array();
		
		$db->query("SELECT * FROM banlist_<myname>");
		$data = $db->fObject('all');
		forEach ($data as $row) {
			$chatBot->banlist[$row->name] = $row;
		}
	}
	
	public static function is_banned($char,$orgban=0) {
		global $chatBot;
		$char_sql = str_replace("'", "''", $char);
		$db = DB::get_instance();		
		if(isset($chatBot->banlist[$char])) return true;
		if ($orgban==0) {
			// check main
		  	$main = Alts::get_main($char);
			if(isset($chatBot->banlist[$main])) return true;
			
			// check orgban
			$whois = Player::get_by_name($char);
			if ($whois->guild){
				if(isset($chatBot->banlist[$whois->guild])) return true;
				$guild_sql = str_replace("'", "''", $whois->guild);
				$db->query("SELECT * FROM warbot.banlist WHERE name='{$guild_sql}';");
				if ($db->numrows()!=0) return 1;
			}
			
			$db->query("SELECT * FROM warbot.alts a RIGHT JOIN warbot.banlist b ON a.main=b.name WHERE a.alt='{$char_sql}' OR a.main='{$char_sql}' OR b.name='{$char_sql}';");
			if ($db->numrows()!=0) return true;
		} else {
			$db->query("SELECT * FROM warbot.banlist WHERE name='{$char_sql}';");
			if ($db->numrows()!=0) return 1;		
		}
//		return isset($chatBot->banlist[$char]);
		return false;
	}
}

?>