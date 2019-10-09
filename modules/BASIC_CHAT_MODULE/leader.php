<?php


//if ($chatBot->settings["leaderecho"] == 1) {
	$status = "<green>Enabled<end>";
	$cmd = "off";
//} else {
//	$status = "<red>Disabled<end>";
//	$cmd = "on";
//}

if (preg_match("/^leader$/i", $message)) {
  	if ($chatBot->data["leader"] == $sender) {
		unset($chatBot->data["leader"]);
	  	$msg = "Leader cleared.";
	} else if ($chatBot->data["leader"] != "") {
		if(!isset($chatBot->admins[$sender])){
			$msg = "You can't take lead from <highlight>{$chatBot->data["leader"]}<end>.";
		} else if ($chatBot->admins[$sender]["level"] >= $chatBot->admins[$chatBot->data["leader"]]["level"]){
  			$chatBot->data["leader"] = $sender;
		  	$msg = "{$sender} is now Leader. Leader echo is currently {$status}. You can change it with <symbol>echo {$cmd}";
			$chatBot->settings["leaderecho"] = 1;
		} else {
			$msg = "You can't take lead from <highlight>{$chatBot->data["leader"]}<end>.";
		}
	} else {
		$chatBot->data["leader"] = $sender;
	  	$msg = "{$sender} is now Leader. Leader echo is currently {$status}. You can change it with <symbol>leaderecho {$cmd}";
		$chatBot->settings["leaderecho"] = 1;
	}
  	$chatBot->send($msg, 'priv');

} else {
	$syntax_error = true;
}

?>