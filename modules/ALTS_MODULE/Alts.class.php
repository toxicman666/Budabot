<?php

class Alts {
	public static function get_main($player) {
		$db = DB::get_instance();
		
		$sql = "SELECT `alt`, `main` FROM `alts` WHERE `alt` LIKE '$player'";
		$db->query($sql);
		$row = $db->fObject();

		if ($row === null) {
			return $player;
		} else {
			return $row->main;
		}
	}
	
	public static function get_alts($main) {
		$db = DB::get_instance();
		
		$sql = "SELECT `alt`, `main` FROM `alts` WHERE `main` LIKE '$main'";
		$db->query($sql);
		
		$data = $db->fObject('all');
		$array = array();
		forEach ($data as $row) {
			$array[] = $row->alt;
		}
		return $array;
	}
	
	public static function get_tara_alts($main) {
		$db = DB::get_instance();
		
		$sql = "SELECT `alt`, `main`, `approved` FROM `alts` WHERE `main` LIKE '$main' AND `approved`=1;";
		$db->query($sql);
		
		$data = $db->fObject('all');
		$array = array();
		forEach ($data as $row) {
			$array[] = $row->alt;
		}
		return $array;
	}
	
	public static function add_alt($main, $alt) {
		$db = DB::get_instance();
		
		$main = ucfirst(strtolower($main));
		$alt = ucfirst(strtolower($alt));
		
		$sql = "INSERT INTO `alts` (`alt`, `main`) VALUES ('$alt', '$main')";
		return $db->exec($sql);
	}
	
	public static function rem_alt($main, $alt) {
		$db = DB::get_instance();
		
		$sql = "DELETE FROM `alts` WHERE `alt` LIKE '$alt' AND `main` LIKE '$main'";
		return $db->exec($sql);
	}
	
	public static function get_alts_blob($char,$show_main=false) {
		$db = DB::get_instance();
	
		$main = Alts::get_main($char);
		$alts = Alts::get_alts($main);

		if (count($alts) == 0) {
			return null;
		}

		$list = "<header> :::: Character List for $main :::: <end>\n\n";
		$list .= "{$main}";
		$character = Player::get_by_name($main);
		if ($character !== null) {
			$list .= " (<highlight>{$character->level}<end>/<green>{$character->ai_level}<end> <highlight>{$character->profession}<end>)";
		}
		$online = Buddylist::is_online($main);
		if ($online === null) {
			$list .= " \n";
		} else if ($online == 1) {
			$list .= " - <green>Online<end>\n";
		} else {
			$list .= " - <red>Offline<end>\n";
		}
		$list .= "\n<header>:::: Alt Character(s) ::::<end>\n";
		
		$playersdb = Player::get_players_db();
		$sql = "SELECT `alt`, `main`, `approved`, p.* FROM `alts` a LEFT JOIN {$playersdb} p ON a.alt = p.name WHERE `main` LIKE '$main' ORDER BY level DESC, ai_level DESC, profession ASC, name ASC";
		$db->query($sql);
		$data = $db->fObject('all');
		$unregistered=array();
		forEach ($data as $row) {
			if($row->approved==1){
				$list .= "{$row->alt}";
				if ($row->profession !== null) {
					$list .= " (<highlight>{$row->level}<end>/<green>{$row->ai_level}<end> <highlight>{$row->profession}<end>)";
				}
				$online = Buddylist::is_online($row->alt);
				if ($online === null) {
					$list .= "\n";
				} else if ($online == 1) {
					$list .= " - <green>Online<end>\n";
				} else {
					$list .= " - <red>Offline<end>\n";
				}
			} else {
				$unregistered[]=$row->alt;
			}
		}
		if(count($unregistered)>0){
			$list .= "\n<header>:::: Unregistered Alts ::::<end>\n";
			foreach ($unregistered as $alt){
				$list .= "$alt ";
			}
		}
		if ($char!=$main || $show_main){
			$msg = Text::make_link("Alts of $main", $list, 'blob');
		} else {
			$msg = Text::make_link("Alts", $list, 'blob');
		}
		
		return $msg;
	}
}

?>