<?php

   
//Check if at least one timer is running
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
		if ($tleft >= 3599 && $tleft < 3601 && ((time() - $set_time) >= 30)) {
			if ($name == "PrimTimer") {
				$msg = "Timer has <highlight>1 hour<end> left [set by <highlight>$owner<end>]";
			} else {
				$msg = "Timer <highlight>$name<end> has <highlight>1 hour<end> left [set by <highlight>$owner<end>]";
			}
		} else if ($tleft >= 1799 && $tleft < 1801 && ((time() - $set_time) >= 30)) {
			if ($name == "PrimTimer") {
				$msg = "Timer has <highlight>30 minutes<end> left [set by <highlight>$owner<end>]";
			} else if (strpos($name,"Plant at former")!==false){
				$msg = "<font color=\"#FFFF00\">$name has 30 minutes left</font>";
			}
			else {
				$msg = "Timer <highlight>$name<end> has <highlight>30 minutes<end> left [set by <highlight>$owner<end>]";
			}
		} else if ($tleft >= 899 && $tleft < 901 && ((time() - $set_time) >= 30)) {
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
		if ($tleft >= 299 && $tleft < 301 && ((time() - $set_time) >= 30)) {
			if ($name == "PrimTimer") {
				$msg = "Timer has <highlight>5 minutes<end> left [set by <highlight>$owner<end>]";
			} else if (strpos($name,"Plant at former")!==false){
				$msg = "<yellow>$name has 5 minutes left<end>";
			}
			else {
				$msg = "Timer <highlight>$name<end> has <highlight>5 minutes<end> left [set by <highlight>$owner<end>]";
			}
		} else if ($tleft >= 119 && $tleft < 121 && ((time() - $set_time) >= 30)) {
			if (strpos($name,"Plant at former")!==false){
				$msg = "<yellow>$name has 2 minutes left<end>";
			}
		} else if ($tleft >= 59 && $tleft < 61 && ((time() - $set_time) >= 30)) {
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