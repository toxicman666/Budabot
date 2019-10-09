<?php

   
if (Whitelist::check($sender) || isset($chatBot->admins[$sender]) || $sender == ucfirst(strtolower($chatBot->settings["relaybot"]))) {
	// nothing to do
	return;
} else if (preg_match("/^(join|confirm|confirm (.+)|update)$/i", $message)) {
	//If the incoming message was a join request
	//Chek if he is a member of the Bot
	$is_member = false;
	if ($chatBot->settings["priv_req_open"] == "members") {
	  	$db->query("SELECT * FROM members_<myname> WHERE `name` = '$sender'");
		if ($db->numrows() == 0) {
		  	$msg = "<orange>This bot is locked to members.<end>";
		  	$chatBot->send($msg, $sender);
  		  	$restricted = true;
		  	return;
		} else {
			$is_member = true;
		}
	}

	//Check if he is a org Member
/*	if ($chatBot->settings["priv_req_open"] == "org" && !isset($chatBot->guildmembers)) {
	  	$msg = "<orange>Error! Only members of the org <myguild> can join this bot.<end>";
	  	$chatBot->send($msg, $sender);
	  	$restricted = true;
	  	return;
	} */
	
	//Get his character infos if minlvl or faction is set
	if ($chatBot->settings["priv_req_lvl"] != 0 || $chatBot->settings["priv_req_faction"] != "all") {
		$whois = Player::get_by_name($sender);
	   	if ($whois === null) {
		    $msg = "<orange>Unable to get your character info. Please try again later.<end>";
		    $chatBot->send($msg, $sender);
		  	$restricted = true;
		    return;
		}
	}
	
	//Check the Minlvl
	if ($chatBot->settings["priv_req_lvl"] != 0 && $chatBot->settings["priv_req_lvl"] > $whois->level) {
	  	$msg = "<orange>You have to be at least level {$chatBot->settings["priv_req_lvl"]} to use this bot.<end>";
	    $chatBot->send($msg, $sender);
	  	$restricted = true;
	    return;
	}
	
	//Check the Faction Limit
	if (($chatBot->settings["priv_req_faction"] == "Omni" || $chatBot->settings["priv_req_faction"] == "Clan" || $chatBot->settings["priv_req_faction"] == "Neutral") && $chatBot->settings["priv_req_faction"] != $whois->faction) {
	  	$msg = "<orange>You have your own bot.<end>";
	    $chatBot->send($msg, $sender);
	  	$restricted = true;
	    return;
	} else if ($chatBot->settings["priv_req_faction"] == "not Omni" || $chatBot->settings["priv_req_faction"] == "not Clan" || $chatBot->settings["priv_req_faction"] == "not Neutral") {
		$tmp = explode(" ", $chatBot->settings["priv_req_faction"]);
		if ($tmp[1] == $whois->faction) {
			$msg = "<orange>You have your own bot.<end>";
		    $chatBot->send($msg, $sender);
		  	$restricted = true;
		    return;
		}
	}

	//Check the Maximum Limit for the Private Channel
	if ($chatBot->settings["priv_req_maxplayers"] != 0 && count($chatBot->chatlist) > $chatBot->settings["priv_req_maxplayers"]) {
	  	$msg = "<orange>Error! Only players who are at least level {$chatBot->settings["priv_req_lvl"]} can join this bot.<end>";
	    $chatBot->send($msg, $sender);
	  	$restricted = true;
	    return;
	}
} else {
	//Check if he is a member of the Bot
	if ($chatBot->settings["tell_req_open"] == "members") {
	  	$db->query("SELECT * FROM members_<myname> WHERE `name` = '$sender'");
		if ($db->numrows() == 0) {
		  	$msg = "<orange>I am only responding to members of this bot!<end>";
		  	$chatBot->send($msg, $sender);
  		  	$restricted = true;
		  	return;
		}
	}

	//Check if he is a org Member
	/*
	if ($chatBot->settings["tell_req_open"] == "org" && !isset($chatBot->guildmembers[$sender])) {
	  	$msg = "<orange>I am only responding to members of the org <myguild>.<end>";
	  	$chatBot->send($msg, $sender);
	  	$restricted = true;
	  	return;
	}
	*/
	
	//Get his character infos if minlvl or faction is set
	if ($chatBot->settings["tell_req_lvl"] != 0 || $chatBot->settings["tell_req_faction"] != "all") {
		$whois = Player::get_by_name($sender);
	   	if ($whois === null) {
		    $msg = "<orange>Unable to get your character info. Please try again later.<end>";
		    $chatBot->send($msg, $sender);
		  	$restricted = true;
		    return;
		}
	}
	
	//Check the Minlvl
	if ($chatBot->settings["tell_req_lvl"] != 0 && $chatBot->settings["tell_req_lvl"] > $whois->level) {
	  	$msg = "<orange>You have to be at least level {$chatBot->settings["tell_req_lvl"]} to use this bot.<end>";
	    $chatBot->send($msg, $sender);
   	  	$restricted = true;
	    return;
	}
	
	//Check the Faction Limit
	if (($chatBot->settings["tell_req_faction"] == "Omni" || $chatBot->settings["tell_req_faction"] == "Clan" || $chatBot->settings["tell_req_faction"] == "Neutral") && $chatBot->settings["tell_req_faction"] != $whois->faction) {
	  	$msg = "<orange>You have your own bot.<end>";
	    $chatBot->send($msg, $sender);
	  	$restricted = true;
	    return;
	} else if ($chatBot->settings["tell_req_faction"] == "not Omni" || $chatBot->settings["tell_req_faction"] == "not Clan" || $chatBot->settings["tell_req_faction"] == "not Neutral") {
		$tmp = explode(" ", $chatBot->settings["tell_req_faction"]);
		if ($tmp[1] == $whois->faction) {
			$msg = "<orange>You have your own bot.<end>";
		    $chatBot->send($msg, $sender);
    	  	$restricted = true;
		    return;
		}
	}
	
	//Check if the person is in channel
	if(!isset($chatBot->chatlist[$sender])){
		$msg="<orange>Join the bot to use commands.<end>";
		$chatBot->send($msg, $sender);
		$restricted = true;
		return;
	}
}
?>