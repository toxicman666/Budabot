<?php

if (Whitelist::check($sender) || isset($chatBot->admins[$sender]) || $sender == ucfirst(strtolower($chatBot->settings["relaybot"]))) {
	// nothing to do
	return;
} else if (preg_match("/^(join|points|mypoints|mypoint|confirm|confirm (.+)|update|register|register accept|forums|forum|spawntime|spawn|rules|alts)$/i", $message)||preg_match("/^alts (rem|del|remove|delete) ([a-z0-9-]+)$/i", $message, $arr)||preg_match("/^alts add ([a-z0-9- ]+)$/i", $message, $arr)||preg_match("/^(spawntime|spawn) ([a-z]+)$/i", $message, $arr)) {
	
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

	//If the incoming message was a join request or allowed commands
	//Check if he is a member of the Bot
	$is_member = false;

	if (preg_match("/^(join|points|mypoint|mypoints|spawntime|spawn)$/i", $message)||preg_match("/^alts add ([a-z0-9- ]+)$/i", $message, $arr)){
		$is_member = Tara::is_registered($sender);
		if ($is_member===null) {
			$msg = "<orange>You are not registered in the system. /tell <myname> !register<end>";
			$chatBot->send($msg, $sender);
			$restricted = true;
			return;
		}
	}
	if (preg_match("/^spawntime$/i", $message) && !isset($chatBot->chatlist[$sender])){
		$account = Tara::get_account_name($sender);
		$forums = Tara::forums($account);
		if($forums===false){
			$blob = ":::: <yellow>How to register at OmniHQ<end> ::::\n\nIf you are already registered, log the toon you registered with or try " . Text::make_link("updating your info","/tell <myname> update",'chatcmd') . "\n\nGo to " . Text::make_link("www.omnihq.net","/start http://www.omnihq.net/component/user/?task=register",'chatcmd') . " and follow instructions to create account\n<white>(Note that the Display Name has to be exact name of your main character)<end>\n\nActivate account AND log in\n\n" . Text::make_link("Confirm account ingame","/tell <myname> !confirm",'chatcmd') . "\n\n\n<white>Note: if you register multiple accounts and get extra points on registration, then decide to merge accounts, your extra points will be deducted from alt.<end>\n\nCheck your forum status: " . Text::make_link("/tell <myname> !forums","/tell <myname> !forums", 'chatcmd');		
			$msg = "<orange>Register your main at www.omnihq.net to check spawntime without joining<end> (" . Text::make_link("How?",$blob,'blob') . ")";
			$chatBot->send($msg, $sender);
			$restricted = true;
			return;			
		}
	}
} else {

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