<?php

class Towers {
	public static function get_tower_info($playfield_id, $site_number) {
		$db = DB::get_instance();

		$sql = "
			SELECT
				*
			FROM
				tower_site t
			WHERE
				`playfield_id` = {$playfield_id}
				AND `site_number` = {$site_number}
			LIMIT 1";
		
		$db->query($sql);
		return $db->fObject();
	}
	
	public static function find_sites_in_playfield($playfield_id) {
		$db = DB::get_instance();

		$sql = "SELECT * FROM tower_site WHERE `playfield_id` = {$playfield_id}";

		$db->query($sql);
		return $db->fObject('all');
	}
	
	public static function get_closest_site($playfield_id, $x_coords, $y_coords) {
		$db = DB::get_instance();

		$sql = "
			SELECT
				*,
				((x_distance * x_distance) + (y_distance * y_distance)) radius
			FROM
				(SELECT
					playfield_id,
					site_number,
					min_ql,
					max_ql,
					x_coord,
					y_coord,
					site_name,
					(x_coord - {$x_coords}) as x_distance,
					(y_coord - {$y_coords}) as y_distance
				FROM
					tower_site
				WHERE
					playfield_id = {$playfield_id}) t
			ORDER BY
				radius ASC
			LIMIT 1";

		$db->query($sql);
		return $db->fObject();		
	}

	public static function get_last_attack($att_faction, $att_guild_name, $def_faction, $def_guild_name, $playfield_id) {
		$db = DB::get_instance();
		
		$att_guild_name = str_replace("'", "''", $att_guild_name);
		$def_guild_name = str_replace("'", "''", $def_guild_name);
		
		$time = time() - (7 * 3600);
		if($att_guild_name!="")
		$sql = "
			SELECT
				*
			FROM
				tower_attack
			WHERE
				`att_guild_name` = '{$att_guild_name}'
				AND `att_faction` = '{$att_faction}'
				AND `def_guild_name` = '{$def_guild_name}'
				AND `def_faction` =  '{$def_faction}'
				AND `playfield_id` = {$playfield_id}
				AND `time` >= {$time}
			ORDER BY
				`time` DESC
			LIMIT 1";
		else 
		$sql = "
			SELECT
				*
			FROM
				tower_attack
			WHERE
				`def_guild_name` = '{$def_guild_name}'
				AND `def_faction` =  '{$def_faction}'
				AND `playfield_id` = {$playfield_id}
				AND `time` >= {$time}
			ORDER BY
				`time` DESC
			LIMIT 1";		
		$db->query($sql);
		return $db->fObject();
	}
	
	public static function record_attack($whois, $def_faction, $def_guild_name, $x_coords, $y_coords, $closest_site) {
		// delay tower records if there are multiple bots on one DB
	//	$delay = Setting::get('tower_record_delay');
		
		$db = DB::get_instance();
		
		$time_kill = time();
		
		$att_guild_name = str_replace("'", "''", $whois->guild);
		$def_guild_name = str_replace("'", "''", $def_guild_name);
		
		$flag = $db->exec("INSERT INTO tower_query_flag (flag) VALUES (1);");	// set flag
		while(!$flag) {
			$flag = $db->exec("INSERT INTO tower_query_flag (flag) VALUES (1);");
		}
		$sql = "SELECT *
			FROM tower_attack 
			WHERE att_player LIKE '{$whois->name}' AND
				  def_guild_name LIKE '{$def_guild_name}' AND
				  playfield_id={$closest_site->playfield_id} AND
				  site_number={$closest_site->site_number}
			HAVING time>". (time()-1260) .";";
				
		$db->query($sql);
		if ($db->numrows() === 0){
			$sql = "
				INSERT INTO tower_attack (
					`time`,
					`att_guild_name`,
					`att_faction`,
					`att_player`,
					`att_level`,
					`att_ai_level`,
					`att_profession`,
					`def_guild_name`,
					`def_faction`,
					`playfield_id`,
					`site_number`,
					`x_coords`,
					`y_coords`
				) VALUES (
					{$time_kill},
					'{$att_guild_name}',
					'{$whois->faction}',
					'{$whois->name}',
					'{$whois->level}',
					'{$whois->ai_level}',
					'{$whois->profession}',
					'{$def_guild_name}',
					'{$def_faction}',
					{$closest_site->playfield_id},
					{$closest_site->site_number},
					{$x_coords},
					{$y_coords});";
			$db->exec($sql);
		}
		$db->exec("DELETE FROM tower_query_flag WHERE flag=1;");	// remove flag
		return true;
		
	}
	
	public static function find_all_scouted_sites() {
		$db = DB::get_instance();
		
		$sql = 
			"SELECT
				*
			FROM
				scout_info s
				JOIN tower_site t
					ON (s.playfield_id = t.playfield_id AND s.site_number = t.site_number)
				JOIN playfields p
					ON (s.playfield_id = p.id)
			ORDER BY
				guild_name, ct_ql";

		$db->query($sql);
		return $db->fObject('all');
	}
	
	public static function get_last_victory($playfield_id, $site_number) {
		$db = DB::get_instance();
		
		$sql = "
			SELECT
				*,v.time AS win_time
			FROM
				tower_victory v
				JOIN tower_attack a ON (v.attack_id = a.id)
			WHERE
				a.`playfield_id` = {$playfield_id}
				AND a.`site_number` = {$site_number}
			ORDER BY
				v.`time` DESC
			LIMIT 1";
		
		$db->query($sql);
		return $db->fObject();
	}
	
	public static function record_victory($last_attack) {
		// delay tower records if there are multiple bots on one DB
	//	$delay = Setting::get('tower_record_delay');
		
	
		$db = DB::get_instance();
		
		$time_kill = time();
		$win_guild_name = str_replace("'", "''", $last_attack->att_guild_name);
		$lose_guild_name = str_replace("'", "''", $last_attack->def_guild_name);
		
		if(empty($last_attack->id)){
			$flag = $db->exec("INSERT INTO tower_query_flag (flag) VALUES (0);");	// set flag
			while(!$flag) {
				$flag = $db->exec("INSERT INTO tower_query_flag (flag) VALUES (0);");
			}
			$sql = "
				SELECT
					*
				FROM tower_victory
				WHERE
					lose_guild_name='{$lose_guild_name}'
					" . (empty($win_guild_name)?"":"AND win_guild_name='{$win_guild_name}'") . "
					AND lose_faction = '{$last_attack->def_faction}'
					AND attack_id IS NULL
				HAVING
					time>" . ($time_kill-600) . ";";
				
			$db->query($sql);
			if ($db->numrows() !== 0){
				$db->exec("DELETE FROM tower_query_flag WHERE flag=0;");	// remove flag
				return true;
			}
		}
		
		$sql = "
			INSERT IGNORE INTO tower_victory (
				`time`,
				`win_guild_name`,
				`win_faction`,
				`lose_guild_name`,
				`lose_faction`
				" . (empty($last_attack->id) ? "" : ",`attack_id`") . "
			) VALUES (
				{$time_kill},
				'{$win_guild_name}',
				'{$last_attack->att_faction}',
				'{$lose_guild_name}',
				'{$last_attack->def_faction}'
				" . (empty($last_attack->id) ? "" : "," . $last_attack->id) . "
			);";
		$db->exec($sql);
		if(empty($last_attack->id)) $db->exec("DELETE FROM tower_query_flag WHERE flag=0;");	// remove flag
		return true;
	}
	
	public static function add_scout_site($playfield_id, $site_number, $close_time, $ct_ql, $faction, $guild_name, $scouted_by, $force=false) {
		$db = DB::get_instance();
		$faction = ucfirst(strtolower($faction));
		
		$guild_name = str_replace("'", "''", $guild_name);
		
		$sql = "
			INSERT INTO scout_info_history (
				`playfield_id`,
				`site_number`,
				`scouted_on`,
				`scouted_by`,
				`ct_ql`,
				`guild_name`,
				`faction`,
				`close_time`
				" . ($force?",`force`":"") . "
			) VALUES (
				{$playfield_id},
				{$site_number},
				NOW(),
				'{$scouted_by}',
				{$ct_ql},
				'{$guild_name}',
				'{$faction}',
				{$close_time}
				" . ($force?",1":"") . "
			)";

		$db->exec($sql);
			
		$sql = "
			UPDATE scout_info SET 
				`scouted_on` = NOW(),
				`scouted_by` = '{$scouted_by}',
				`ct_ql` = {$ct_ql},
				`guild_name` = '{$guild_name}',
				`faction` = '{$faction}',
				`close_time` = {$close_time},
				`is_current` = 1
			WHERE
				`playfield_id` = {$playfield_id}
				AND `site_number` = {$site_number}";

		return $db->exec($sql);
	}
	
	public static function rem_scout_site($playfield_id, $site_number) {
		$db = DB::get_instance();
		
		$sql = "UPDATE scout_info SET is_current=0 WHERE `playfield_id` = {$playfield_id} AND `site_number` = {$site_number}";

		return $db->exec($sql);
	}
	
	public static function check_guild_name($guild_name) {
		$db = DB::get_instance();
		
		$guild_name = str_replace("'", "''", $guild_name);
	
		$sql = "SELECT * FROM tower_attack WHERE `att_guild_name` LIKE '{$guild_name}' OR `def_guild_name` LIKE '{$guild_name}' LIMIT 1";
		
		$db->query($sql);
		if ($db->numrows() === 0) {
			return false;
		} else {
			return true;
		}
	}
}

?>