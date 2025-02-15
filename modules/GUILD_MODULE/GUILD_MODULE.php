<?php
	$MODULE_NAME = "GUILD_MODULE";
	
	DB::loadSQLFile($MODULE_NAME, "org_members");

	// Setup
	Event::register($MODULE_NAME, "setup", "setup.php");
	
	// Logon Handling
	Command::register($MODULE_NAME, "", "logon_msg.php", "logon", "guild", "Sets a Logon Msg");
	
	// Lastseen
	Command::register($MODULE_NAME, "", "lastseen.php", "lastseen", "guild", "Shows the logoff time of a player");
	
	// Tellall
	Command::register($MODULE_NAME, "", "tellall.php", "tellall", "leader", "Sends a tell to all online guild members");

    // Org Roster list creation and Notify on/off handling
	Event::register($MODULE_NAME, "24hrs", "roster_guild.php", "none", "Download guild roster xml and update guild members");
	Event::register($MODULE_NAME, "orgmsg", "notify_auto.php", "none", "Automatically add and remove chars from the guild roster as they leave and join the guild");
	Command::register($MODULE_NAME, "", "notify.php", "notify", "guildadmin", "Adding a char manually to the notify list");
	
	Command::register($MODULE_NAME, "", "updateorg.php", "updateorg", "guildadmin", "Forcing an update of the org roster");
	
	Event::register($MODULE_NAME, "logOn", "notify_logon_guild.php", "none", "Shows an org member login in chat");
	Event::register($MODULE_NAME, "logOff", "notify_logoff_guild.php", "none", "Shows an org member logoff in chat");
	
	Event::register($MODULE_NAME, "logOff", "record_lastseen.php", "none", "Records when each member of the org logs off for lastseen command");
	
	Setting::add($MODULE_NAME, "max_logon_msg_size", "The maximum number of characters a logon message can have", "edit", "number", "200", "100;200;300;400", '', "mod");
	
	// Help files
	Help::register($MODULE_NAME, "updateorg", "updateorg.txt", "guildadmin", "Force an update of org roster");
	Help::register($MODULE_NAME, "lastseen", "lastseen.txt", "guild", "Check when an orgmember was online");
	Help::register($MODULE_NAME, "logon", "logon_msg.txt", "guild", "Changing your logon message");
	Help::register($MODULE_NAME, "notify", "notify.txt", "guildadmin", "Add or remove a player from the notify list");
	Help::register($MODULE_NAME, "tellall", "tellall.txt", "leader", "Send a tell to all online guild members");
?>