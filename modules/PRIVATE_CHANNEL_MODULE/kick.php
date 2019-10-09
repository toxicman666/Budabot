<?php

if (preg_match("/^kick all$/i", $message)) {
  	$msg = "Everyone will be kicked from this channel in 10 seconds. [by <highlight>$sender<end>]";
  	$chatBot->send($msg, 'priv');
  	$chatBot->data["priv_kickall"] = time() + 10;
	Event::activate("2sec", "PRIVATE_CHANNEL_MODULE/kickall_event.php");
} else if (preg_match("/^kick ([a-z0-9-]+)$/i", $message, $arr)) {
	$uid = $chatBot->get_uid($arr[1]);
    $name = ucfirst(strtolower($arr[1]));

	// check if high enough rank
	$main = Alts::get_main($name);
	if (isset($chatBot->admins[$main]))
		if (!isset($chatBot->admins[$sender])||(int)$chatBot->admins[$sender]["level"]<=(int)$chatBot->admins[$main]["level"]){
			$chatBot->send("<orange>Player $name ($main) has too high rank for you to kick.<end>", $sendto);
			return;			
		}
		
	// check alts
	$alts = Alts::get_alts($main);
	foreach($alts as $alt)
		if (isset($chatBot->admins[$alt]))
			if (!isset($chatBot->admins[$sender])||(int)$chatBot->admins[$sender]["level"]<=(int)$chatBot->admins[$alt]["level"]){
				$chatBot->send("<orange>Player $name ($alt) has too high rank for you to kick.<end>", $sendto);
				return;			
			} 

    if ($uid) {
        if (isset($chatBot->chatlist[$name])) {
			$msg = "<highlight>$name<end> has been kicked from the private channel.";
		} else {
			$msg = "<highlight>$name<end> is not in the private channel.";
		}

		// we kick whether they are in the channel or not in case the channel list is bugged
		$chatBot->privategroup_kick($name);
    } else {
		$msg = "Player <highlight>{$name}<end> does not exist.";
	}
	
	$chatBot->send($msg, $sendto);
} else {
	$syntax_error = true;
}
?>