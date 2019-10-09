<?php

class Tara {
	public static function in_raidlist($char){
		$account = Tara::get_account_name($char);
		$db = DB::get_instance();
		$db->query("SELECT * FROM tara_raidlist WHERE account='{$account}';");
		if ($db->numrows()===0) return false;
		else {
			$row=$db->fObject();
			return $row->name;
		}
	}

	public static function raid_add($char){
		if (!Tara::is_registered($char)) return false;
		$account = Tara::get_account_name($char);
		$db = DB::get_instance();
		$whois = Player::get_by_name($char);
		if ($whois){
			$level = $whois->level;
		} else $level = 1;
		$db->query("SELECT * FROM tara_raid_categories WHERE min_level<={$whois->level} AND max_level>={$whois->level};");
		if ($db->numrows()===0) return false;
		$row=$db->fObject();
		$db->exec("INSERT INTO tara_raidlist (`account`,`name`,`category`) VALUES ('{$account}','{$char}',{$row->cat_id});");
		return $row->cat_id;
	}

	public static function get_account_name($char){
		$account = null;
		$db = DB::get_instance();
		$db->query("SELECT `name` FROM tara_points WHERE `name` LIKE '$char';");
		if ($db->numrows() > 0) {
			$row = $db->fObject();
			$account = $row->name;
		} else {
			$db->query("SELECT main FROM alts WHERE alt LIKE '{$char}' AND approved=1;");
			if ($db->numrows()>0){
				$row=$db->fObject();
				$main = $row->main;
				$db->query("SELECT `name` FROM tara_points WHERE `name` LIKE '$main';");
				if ($db->numrows() > 0) {
					$row = $db->fObject();
					$account = $row->name;
				}
			}
		}		
		return $account;
	}
	
	public static function get_points($char){
		$account = Tara::get_account_name($char);
		$db = DB::get_instance();
		$db->query("SELECT name, IF(forums=1,points+20,points) as points FROM tara_points WHERE name='{$account}';");
		if ($db->numrows()===0){
			return false;
		}
		$row=$db->fObject();
		return $row->points;
	}

	public static function get_stats($account=null) {
		if($account==null) return "";
		$db = DB::get_instance();
		
		$db->query("SELECT SUM(`change`) AS total_points, COUNT(`change`) AS raid_count FROM tara_points_history WHERE account='{$account}' AND item='';");
		$total=$db->fObject();
		
		$db->query("SELECT SUM(`change`) AS total_points, COUNT(`change`) AS raid_count FROM tara_points_history WHERE account='{$account}' AND item!='';");
		$total_spent=$db->fObject();
		
		$db->query("SELECT t.*,r.time,r.type FROM tara_points_history t LEFT JOIN tara_raid_history r ON t.raid_id=r.id WHERE t.account='{$account}' ORDER BY t.id ASC LIMIT 1;");
		$first_raid=$db->fObject();
		
		$db->query("SELECT count(t.id) AS cnt FROM tara_points_history t LEFT JOIN tara_raid_history r ON t.raid_id=r.id WHERE t.account='{$account}' AND r.time>" . gmmktime(0,0,0,date('m'),1,date('Y')) . " AND t.item='' ORDER BY t.id ASC;");
		$this_month=$db->fObject();
		
		$db->query("SELECT count(id) AS cnt FROM tara_raid_history WHERE time>" . gmmktime(0,0,0,date('m'),1,date('Y')) . ";");
		$this_month_total=$db->fObject();
		
		$db->query("SELECT count(id) AS raid_count FROM tara_raid_history h LEFT JOIN alts a ON h.leader=a.alt WHERE a.main='{$account}' OR h.leader='{$account}';");
		$lead_count=$db->fObject();
		
		$points_spent = 0 - $total_spent->total_points;
		
		$blob = "First raid: <white>";
		if ($first_raid->time!=0) $blob .= gmdate('j/M/y G:i',($first_raid->time));
		else $blob .= "Never";
		$blob .= "<end>\n";
		$blob .= "Raids attended: <white>{$total->raid_count}<end> (" . Text::make_link("raidhistory","/tell <myname> !raidhistory {$account}",'chatcmd') . ")\n";
		if ($this_month_total->cnt>0) $blob .= "This month: <white>{$this_month->cnt}<end> <highlight>(" . round(100*$this_month->cnt/$this_month_total->cnt) . "% of total raids this month)<end>\n";
		if ($total->raid_count>0) $blob .= "Raids RLed: <white>{$lead_count->raid_count}<end> <highlight>(" . round(100*$lead_count->raid_count/$total->raid_count) . "% of total raids attended)<end>\n";
		$blob .= "Total points gained: <white>{$total->total_points}<end>\n";
		$blob .= "Total points spent: <white>{$points_spent}<end>\n";
		$blob .= "Items won: <white>{$total_spent->raid_count}<end> (" . Text::make_link("lootplayer","/tell <myname> !lootplayer {$account}",'chatcmd') . ")";

		return $blob;
	}
	
	public static function get_points_blob($char,$title=false,$mod=false){
		$account = Tara::get_account_name($char);
		
		$db = DB::get_instance();
		
		$db->query("SELECT * FROM tara_points WHERE name='{$account}';");
		if ($db->numrows()===0){
			return false;
		}
		$row=$db->fObject();
		$points = $row->points;
		$bonus="";
		if ($row->forums==1) { 
			$points+=20;
			$bonus = " (20 points " . Text::make_link("www.omnihq.net","/tell <myname> !forums",'chatcmd') . " bonus)";
		} else {
			$bonus = " (" . Text::make_link("get extra 20", "/tell <myname> !forums", 'chatcmd') . ")";
		}
		if ($title==false) {
			$limit = " LIMIT 30";
			$stats = "";
		}
		else { 
			$limit = "";
			$stats = Tara::get_stats($account);
		}
		
		$db->query("SELECT t.*,r.time,r.type,l.long_name FROM tara_points_history t LEFT JOIN tara_raid_history r ON t.raid_id=r.id LEFT JOIN tara_loot l ON t.item=l.short_name WHERE t.account='{$account}' ORDER BY t.id DESC{$limit};");
		if ($db->numrows()===0){
			return false;
		}
		$blob = "<header>:::: {$account}'s points history ::::<end>\n";
		$blob .= "Points: <yellow>{$points}<end>{$bonus}\n";
		$blob .= $stats;
		$blob .= "\n<white>:char: :date: :raid: :points: :(item):<end>\n";
		while($row=$db->fObject()){
			$blob .= "<yellow>{$row->name}<end> " . Text::make_link(gmdate('j/M/y G:i',($row->time)),"/tell <myname> !raidhistory {$row->raid_id}",'chatcmd') . " {$row->type} <orange>{$row->change}<end>";
			if ($row->item != "") {
				$blob .= " <highlight>(won " . Text::make_link($row->item,"/tell <myname> !items {$row->long_name}",'chatcmd') . ")<end>";
				if ($mod) $blob .= " id:{$row->id}";
				if ($row->refunded!="") $blob .= " <orange>[refunded by {$row->refunded}]<end>";
			}
			$blob .= "\n";
		}
		if ($title===false) {
			$blob .= "\n\n:: " . Text::make_link("Full history","/tell <myname> !raidhistory {$account}",'chatcmd');
			$msg = Text::make_link("history",$blob,'blob');
			return "({$msg})";
		} else {
			$msg = Text::make_link($title,$blob,'blob');
			return $msg;
		}
	}
	
	public static function award_points($topic, $leader){
		// find raid
		$db = DB::get_instance();
		$db->query("SELECT * FROM tara_raids WHERE short_name='{$topic}';");
		if($db->numrows()===0){
			return "No points awarded for this raid.";
		}
		$raid=$db->fObject();
		// record raid
		$db->exec("INSERT INTO tara_raid_history (type,leader,time) VALUES ('{$topic}','{$leader}'," . time() . ")");
		
		// get raidlist
		$db->query("SELECT * FROM tara_raidlist ORDER BY name;");
		if($db->numrows()===0){
			return "Raidlist empty, no points awarded.";
		}
		$data = $db->fObject('all');
		// award points and record history
		$blob="<header>:::: Awarded points ::::<end>\n\n";
		$blob.="<highlight>name (account) points awarded::<end>\n";
		$raid_id=Tara::last_raid_id();
		
		foreach($data as $row){
			$account = Tara::get_account_name($row->name);
			$category = 'points_cat_' . $row->category;
			$points = $raid->$category;
			$db->exec("UPDATE tara_points SET points=points+{$points} WHERE name='{$account}';");
			$db->exec("INSERT INTO tara_points_history (`account`,`name`,`change`,`raid_id`) VALUES ('{$account}','{$row->name}',{$points},'{$raid_id}');");
			$blob .= "<white>{$row->name}<end> ({$account}) <white>{$points}<end>\n";
		}
		$msg = Text::make_link("Awarded points",$blob,'blob');
		return $msg;
	}
	
	public static function last_raid_id(){
		$db = DB::get_instance();
		$db->query("SELECT id FROM tara_raid_history ORDER BY id DESC LIMIT 1;");
		if($db->numrows()===0) return 0;
		else {
			$row=$db->fObject();
			return $row->id;
		}
	}
	
	public static function clean_raidlist(){
		global $chatBot;
		$db = DB::get_instance();
		$db->exec("DELETE FROM tara_raidlist;");
		$chatBot->data["TARA_MODULE"]["raidlist"]=array();
		return;
	}
	
	public static function get_item_blob($item){
		$db = DB::get_instance();
		$db->query("SELECT * FROM aodb WHERE `name` LIKE '{$item}' LIMIT 1;");
		if($db->numrows!==0){
			$row=$db->fObject();
			$blob .= Text::make_image($row->icon) . "\n";
			$blob .= Text::make_item($row->lowid, $row->highid, $row->highql, $item);
		} else {
			$blob = $item;
		}
		return $blob;
	}
	
	public static function is_registered($char){
		$db = DB::get_instance();
		$db->query("SELECT * FROM tara_points WHERE `name` = '$char';");
		if ($db->numrows() == 0) {
			$db->query("SELECT * FROM alts WHERE `alt` = '$char' AND `approved` = 1;");
			if ($db->numrows() !== 0) {
				$is_member = true;
			} else $is_member = null;
		} else {
			$db->query("SELECT * FROM alts WHERE `alt` = '$char' AND `approved` = 0;");
			if ($db->numrows() !== 0) {
				$is_member = false;
			} else $is_member = true;
		}
		return $is_member;
	}

	public static function forums($char){
		$db = DB::get_instance();
		$db->query("SELECT * FROM tara_points WHERE `name` = '$char' AND forums=1;");
		if ($db->numrows()>0) return true;
		else return false;
	}
	
	public static function spawntime() {
		$db = DB::get_instance();
		$rezz = intval(Setting::get("tara_spawntime_hours"));
		
		$manual = intval(Setting::get("tara_spawntime"));
		$manual_by = Setting::get("tara_spawntime_by");
		$manual_set = intval(Setting::get("tara_spawntime_set"));
		
		$sql = "SELECT time, type, leader FROM tara_raid_history WHERE type LIKE 'tara' OR type LIKE 'pvpwin' OR type LIKE 'pvploss' ORDER BY time DESC LIMIT 1;";
		$db->query($sql);
		if($db->numrows()==0){
			$i=0;
			while ($manual<time()-1200){
				$manual+=$rezz*3600+300;
				$i++;
			}
			$result->skip = $i;		
			$result->set=0;
			$result->time=0;
			$result->manual=$manual;
			$result->state=1;
			$result->set_by=$manual_by;
		} else {
			$row = $db->fObject();
			$spawn = ($row->time + $rezz * 3600);
			$i=0;
			$result->set = $row->time;
			while ($spawn<(time()-1200)){
				$spawn+=$rezz*3600+300;
				$i++;
			}
			$result->time=$spawn;
			$result->skip=$i;
			$result->set_by=$row->leader;

			if($result->set > $manual_set){
				$result->state = 0;
			} else if ($manual_set > $result->set){
				$i=0;
				while ($manual<time()-1200){
					$manual+=$rezz*3600+300;
					$i++;
				}
				$result->skip = $i;
				$result->manual = $manual;
				$result->set_by = $manual_by;
				$result->state = 1;
			} else {
			//	$spawntime = $result->time;
				$result->state = 0;
			}			
		}

		
		return $result;
	}
}

?>