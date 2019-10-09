<?php

if (preg_match("/^setspawntime ((([0-9]+)(d|day|days))?.?(([0-9]+)(h|hr|hrs))?.?(([0-9]+)(m|min|mins))?.?(([0-9]+)(s|sec|secs))?)$/i", $message, $arr)) {

	$time_string = $arr[1];

	$run_time = 0;

	if (preg_match("/([0-9]+)(d|day|days)/i", $time_string, $day)) {
		if ($day[1] < 1) {
			$msg = "No valid time specified!";
		    $chatBot->send($msg, $sendto);
		    return;
		}
		$run_time += $day[1] * 86400;
	}

	if (preg_match("/([0-9]+)(h|hr|hrs)/i", $time_string, $hours)) {
		if ($hours[1] < 1) {
			$msg = "No valid time specified!";
		    $chatBot->send($msg, $sendto);
		    return;		  	
		}
		$run_time += $hours[1] * 3600;
	}

	if (preg_match("/([0-9]+)(m|min|mins)/i", $time_string, $mins)) {
		if ($mins[1] < 1) {
			$msg = "No valid time specified!";
		    $chatBot->send($msg, $sendto);
		    return;		  	
		}
		$run_time += $mins[1] * 60;
	}

	if (preg_match("/([0-9]+)(s|sec|secs)/i", $time_string, $secs)) {
		if ($secs[1] < 1) {
			$msg = "No valid time specified!";
		    $chatBot->send($msg, $sendto);
		    return;		  	
		}
		$run_time += $secs[1];
	}

	if ($run_time == 0) {
	  	$msg = "No valid Time specified!";
	    $chatBot->send($msg, $sendto);
	    return;		  	
	}

    $spawn = time() + $run_time;
	Setting::save("tara_spawntime",$spawn);
	Setting::save("tara_spawntime_by",$sender);
	Setting::save("tara_spawntime_set",time());

	$str = Util::unixtime_to_readable($run_time);
	$msg = "Spawntime was set to <highlight>$str<end> from now.";
    $chatBot->send($msg, $sendto);
	unset($chatBot->data["TARA_MODULE"]["spawntime"]);
	
} else if (preg_match("/^resetspawntime$/i", $message)) {
	Setting::save("tara_spawntime",0);
	Setting::save("tara_spawntime_by",$sender);
	Setting::save("tara_spawntime_set",0);
	$chatBot->send("Manual spawntime was reset.",$sendto);
	unset($chatBot->data["TARA_MODULE"]["spawntime"]);
} else {
	$syntax_error = true;
}
?>