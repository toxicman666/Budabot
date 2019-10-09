<?php
	$MODULE_NAME = "PRIV_TELL_LIMIT";
	
	require_once 'Whitelist.class.php';
	
	DB::loadSqlFile($MODULE_NAME, 'whitelist');
	
	//Set/Show Limits
	Command::activate("msg", "$MODULE_NAME/config.php", "limits", "admin");
	Command::activate("msg", "$MODULE_NAME/config.php", "limit", "admin");
	Command::activate("msg", "$MODULE_NAME/whitelist.php", "whitelist", "admin");
	
	Command::activate("priv", "$MODULE_NAME/config.php", "limits", "admin");
	Command::activate("priv", "$MODULE_NAME/config.php", "limit", "admin");
	Command::activate("priv", "$MODULE_NAME/whitelist.php", "whitelist", "admin");
	
	Command::activate("guild", "$MODULE_NAME/config.php", "limits", "admin");
	Command::activate("guild", "$MODULE_NAME/config.php", "limit", "admin");
	Command::activate("guild", "$MODULE_NAME/whitelist.php", "whitelist", "admin");

	//Set/Show minlvl for Tells
	Command::activate("msg", "$MODULE_NAME/set_limits_tells.php", "tminlvl", "admin");
	Command::activate("priv", "$MODULE_NAME/set_limits_tells.php", "tminlvl", "admin");

	//Set/Show general limit for Tells
	Command::activate("msg", "$MODULE_NAME/set_limits_tells.php", "topen", "admin");
	Command::activate("priv", "$MODULE_NAME/set_limits_tells.php", "topen", "admin");

	//Set/Show faction limit for Tells
	Command::activate("msg", "$MODULE_NAME/set_limits_tells.php", "tfaction", "admin");
	Command::activate("priv", "$MODULE_NAME/set_limits_tells.php", "tfaction", "admin");

	//Set/Show minlvl for private channel
	Command::activate("msg", "$MODULE_NAME/set_limits_priv.php", "minlvl", "admin");
	Command::activate("priv", "$MODULE_NAME/set_limits_priv.php", "minlvl", "admin");

	//Set/Show general limit for private channel
	Command::activate("msg", "$MODULE_NAME/set_limits_priv.php", "openchannel", "admin");
	Command::activate("priv", "$MODULE_NAME/set_limits_priv.php", "openchannel", "admin");

	//Set/Show faction limit for private channel
	Command::activate("msg", "$MODULE_NAME/set_limits_priv.php", "faction", "admin");
	Command::activate("priv", "$MODULE_NAME/set_limits_priv.php", "faction", "admin");

	//Settings
	Setting::add($MODULE_NAME, "priv_req_lvl", "Private Channel Min Level Limit", "noedit", "number", "0", "", "", "admin");
	Setting::add($MODULE_NAME, "priv_req_faction", "Private Channel Faction Limit", "noedit", "options", "all", "", "", "admin");
	Setting::add($MODULE_NAME, "priv_req_open", "Private Channel General Limit", "noedit", "options", "all", "", "", "admin");
	Setting::add($MODULE_NAME, "priv_req_maxplayers", "Maximum number of players in the Private Channel", "noedit", "number", "0", "", "", "admin");

	Setting::add($MODULE_NAME, "tell_req_lvl", "Tells Min Level", "noedit", "number", "0", "", "", "admin");
	Setting::add($MODULE_NAME, "tell_req_faction", "Tell Faction Limit", "noedit", "options", "all", "", "", "admin");
	Setting::add($MODULE_NAME, "tell_req_open", "Tell General Limit", "noedit", "options", "all", "", "", "admin");

	//Help File
	Help::register($MODULE_NAME, "priv_tell_limits", "priv_tell_limits.txt", "admin", "Set Limits for Tells and Private Channel");
	Help::register($MODULE_NAME, "priv_req_lvl", "priv_req_lvl.txt", "admin", "Set level requirements to join the private channel");
	Help::register($MODULE_NAME, "priv_req_faction", "priv_req_faction.txt", "admin", "Set faction requirements to join the private channel");
	Help::register($MODULE_NAME, "priv_req_open", "priv_req_open.txt", "admin", "Set general requirements to join the private channel");
	Help::register($MODULE_NAME, "priv_req_maxplayers", "priv_req_maxplayers.txt", "admin", "Set the maximum amount of players allowed in the private channel at a time");
	Help::register($MODULE_NAME, "tell_req_lvl", "tell_req_lvl.txt", "admin", "Set level requirements to send tells to the bot");
	Help::register($MODULE_NAME, "tell_req_faction", "tell_req_faction.txt", "admin", "Set faction requirements to send tells to the bot");
	Help::register($MODULE_NAME, "tell_req_open", "tell_req_open.txt", "admin", "Set general requirements to send tells to the bot");
?>