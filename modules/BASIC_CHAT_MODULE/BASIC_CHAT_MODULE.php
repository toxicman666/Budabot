<?php
	require_once 'functions.php';
	
	$MODULE_NAME = "BASIC_CHAT_MODULE";

	// Check macros	// oved to tara module
//	Command::register($MODULE_NAME, "", "check.php", "check", "rl", "Checks who of the raidgroup is in the area");

	// Topic set/show
	Event::register($MODULE_NAME, "joinPriv", "topic_logon.php", "topic", "Shows Topic when someone joins PrivChat");
	Event::register($MODULE_NAME, "logOn", "topic_logon.php", "topic", "Shows Topic on logon of members");
	Command::register($MODULE_NAME, "", "topic.php", "topic", "all", "Shows Topic");
	Subcommand::register($MODULE_NAME, "", "topic_change.php", "topic (.+)", "leader", "topic", "Changes Topic");
	Setting::add($MODULE_NAME, "topic", "Topic for Priv Channel", "noedit", "text", '');
	Setting::add($MODULE_NAME, "topic_setby", "Character who set the topic", "noedit", "text", '');
	Setting::add($MODULE_NAME, "topic_time", "Time the topic was set", "noedit", "text", '');
//	Command::register($MODULE_NAME, "", "basetopic.php", "basetopic", "all", "Set base topic");	
	Command::register($MODULE_NAME, "", "rally.php", "rally", "all", "Shows Rally");	
	Subcommand::register($MODULE_NAME, "", "rally_change.php", "rally (.+)", "leader", "rally", "Changes Rally");	
	Setting::add($MODULE_NAME, "rally", "Rally coordinates", "noedit", "text", '');	
	
	// Leader
	Command::register($MODULE_NAME, "priv", "leader.php", "leader", "all", "Sets the Leader of the raid");
	Subcommand::register($MODULE_NAME, "priv", "leader_set.php", "leader (.+)", "raidleader", "leader", "Set a specific Leader");
	Command::register($MODULE_NAME, "", "leaderecho_cmd.php", "leaderecho", "leader", "Set if the text of the leader will be repeated");
	Event::register($MODULE_NAME, "priv", "leaderecho.php", "leader", "leader echo");
	Event::register($MODULE_NAME, "leavePriv", "leader_leave.php", "leader", "Removes leader when the leader leaves the channel", 'leader');
	Setting::add($MODULE_NAME, "leaderecho", "Repeat the text of the raidleader", "edit", "options", "1", "true;false", "1;0");
	Setting::add($MODULE_NAME, "leaderecho_color", "Color for Raidleader echo", "edit", "color", "<font color=#FFFF00>");
	CommandAlias::register($MODULE_NAME, "leaderecho", "echo");	

	// Assist
	Command::register($MODULE_NAME, "", "assist.php", "assist", "all", "Shows an Assist macro");
	Command::register($MODULE_NAME, "", "addassist.php", "addassist", "leader", "Adds caller");
	Command::register($MODULE_NAME, "", "remassist.php", "remassist", "leader", "Removes person from assist");	
	CommandAlias::register($MODULE_NAME, "assist", "callers");
	Subcommand::register($MODULE_NAME, "", "assist_set.php", "assist (.+)", "leader", "assist", "Set a new assist");
	Setting::add($MODULE_NAME, "assist", "Assist", "noedit", "text", '');
	Command::register($MODULE_NAME, "", "healassist.php", "heal", "all", "Creates/showes an Doc Assist macro");
	Command::register($MODULE_NAME, "", "addhealassist.php", "addheal", "leader", "Adds person(s) to heal assist");
	Command::register($MODULE_NAME, "", "remhealassist.php", "remheal", "leader", "Removes person from heal assist");
	Subcommand::register($MODULE_NAME, "", "healassist_set.php", "heal (.+)", "leader", "heal", "Set a new Doc assist");
	CommandAlias::register($MODULE_NAME, "heal", "healassist");
	CommandAlias::register($MODULE_NAME, "addheal", "addhealassist");
	CommandAlias::register($MODULE_NAME, "remheal", "remhealassist");
	CommandAlias::register($MODULE_NAME, "remheal", "delheal");
	CommandAlias::register($MODULE_NAME, "remheal", "delhealassist");
	CommandAlias::register($MODULE_NAME, "remassist", "delassist");	
	Setting::add($MODULE_NAME, "healassist", "Healassist", "noedit", "text", '');
	Command::register($MODULE_NAME, "", "teamassist.php", "teamassist", "all", "Shows TeamAssist macros");
	Subcommand::register($MODULE_NAME, "", "teamassist_set.php", "teamassist (.+)", "leader", "teamassist", "Set a new TeamAssist");
	Setting::add($MODULE_NAME, "teamassist", "TeamAssist", "noedit", "text", '');
	
	// Tell
	Command::register($MODULE_NAME, "", "tell.php", "tell", "leader", "Repeats a message 3 times");
	Command::register($MODULE_NAME, "", "cmd.php", "cmd", "leader", "Creates a highly visible messaage");

	// Orders
	Command::register($MODULE_NAME, "", "orders.php", "orders", "all", "Shows Orders");
	Subcommand::register($MODULE_NAME, "", "orders.php", "orders (.+)", "leader", "orders", "Set new orders");
	Event::register($MODULE_NAME, "2min", "orders_check.php", "orders", "Timer check for raid orders");
	Setting::add($MODULE_NAME, "orders", "Raid orders", "noedit", "text", '');
	Help::register($MODULE_NAME, "orders", "orders.txt", "all", "Orders help");
	
	// Helpfiles
	Help::register($MODULE_NAME, "stats", "stats.txt", "all", "Statistics");
	Help::register($MODULE_NAME, "top", "stats.txt", "all", "Statistics");
	Help::register($MODULE_NAME, "teamassist", "assist.txt", "all", "Creating an Assist Macro");
	Help::register($MODULE_NAME, "assist", "assist.txt", "all", "Creating an Assist Macro");
	Help::register($MODULE_NAME, "check", "check.txt", "all", "See of the ppls are in the area");
	Help::register($MODULE_NAME, "heal", "healassist.txt", "all", "Creating an Healassist Macro");
	Help::register($MODULE_NAME, "leader", "leader.txt", "all", "Set a Leader of a Raid/Echo on/off");
	Help::register($MODULE_NAME, "tell", "tell.txt", "leader", "How to use tell");
	Help::register($MODULE_NAME, "topic", "topic.txt", "raidleader", "Set the Topic of the raid");
	Help::register($MODULE_NAME, "cmd", "cmd.txt", "leader", "How to use cmd");
	Help::register($MODULE_NAME, "rally", "rally.txt", "all", "How to use Rally");	
?>
