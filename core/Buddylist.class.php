<?php

class Buddylist {

	/**
	 * @name: is_online
	 * @description: Returns null when online status is unknown, 1 when buddy is online, 0 when buddy is offline
	 */
	public static function is_online($name) {
		$buddy = Buddylist::get_buddy($name);
		return ($buddy === null ? null : $buddy['online']);
    }
	
	public static function get_buddy($name) {
		global $chatBot;

		$uid = $chatBot->get_uid($name);
		if ($uid === false || !isset($chatBot->buddyList[$uid])) {
			return null;
		} else {
			return $chatBot->buddyList[$uid];
		}
    }
	
	public static function add($name, $type) {
		global $chatBot;

		$uid = $chatBot->get_uid($name);
		if ($uid === false || $type === null || $type == '' || empty($name)) {
			return false;
		} else {
			if (!isset($chatBot->buddyList[$uid])) {
				Logger::log('debug', "Buddy", "$name buddy added");
				$chatBot->buddy_add($uid);
			}
			
			if (!isset($chatBot->buddyList[$uid]['types'][$type])) {
				$chatBot->buddyList[$uid]['types'][$type] = 1;
				Logger::log('debug', "Buddy", "$name buddy added (type: $type)");
			}
			
			// add to forums
			if ($type=='member' && Setting::get('wc_forum_members')==1){
				$group_id_wc = 3; // warleaders
				$role_id = 2; // member
				$db = DB::get_instance();
				
				// check if alts added
				$main = Alts::get_main($name);
				$alts = Alts::get_alts($main);
				if(count($alts)>0){
					foreach($alts as $alt){
						if ($alt==$name) continue;
						$sql = "SELECT ag.id, ag.email FROM omnihqdb.jos_users jo JOIN omnihqdb.jos_agora_users ag ON jo.username = ag.username LEFT JOIN omnihqdb.jos_agora_user_group gr ON gr.user_id=ag.id WHERE jo.name='{$alt}' AND gr.group_id = {$group_id_wc};";
						$db->query($sql);
						if ($db->numrows() != 0) {
							return true;
						}
					}
				}
				if($main!=$name){
					$sql = "SELECT ag.id, ag.email FROM omnihqdb.jos_users jo JOIN omnihqdb.jos_agora_users ag ON jo.username = ag.username LEFT JOIN omnihqdb.jos_agora_user_group gr ON gr.user_id=ag.id WHERE jo.name='{$main}' AND gr.group_id = {$group_id_wc};";
					$db->query($sql);
					if ($db->numrows() != 0) {
						return true;
					}
				}				
				
				$sql = "SELECT ag.id AS id FROM omnihqdb.jos_users jo JOIN omnihqdb.jos_agora_users ag ON jo.username = ag.username WHERE jo.name='$name';";
				$db->query($sql);
				if ($db->numrows() != 0) {
					$row = $db->fObject();
					$ag_id = $row->id;
					$sql = "SELECT id,role_id FROM omnihqdb.jos_agora_user_group WHERE user_id = $ag_id AND group_id = $group_id_wc;";
					$db->query($sql);
					if ($db->numrows() == 0) {
						$sql = "INSERT INTO omnihqdb.jos_agora_user_group (user_id, group_id, role_id) VALUES ($ag_id, $group_id_wc, $role_id);";
						$db->exec($sql);
					}
				}				
			}
			
			return true;
		}
	}
	
	public static function remove($name, $type = '') {
		global $chatBot;

		$uid = $chatBot->get_uid($name);
		if ($uid === false) {
			return false;
		} else if (isset($chatBot->buddyList[$uid])) {
			if (isset($chatBot->buddyList[$uid]['types'][$type])) {
				unset($chatBot->buddyList[$uid]['types'][$type]);
				Logger::log('debug', "Buddy", "$name buddy type removed (type: $type)");
			}

			if (count($chatBot->buddyList[$uid]['types']) == 0) {
				unset($chatBot->buddyList[$uid]);
				Logger::log('debug', "Buddy", "$name buddy removed");
				$chatBot->buddy_remove($uid);
			}
			
			// remove from forums
			if (Setting::get('wc_forum_members')==1 && $type=='member'){
				$group_id_wc = 3; // warleaders
				$db = DB::get_instance();
				$sql = "SELECT ag.id AS id FROM omnihqdb.jos_users jo JOIN omnihqdb.jos_agora_users ag ON jo.username = ag.username WHERE jo.name='$name';";
				$db->query($sql);
				if ($db->numrows() != 0) {
					$row = $db->fObject();
					$ag_id = $row->id;
					$sql = "DELETE FROM omnihqdb.jos_agora_user_group WHERE user_id = $ag_id AND group_id = $group_id_wc;";
					$db->exec($sql);
				}				
			}
			
			return true;
		} else {
			return false;
		}
	}
}

?>