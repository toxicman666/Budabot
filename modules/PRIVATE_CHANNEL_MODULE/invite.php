<?php


if (preg_match("/^invite (.+)$/i", $message, $arr)) {
    $uid = $chatBot->get_uid($arr[1]);
    $name = ucfirst(strtolower($arr[1]));
	if ($chatBot->vars["name"] == $name) {
		$msg = "You cannot invite the bot to its own private channel.";
	} else if ($uid) {
		$whois = Player::get_by_name($name);
		if($whois->faction=="Clan"){
			$msg = "<orange>Clanners have their own bot.<end>";
		} else {
			if (Ban::is_banned($name)){
				$msg = "<orange>{$name} is banned from this bot<end>";
			} else {
				$msg = "Invited <highlight>$name<end> to this channel.";
		//	  	$chatBot->privategroup_kick($name);
				$chatBot->privategroup_invite($name);
				$msg2 = "You have been invited to the <highlight><myname><end> channel by <highlight>$sender<end>";
				$chatBot->send($msg2, $name);
			}
		}
    } else {
		$msg = "Player <highlight>{$name}<end> does not exist.";
	}
	
	$chatBot->send($msg, $sendto);
} else {
	$syntax_error = true;
}
?>