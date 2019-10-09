<?php

if (preg_match("/^planttimer (del|rem) ([0-9]+)$/i", $message, $arr)){
	$number=$arr[2];
	$time=intval(gmdate("U"))-1200;
	$db = DB::get_instance();
	$sql="SELECT * FROM tower_victory WHERE `win_guild_name`='' OR `attack_id`='' HAVING `time`>'$time' ORDER BY `time` DESC;";
	$db->query($sql);
	$arr_timers=array();
	if ($db->numrows() !== 0) {
		while (($row = $db->fObject()) !== null) {
			$arr_timers[]=$row;
		}	
	}
	if(count($arr_timers)<$number){
		$msg = "Invalid planttimer number.";
		$chatBot->send($msg,$sendto);
	} else {
		$row = $arr_timers[$number-1];
		$timer_name = "Plant at former {$row->lose_guild_name} (Unknown)";

		$found=false;
		forEach ($chatBot->data["timers"] as $key => $timer) {
			$name = $timer->name;
			$owner = $timer->owner;

			if ($name==$timer_name) {
				if ($owner == $sender) {
					Timer::remove_timer($key, $name, $sender);
					$msg = "Removed timer <highlight>{$name}<end>.";
					$chatBot->send($msg,'priv');
				} else if (isset($chatBot->admins[$sender])) {
					Timer::remove_timer($key, $name, $owner);
					$msg = "Removed timer <highlight>{$name}<end>.";
					$chatBot->send($msg,'priv');
				} else {
					$msg = "You don't have the right to remove this timer.";
				}
				if ($sendto!='prv') $chatBot->send($msg,$sendto);
				$found=true;
			}
		}
		if(!$found) $chatBot->send("Timer not found.",$sendto);
	}
} else if (preg_match("/^planttimer ([a-z0-9]+) ([0-9]+)$/i", $message, $arr)) {
	
	$time=intval(gmdate("U"))-1194; // 1200-6=1194 (hotfix)

	$arr[1]=strtoupper($arr[1]);
	$sql = "SELECT
			*,v.time AS win_time
		FROM
			tower_victory v
			JOIN tower_attack a ON (v.attack_id = a.id)
			LEFT JOIN playfields p ON (a.playfield_id = p.id)
		WHERE
			short_name='$arr[1]'
		AND
			site_number='$arr[2]'
		HAVING
			v.`time` > '$time'
		ORDER BY
			v.`time` DESC 
			LIMIT 1";
			
	$db = DB::get_instance();	
	$db->query($sql);
	$msg="";
	if ($db->numrows() === 0) {
		$chatBot->send("There was no victories on $arr[1] $arr[2] within 20 minutes.",$sendto);
	}
	else {
		$row = $db->fObject();
		if ($row!==null){
			$togo=1194-(gmdate('U')-($row->win_time));  // 1200-6=1194  (hotfix)
			$timer_name = "Plant at former {$row->lose_guild_name} ({$arr[1]} {$arr[2]})";
			$type='prv';
			$unique=true;
			forEach ($chatBot->data["timers"] as $key => $timer) {
				if ($timer->name == $timer_name) {
					$unique = false;
					break;
				}
			}			
			if ($unique){
				Timer::add_timer($timer_name, $sender, $type, $togo+gmdate('U'));			
				$msg="<font color=\"#FF0000\">{$timer_name}</font> set (" . floor($togo/60) . " minutes " . $togo%60 . " seconds to go)";
			}
			else
				$chatBot->send("$timer_name is already running (use !timers)",$sendto);
		} else $msg="Error";
	}
	if ($msg!="") $chatBot->send($msg,'prv');
	
} else if (preg_match("/^planttimer (del|rem) ([a-z0-9]+) ([0-9]+)$/i", $message, $arr)){
	$found=false;
	forEach ($chatBot->data["timers"] as $key => $timer) {
		$name = $timer->name;
		$owner = $timer->owner;

		if (stripos($name,"Plant at former")!==false && stripos($name,"($arr[2] $arr[3])")!==false) {
			if ($owner == $sender) {
				Timer::remove_timer($key, $name, $sender);
				$msg = "Removed timer <highlight>{$name}<end>.";
				$chatBot->send($msg,'priv');
			} else if (isset($chatBot->admins[$sender])) {
				Timer::remove_timer($key, $name, $owner);
				$msg = "Removed timer <highlight>{$name}<end>.";
			  	$chatBot->send($msg,'priv');
			} else {
				$msg = "You don't have the right to remove this timer.";
			}
			if ($sendto!='prv') $chatBot->send($msg,$sendto);
			$found=true;
		}
	}
	if(!$found) $chatBot->send("Timer not found.",$sendto);
} else if (preg_match("/^planttimer ([0-9]+)$/i", $message, $arr)){	
	$number = $arr[1];
	// planttimers for unknown sites
	$time=intval(gmdate("U"))-1194; // 1200-6=1194  (hotfix)
	$db = DB::get_instance();
	$sql="SELECT * FROM tower_victory WHERE `win_guild_name`='' OR `attack_id`='' HAVING `time`>'$time' ORDER BY `time` DESC;";
	$db->query($sql);
	$arr_timers=array();
	if ($db->numrows() !== 0) {
		while (($row = $db->fObject()) !== null) {
			$arr_timers[]=$row;
		}	
	}
	if(count($arr_timers)<$number){
		$msg = "Invalid planttimer number.";
		$chatBot->send($msg,$sendto);
	} else {
		$row = $arr_timers[$number-1];
		if ($row!==null){
			$togo=1194-(gmdate('U')-($row->time)); // 1200-6=1194  (hotfix)
			$timer_name = "Plant at former {$row->lose_guild_name} (Unknown)";
			$type='prv';
			$unique=true;
			forEach ($chatBot->data["timers"] as $key => $timer) {
				if ($timer->name == $timer_name) {
					$unique = false;
					break;
				}
			}			
			if ($unique){
				Timer::add_timer($timer_name, $sender, $type, $togo+gmdate('U'));			
				$msg="<font color=\"#FF0000\">{$timer_name}</font> set (" . floor($togo/60) . " minutes " . $togo%60 . " seconds to go)";
			}
			else
				$chatBot->send("$timer_name is already running (use !timers)",$sendto);
		}
	}
	if ($msg!="") $chatBot->send($msg,'prv');
	
} else if (preg_match("/^planttimer$/i", $message, $arr)){
	
	$time=intval(gmdate("U"))-1200;
	$sql = "SELECT
			*
		FROM
			tower_victory v
			JOIN tower_attack a ON (v.attack_id = a.id)
			LEFT JOIN playfields p ON (a.playfield_id = p.id)
		HAVING
			v.`time` > '$time'
		ORDER BY
			v.`time` DESC;";
			
	$db = DB::get_instance();	
	$db->query($sql);
	$blob="";
	if ($db->numrows() !== 0) {
		while (($row = $db->fObject()) !== null) {
			$blob.= Text::make_link("Plant at former $row->lose_guild_name ({$row->short_name} {$row->site_number})", "/tell <myname> planttimer {$row->short_name} {$row->site_number}", 'chatcmd') . " ::";
			$blob.= Text::make_link("del", "/tell <myname> planttimer del {$row->short_name} {$row->site_number}", 'chatcmd') . "::<br><br>";			
		}	
	}
	$sql="SELECT * FROM tower_victory WHERE `win_guild_name`='' OR `attack_id`='' HAVING `time`>'$time' ORDER BY `time` DESC;";
	$db->query($sql);
	if ($db->numrows() !== 0) {
		$i=1;
		while (($row = $db->fObject()) !== null) {
			$blob.= Text::make_link("Plant at former {$row->lose_guild_name} (Unknown)", "/tell <myname> planttimer $i", 'chatcmd') . " ::";
			$blob.= Text::make_link("del", "/tell <myname> planttimer del $i", 'chatcmd') . "::<br><br>";
			$i++;
		}	
	}
	if ($blob == "") {
		$msg="There was no victories within 20 minutes.";
	} else $msg=Text::make_link('Plant timers', $blob, 'blob');
	$chatBot->send($msg,$sendto);
} else {
	$syntax_error = true;
}

?>