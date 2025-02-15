<?php

class Player {
	public static function sync_attempt($sender=null){
		$db = DB::get_instance();
		global $chatBot;
		if($sender===null){
			$sql = "SELECT * FROM players WHERE last_update<" . (time() - 604800) . " OR charid=0 ORDER BY last_update ASC LIMIT 1;";
			$db->query($sql);
			if($db->numrows()===0) return false;
			$char = $db->fObject();
			$name = $char->name;
		} else {
			$sql = "SELECT * FROM players WHERE name LIKE '{$sender}';";
			$db->query($sql);
			if($db->numrows()===0) return false;
			$char = $db->fObject();
			$name = $char->name;
		}
		
		$charid = $chatBot->get_uid($name);
		if ($charid !== null && !empty($charid)) {
			$player = Player::lookup($name, $chatBot->vars['dimension']);
			if ($player->name === $name) {
				$player->charid = $charid;
				Player::update($player);
			} else {
				$db->exec("UPDATE players SET last_update=" . (time() - 604800) . " WHERE name LIKE '{$name}';");
				return false;
			}
		} else {
			$db->exec("DELETE FROM players WHERE name LIKE '{$name}';");
			$db->exec("INSERT INTO charid_history (`charid`,`name`,`level`,`profession`,`faction`,`time`) VALUES ('{$char->charid}','{$char->name}','{$char->level}','{$char->profession}','{$char->faction}'," . time() . ";");
		}
		return true;
	}

	public static function get_by_name($name, $forceUpdate = false) {
		$db = DB::get_instance();
		global $chatBot;
		
		$name = ucfirst(strtolower($name));
		
		$charid = $chatBot->get_uid($name);
		if ($charid == null) {
			return null;
		}
	
		if(Setting::get('hide_omni_scout')==0){
			$sql = "SELECT p.*,op.charid AS oldcharid FROM players p LEFT JOIN warbot_old.players op ON p.name=op.name WHERE p.name LIKE '$name'";
		} else {
			$sql = "SELECT * FROM players WHERE name LIKE '$name'";
		}
		
		$db->query($sql);
		$found = $db->numrows();
		$player = $db->fObject();

		if ($player === null || $forceUpdate) {
			$player = Player::lookup($name, $chatBot->vars['dimension']);
			if ($player !== null && $player->name == $name) {
				$player->charid = $charid;
				Player::update($player);
			} else {
				$player = Player::lookup_auno($name, $chatBot->vars['dimension']);
				if($found === 0 && $player !== null){
					Player::update($player,0);
				}
			}
		} else if ($player->last_update < (time() - 604800)) {
		/*	$player2 = Player::lookup($name, $chatBot->vars['dimension']);
			if ($player2 !== null) {
				$player = $player2;
				$player->charid = $charid;
				Player::update($player);
			} else {	*/
				$player->source .= ' (old-cache)';
		//	}
		} else {
			$player->source .= ' (current-cache)';
		}
		
		return $player;
	}
	
	public static function lookup($name, $dimension) {
		$xml = Player::lookup_url("http://people.anarchy-online.com/character/bio/d/5/name/$name/bio.xml");
	//	if ($xml->name == $name) {
		if ($xml!==null){
			$xml->source = 'people.anarchy-online.com';
			$xml->dimension = $dimension;

			return $xml;
		}
		
		// if people.anarchy-online.com was too slow to respond or returned invalid data then try to update from auno.org
		/*
		$xml = Player::lookup_url("http://auno.org/ao/char.php?output=xml&dimension=$dimension&name=$name");
		if ($xml->name == $name) {
			$xml->source = 'auno.org';
			$xml->dimension = $dimension;

			return $xml;
		}
		*/
		
		return null;
	}

	public static function lookup_auno($name, $dimension) {

		$xml = Player::lookup_url("http://auno.org/ao/char.php?output=xml&dimension=$dimension&name=$name");
		if ($xml->name == $name) {
			$xml->source = 'auno.org';
			$xml->dimension = $dimension;

			return $xml;
		}

		return null;
	}
	
	private static function lookup_url($url) {
		$playerbio = xml::getUrl($url);
		
		$xml = new stdClass;
	
		// parsing of the player data		
		$xml->firstname      = xml::spliceData($playerbio, '<firstname>', '</firstname>');
		$xml->name           = xml::spliceData($playerbio, '<nick>', '</nick>');
		$xml->lastname       = xml::spliceData($playerbio, '<lastname>', '</lastname>');
		$xml->level          = xml::spliceData($playerbio, '<level>', '</level>');
		$xml->breed          = xml::spliceData($playerbio, '<breed>', '</breed>');
		$xml->gender         = xml::spliceData($playerbio, '<gender>', '</gender>');
		$xml->faction        = xml::spliceData($playerbio, '<faction>', '</faction>');
		$xml->profession     = xml::spliceData($playerbio, '<profession>', '</profession>');
		$xml->prof_title     = xml::spliceData($playerbio, '<profession_title>', '</profession_title>');
		$xml->ai_rank        = xml::spliceData($playerbio, '<defender_rank>', '</defender_rank>');
		$xml->ai_level       = xml::spliceData($playerbio, '<defender_rank_id>', '</defender_rank_id>');
		$xml->guild_id       = xml::spliceData($playerbio, '<organization_id>', '</organization_id>');
		$xml->guild          = xml::spliceData($playerbio, '<organization_name>', '</organization_name>');
		$xml->guild_rank     = xml::spliceData($playerbio, '<rank>', '</rank>');
		$xml->guild_rank_id  = xml::spliceData($playerbio, '<rank_id>', '</rank_id>');
		
		return $xml;
	}
	
	public static function update(&$char,$last_update=1) {
		$db = DB::get_instance();
		if($last_update===1) $last_update = time();
	//	$db->query("SELECT charid, name FROM players WHERE `name` = '{$char->name}'");
	//	$old = $db->fObject();
		
	/*	
		$sql = "UPDATE players SET
				charid='{$char->charid}',
				firstname='" . str_replace("'", "''", $char->firstname) . "',
				name='{$char->name}',
				lastname='" . str_replace("'", "''", $char->lastname) . "',
				level='{$char->level}',
				breed='{$char->breed}',
				gender='{$char->gender}',
				faction='{$char->faction}',
				profession='{$char->profession}',
				prof_title='{$char->prof_title}',
				ai_rank='{$char->ai_rank}',
				ai_level='{$char->ai_level}',
				guild_id='{$char->guild_id}',
				guild='" . str_replace("'", "''", $char->guild) . "',
				guild_rank='{$char->guild_rank}',
				guild_rank_id='{$char->guild_rank_id}',
				dimension='{$char->dimension}',
				source='{$char->source}',
				last_update='{$last_update}'
				WHERE name='{$char->name}';
			";
	*/	
	
		$sql = "DELETE FROM players WHERE `name` = '{$char->name}'";
		$db->exec($sql);

		$sql = "
			INSERT INTO players (
				`charid`,
				`firstname`,
				`name`,
				`lastname`,
				`level`,
				`breed`,
				`gender`,
				`faction`,
				`profession`,
				`prof_title`,
				`ai_rank`,
				`ai_level`,
				`guild_id`,
				`guild`,
				`guild_rank`,
				`guild_rank_id`,
				`dimension`,
				`source`,
				`last_update`
			) VALUES (
				'{$char->charid}',
				'{$char->firstname}',
				'{$char->name}',
				'{$char->lastname}',
				'{$char->level}',
				'{$char->breed}',
				'{$char->gender}',
				'{$char->faction}',
				'{$char->profession}',
				'{$char->prof_title}',
				'{$char->ai_rank}',
				'{$char->ai_level}',
				'{$char->guild_id}',
				'" . str_replace("'", "''", $char->guild) . "',
				'{$char->guild_rank}',
				'{$char->guild_rank_id}',
				'{$char->dimension}',
				'{$char->source}',
				'{$last_update}'
			)";

		
		$db->exec($sql);
	}
	
	public static function get_info(&$whois) {
		$msg = '';
		
		if ($whois->firstname) {
            $msg = $whois->firstname." ";
		}

        $msg .= "<highlight>\"{$whois->name}\"<end> ";

        if ($whois->lastname) {
            $msg .= $whois->lastname." ";
		}
	
		$msg .= "(<highlight>{$whois->level}<end>/<green>{$whois->ai_level}<end>";
		$msg .= ", {$whois->gender} {$whois->breed} <highlight>{$whois->profession}<end>";
		$msg .= ", <" . strtolower($whois->faction) . ">$whois->faction<end>";

        if ($whois->guild) {
            $msg .= ", {$whois->guild_rank} of <highlight>{$whois->guild}<end>)";
        } else {
            $msg .= ", Not in a guild)";
		}
		
		return $msg;
	}
}

?>