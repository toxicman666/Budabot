<?php
// spawntime
if (!isset($chatBot->data["TARA_MODULE"]["spawntime"])){
	$spawntime = Tara::spawntime();
	if($spawntime->state == 0)
		$chatBot->data["TARA_MODULE"]["spawntime"] = $spawntime->time;
	else 
		$chatBot->data["TARA_MODULE"]["spawntime"] = $spawntime->manual;
}
$topop = $chatBot->data["TARA_MODULE"]["spawntime"] - time();

if (!isset($chatBot->data["TARA_MODULE"]["raid_topic"])){
	$chatBot->data["TARA_MODULE"]["raid_topic"] = Setting::get("raid_topic");
}

if ($topop < -1201) {
	$spawntime = Tara::spawntime();
	if($spawntime->state == 0)
		$chatBot->data["TARA_MODULE"]["spawntime"] = $spawntime->time;
	else 
		$chatBot->data["TARA_MODULE"]["spawntime"] = $spawntime->manual;
	
	$topop = $chatBot->data["TARA_MODULE"]["spawntime"] - time();
}

if($topop >= 5399 && $topop < 5401){
	if(!empty($chatBot->data["TARA_MODULE"]["raid_topic"])) // end the raid if it's old
		if((time()-Setting::get('raid_topic_time')) > (Setting::get("tara_spawntime_hours")*1800-$topop)){
			Setting::save("raid_topic","");
			$chatBot->data["TARA_MODULE"]["raid_topic"] = "";
			Setting::save("raid_topic_long","");
			Setting::save("raid_status",0);
			$chatBot->data["TARA_MODULE"]["raid_status"] = 0;
			Setting::save("raid_by",$sender);
			Setting::save('assist', '');
			Setting::save('teamassist', '');	
			Setting::save('healassist', '');	
			// clean raidlist
			Tara::clean_raidlist();
			$chatBot->send("<yellow>Old topic cleared<end>",'priv');
			$chatBot->send("<yellow>Raid ended.<end> (expired)",'priv');		
		}
		
	$msg = "Tarasque is expected to spawn in 1 hour 30 minutes.";
	$chatBot->send("<yellow>$msg<end>",'priv');
	// notify linknet
	$chatBot->send("spam <myname> $msg", $this->settings['otspambot']);
} else if($topop >= 3599 && $topop < 3601){
	$msg = "Tarasque is expected to spawn in 1 hour.";
	$chatBot->send("<yellow>$msg<end>",'priv');
	// notify linknet
	$chatBot->send("spam <myname> $msg", $this->settings['otspambot']);
} else if ($topop >= 1799 && $topop < 1801) {
	$msg = "Tarasque is expected to spawn in 30 minutes.";
	if($chatBot->data["TARA_MODULE"]["raid_topic"] == "tara"){
		$msg .= " Raid leader may close the raid in 15 minutes if there is no pvp.";
		$chatBot->data["TARA_MODULE"]["linknet"]=time();
	}
	// notify linknet
	$chatBot->send("spam <myname> $msg", $this->settings['otspambot']);
	$chatBot->send("<yellow>$msg<end>",'priv');
} else if ($topop >= 899 && $topop < 901) {
	$msg = "Tarasque is expected to spawn in 15 minutes.";
	// notify linknet
	$chatBot->send("spam <myname> $msg", $this->settings['otspambot']);
	$chatBot->send("<yellow>$msg<end>",'priv');
} else if ($topop >= 299 && $topop < 301) {
	$chatBot->send("<yellow>Tarasque is expected to spawn in 5 minutes.<end>",'priv');
} else if ($topop >= 119 && $topop < 121) {
	$chatBot->send("<yellow>Tarasque is expected to spawn in 2 minutes.<end>",'priv');
} else if ($topop >= -1 && $topop < 1) {
	$chatBot->send("<yellow>Tarasque is expected to spawn around NOW<end>",'priv');
}

if ($topop < 1801 && $topop >= -1200) {
	if($chatBot->data["TARA_MODULE"]["raid_topic"] == "tara"){
		if (!isset($chatBot->data["TARA_MODULE"]["linknet"])){
			if ($topop >= 901){
				$msg = "Raid leader may close the raid in 15 minutes if there is no pvp.";
				$chatBot->data["TARA_MODULE"]["linknet"]=time();
				// notify linknet
				$chatBot->send("spam <myname> " . Setting::get('raid_topic_long') . " $msg", $this->settings['otspambot']);
				$chatBot->send("<yellow>$msg<end>",'priv');
			}
		} else if ($chatBot->data["TARA_MODULE"]["linknet"] != 0 && $topop < 901 && time() - $chatBot->data["TARA_MODULE"]["linknet"] >= 899) {
			$msg = "Raid leader may close the raid NOW if there is no pvp.";
			$chatBot->data["TARA_MODULE"]["linknet"] = 0;
			$chatBot->send("<yellow>$msg<end>",'priv');
		} else if ($topop < 1201 && time() - $chatBot->data["TARA_MODULE"]["linknet"] >= 599 && time() - $chatBot->data["TARA_MODULE"]["linknet"] < 601) {
			$msg = "Raid leader may close the raid in 5 minutes if there is no pvp.";
			// notify linknet
			$chatBot->send("spam <myname> " . Setting::get('raid_topic_long') . " $msg", $this->settings['otspambot']);
			$chatBot->send("<yellow>$msg<end>",'priv');
		}
	}
} else if (isset($chatBot->data["TARA_MODULE"]["linknet"])) unset($chatBot->data["TARA_MODULE"]["linknet"]);

// timers
if (count($chatBot->data["timers"]) == 0) {
	return;
}

forEach ($chatBot->data["timers"] as $key => $timer) {
	$msg = "";

	$tleft = $timer->timer - time();
	$time_now = time();
	$set_time = $timer->settime;
	$name = $timer->name;
	$owner = $timer->owner;
	$mode = $timer->mode;
	
	if ($timer->callback != '') {
		call_user_func($timer->callback, $timer->callback_param);
		return;
	}
	if ($tleft >=301){
		if ($tleft >= 3599 && $tleft < 3601 && (($time_now - $set_time) >= 30)) {
			if ($name == "PrimTimer") {
				$msg = "Timer has <highlight>1 hour<end> left [set by <highlight>$owner<end>]";
			} else {
				$msg = "Timer <highlight>$name<end> has <highlight>1 hour<end> left [set by <highlight>$owner<end>]";
			}
		} else if ($tleft >= 1799 && $tleft < 1801 && (($time_now - $set_time) >= 30)) {
			if ($name == "PrimTimer") {
				$msg = "Timer has <highlight>30 minutes<end> left [set by <highlight>$owner<end>]";
			} else if (strpos($name,"Plant at former")!==false){
				$msg = "<font color=\"#FFFF00\">$name has 30 minutes left</font>";
			}
			else {
				$msg = "Timer <highlight>$name<end> has <highlight>30 minutes<end> left [set by <highlight>$owner<end>]";
			}
		} else if ($tleft >= 899 && $tleft < 901 && (($time_now - $set_time) >= 30)) {
			if ($name == "PrimTimer") {
				$msg = "Timer has <highlight>15 minutes<end> left [set by <highlight>$owner<end>]";
			} else if (strpos($name,"Plant at former")!==false){
				$msg = "<yellow>$name has 15 minutes left<end>";
			}
			else {
				$msg = "Timer <highlight>$name<end> has <highlight>15 minutes<end> left [set by <highlight>$owner<end>]";
			}
		}
	} else if ($tleft >= 31) { 
		if ($tleft >= 299 && $tleft < 301 && (($time_now - $set_time) >= 30)) {
			if ($name == "PrimTimer") {
				$msg = "Timer has <highlight>5 minutes<end> left [set by <highlight>$owner<end>]";
			} else if (strpos($name,"Plant at former")!==false){
				$msg = "<yellow>$name has 5 minutes left<end>";
			}
			else {
				$msg = "Timer <highlight>$name<end> has <highlight>5 minutes<end> left [set by <highlight>$owner<end>]";
			}
		} else if ($tleft >= 119 && $tleft < 121 && (($time_now - $set_time) >= 30)) {
			if (strpos($name,"Plant at former")!==false){
				$msg = "<yellow>$name has 2 minutes left<end>";
			}
		} else if ($tleft >= 59 && $tleft < 61 && (($time_now - $set_time) >= 30)) {
			if ($name == "PrimTimer") {
				$msg = "Timer has <highlight>1 minute<end> left [set by <highlight>$owner<end>]";
			} else if (strpos($name,"Plant at former")!==false){
				$msg = "<font color=\"#FFFF00\">$name has 1 minute left</font>";
			}
			else {
				$msg = "Timer <highlight>$name<end> has <highlight>1 minute<end> left [set by <highlight>$owner<end>]";
			}
		}
	} else {
		if ($tleft >= 29 && $tleft < 31) {
			if (strpos($name,"Plant at former")!==false)
				$msg = "<yellow>$name has 30 seconds left<end>";
		} else if ($tleft >= 14 && $tleft < 16) {
			if (strpos($name,"Plant at former")!==false)
				$msg = "<yellow>$name has 15 seconds left. PREPARE TO PLANT CT!!!<end>";
		} else if ($tleft >= 4 && $tleft < 6) {
			if (strpos($name,"Plant at former")!==false){
				for ($i = 5; $i > 3; $i--) {
					$msg = "<red>-------> $i &lt;-------<end>";
					$chatBot->send($msg, $mode);
					sleep(1);
				}

				for ($i = 3; $i > 1; $i--) {
					$msg = "<orange>-------> $i &lt;-------<end>";
					$chatBot->send($msg, $mode);
					sleep(1);
				}
				
				$msg = "<yellow>-------> $i &lt;-------<end>";
			}
		} else if ($tleft <= 0) {
			if ($tleft >= -600) {
				if ($name == "PrimTimer") {
					$msg = "<highlight>$owner<end> your timer has gone off";
				} else if (strpos($name,"Plant at former")!==false) {
					$chatBot->send("<font color=\"#FF0000\">BEEP BEEP BEEP BEEP BEEP BEEP BEEP!!!</font>",$mode);
					$msg = "<font color=\"#FF0000\">BEEP BEEP BEEP BEEP BEEP BEEP BEEP!!!</font>";
				} else {
					$msg = "<highlight>$owner<end> your timer named <highlight>$name<end> has gone off";
				}
			}
		
			Timer::remove_timer($key, $name, $owner);
		}
	}

	if ('' != $msg) {
		if ('msg' == $mode) {
			$chatBot->send($msg, $owner);
		} else {
			$chatBot->send($msg, $mode);
		}
	}
}

?>