<?php 
	require_once 'Ban.class.php';

	$MODULE_NAME = "BAN";

	//Commands
	Command::activate("msg", "$MODULE_NAME/ban_player.php", "ban", "mod");
	Command::activate("msg", "$MODULE_NAME/ban_player.php", "fastban", "rl");
	Command::activate("msg", "$MODULE_NAME/ban_player.php", "quickban", "rl");		
	Command::activate("msg", "$MODULE_NAME/ban_player.php", "banorg", "mod");
	Command::activate("msg", "$MODULE_NAME/unban.php", "unban", "mod");
	Command::activate("msg", "$MODULE_NAME/unban.php", "unbanorg", "mod");	
	Command::activate("msg", "$MODULE_NAME/banlist.php", "banlist", "rl");
	Command::activate("msg", "$MODULE_NAME/ban_player.php", "banhistory", "rl");	
	Command::activate("priv", "$MODULE_NAME/ban_player.php", "fastban", "rl");
	Command::activate("priv", "$MODULE_NAME/ban_player.php", "quickban", "rl");
//	Command::activate("priv", "$MODULE_NAME/ban_player.php", "banhistory", "rl");	
//	Command::activate("priv", "$MODULE_NAME/ban_player.php", "ban", "mod");
//	Command::activate("priv", "$MODULE_NAME/unban.php", "unban", "mod");
//	Command::activate("priv", "$MODULE_NAME/banlist.php", "banlist");
//	Command::activate("guild", "$MODULE_NAME/ban_player.php", "ban", "mod");
//	Command::activate("guild", "$MODULE_NAME/unban.php", "unban", "mod");
//	Command::activate("guild", "$MODULE_NAME/banlist.php", "banlist");

	//Events
	Event::activate("15mins", "$MODULE_NAME/check_tempban.php");
	Event::activate("24hrs", "$MODULE_NAME/update_names.php");	

	//Setup
	Event::activate("setup", "$MODULE_NAME/setup.php");
	
	//Help Files
	Help::register($MODULE_NAME, "ban", "ban.txt", "mod", "Ban a person from the bot");
?>