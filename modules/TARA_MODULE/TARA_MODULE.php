<?php
	require_once 'Tara.class.php';

	$MODULE_NAME = "TARA_MODULE";
	
	DB::loadSQLFile($MODULE_NAME, "tara_points");
	DB::loadSQLFile($MODULE_NAME, "tara_points_history");
	DB::loadSQLFile($MODULE_NAME, "tara_raids");
	DB::loadSQLFile($MODULE_NAME, "tara_raidlist");
	DB::loadSQLFile($MODULE_NAME, "tara_raid_history");
	DB::loadSQLFile($MODULE_NAME, "tara_raid_categories");
	DB::loadSQLFile($MODULE_NAME, "tara_loot");
	
	// spawntime
	Command::register($MODULE_NAME, "", "spawntime.php", "spawntime", "all", "Display spawntime");
	Command::register($MODULE_NAME, "", "set_spawntime.php", "setspawntime", "rl", "Set spawntime manually");
	Command::register($MODULE_NAME, "", "set_spawntime.php", "resetspawntime", "rl", "Reset spawntime to last raid");
	CommandAlias::register($MODULE_NAME, "spawntime", "spawntimer");
	CommandAlias::register($MODULE_NAME, "spawntime", "spawn");
	
	// points
	Command::register($MODULE_NAME, "", "points.php", "mypoints", "all", "Display your points");
	Command::register($MODULE_NAME, "", "points.php", "points", "all", "Display points");
	CommandAlias::register($MODULE_NAME, "mypoints", "mypoint");
	Command::register($MODULE_NAME, "", "raid_manage.php", "raidpoints", "leader", "Award points");

	// stats
	Command::register($MODULE_NAME, "", "stats.php", "stats", "all", "Member statistics");
	CommandAlias::register($MODULE_NAME, "stats", "stat");
	Command::register($MODULE_NAME, "", "stats.php", "top", "all", "Top statistics");
	
	// raids
	Command::register($MODULE_NAME, "", "raid_manage.php", "raids", "leader", "Display available raids");	
	Command::register($MODULE_NAME, "", "raid_manage.php", "raidstart", "leader", "Start raid");
	Command::register($MODULE_NAME, "", "raid_manage.php", "forceraid", "rl", "Force start raid");	
	Command::register($MODULE_NAME, "", "raid_manage.php", "raidupdate", "leader", "Update raid");
	Command::register($MODULE_NAME, "", "raid_manage.php", "raidclose", "leader", "Close raid");
	Command::register($MODULE_NAME, "", "raid_manage.php", "raidopen", "leader", "Close raid");		
	Command::register($MODULE_NAME, "", "raid_manage.php", "raidend", "leader", "End raid");
	Command::register($MODULE_NAME, "", "raid_history.php", "raidhistory", "all", "See raid history");
	Command::register($MODULE_NAME, "", "loot_history.php", "loothistory", "all", "See loot history");
	Command::register($MODULE_NAME, "", "loot_history.php", "lootplayer", "all", "See loot history for player");	
	CommandAlias::register($MODULE_NAME, "raidhistory", "history");
	CommandAlias::register($MODULE_NAME, "lootplayer", "won");	
	CommandAlias::register($MODULE_NAME, "lootplayer", "itemswon");
	
	Command::register($MODULE_NAME, "", "raid_admin.php", "undoraid", "admin", "Remove points for raid");
	Command::register($MODULE_NAME, "", "raid_admin.php", "replaceraid", "admin", "Replace points for raid with correct topic");

	Command::register($MODULE_NAME, "", "raid_admin.php", "mergetomain", "admin", "Remove alt points for raids with alt and main present");	
	
	// raidlist
	Command::register($MODULE_NAME, "", "raid_list.php", "raidlist", "all", "Display raidlist");
	Command::register($MODULE_NAME, "", "raid_list.php", "raidadd", "leader", "Add player to raidlist");
	Command::register($MODULE_NAME, "", "raid_list.php", "forceadd", "mod", "Force add player to raidlist");
	Command::register($MODULE_NAME, "", "raid_list.php", "raidkick", "all", "Kick self from raidlist");
	Subcommand::register($MODULE_NAME, "", "raid_list.php", "raidkick (.+)", "leader", "raidkick", "Kick player from raidlist");	
	// Check macros
	Command::register($MODULE_NAME, "", "raid_list.php", "check", "leader", "Checks who of raidlist is in the area");
	Subcommand::register($MODULE_NAME, "", "check.php", "check (.+)", "all", "check", "Checks who of the raidgroup is in the area");
	CommandAlias::register($MODULE_NAME, "check", "raidcheck");
//	Command::register($MODULE_NAME, "", "raid_list.php", "raidcheck", "leader", "Check people present");
	Event::register($MODULE_NAME, "2sec", "raidlist_check.php", "raidlist", "Check if anyone not in channel for too long");
	
	// loot auction
	Command::register($MODULE_NAME, "", "raid_loot.php", "raidloot", "leader", "Show loot list");
	Command::register($MODULE_NAME, "", "raid_loot.php", "abort", "leader", "Abort auction");	
	Subcommand::register($MODULE_NAME, "", "raid_loot.php", "raidloot (.+)", "leader", "raidloot", "Start auction for item");
	Command::register($MODULE_NAME, "", "raid_loot.php", "bid", "all", "Bid on item");
	Command::register($MODULE_NAME, "", "raid_loot.php", "unbid", "all", "Remove bid");	
	Event::register($MODULE_NAME, "2sec", "auction_check.php", "raidloot", "Auction timer check");
	
	Command::register($MODULE_NAME, "", "refund.php", "refund", "mod", "Refund item");
	Command::register($MODULE_NAME, "", "refund.php", "refundhistory", "all", "Refund item");	
	Command::register($MODULE_NAME, "", "refund.php", "unrefund", "admin", "Undo Refund item");	
	CommandAlias::register($MODULE_NAME, "refund", "refunditem");	
	
	// loot table
	Command::register($MODULE_NAME, "", "setup_loot.php", "addloot", "admin", "Add loot to table");	
	Command::register($MODULE_NAME, "", "setup_loot.php", "remloot (.+)", "admin", "Remove loot to table");	
	
	Setting::add($MODULE_NAME, "tara_spawntime_hours", "Spawntime hours", "edit", "text", "9", "admin");	
	Setting::add($MODULE_NAME, "tara_spawntime", "Spawntime", "noedit", "text", '0');
	Setting::add($MODULE_NAME, "tara_spawntime_by", "Spawntime by", "noedit", "text", '');		
	Setting::add($MODULE_NAME, "tara_spawntime_set", "Spawntime set", "noedit", "text", '0');
	
	Setting::add($MODULE_NAME, "raid_topic", "raid topic", "noedit", "text", '');
	Setting::add($MODULE_NAME, "raid_topic_time", "raid topic set", "noedit", "text", '');	
	Setting::add($MODULE_NAME, "raid_topic_long", "raid topic long name", "noedit", "text", '');	
	Setting::add($MODULE_NAME, "raid_status", "raid status", "noedit", "text", '');
	Setting::add($MODULE_NAME, "raid_by", "raid topic set by", "noedit", "text", '');

	//Helpfile
	Help::register($MODULE_NAME, "points", "points.txt", "all", "How check points");
	Help::register($MODULE_NAME, "spawntime", "spawntime.txt", "all", "How use spawntime");
	Help::register($MODULE_NAME, "spawn", "spawntime.txt", "all", "How use spawntime");
	Help::register($MODULE_NAME, "setspawntime", "spawntime.txt", "all", "How use spawntime");
	Help::register($MODULE_NAME, "raidstart", "raid_manage.txt", "all", "How to manage raid");
	Help::register($MODULE_NAME, "raidend", "raid_manage.txt", "all", "How to manage raid");
	Help::register($MODULE_NAME, "raidloot", "raid_manage.txt", "all", "How to manage raid");
	Help::register($MODULE_NAME, "raidupdate", "raid_manage.txt", "all", "How to manage raid");
	Help::register($MODULE_NAME, "raidpoints", "raid_manage.txt", "all", "How to manage raid");
	Help::register($MODULE_NAME, "raidlist", "raidlist.txt", "all", "How to use raidlist");
	Help::register($MODULE_NAME, "raidcheck", "raidlist.txt", "all", "How to use raidlist");
	Help::register($MODULE_NAME, "raidadd", "raidlist.txt", "all", "How to use raidlist");
	Help::register($MODULE_NAME, "raidkick", "raidlist.txt", "all", "How to use raidlist");
	Help::register($MODULE_NAME, "bid", "bid.txt", "all", "How to bid");
	Help::register($MODULE_NAME, "lootplayer", "history.txt", "all", "Raid and Loot History");	
	Help::register($MODULE_NAME, "history", "history.txt", "all", "Raid and Loot History");	
	Help::register($MODULE_NAME, "raidhistory", "history.txt", "all", "Raid and Loot History");
	Help::register($MODULE_NAME, "loothistory", "history.txt", "all", "Raid and Loot History");	
	Help::register($MODULE_NAME, "top", "stats.txt", "all", "Raid statistics");
	Help::register($MODULE_NAME, "stats", "stats.txt", "all", "Raid statistics");
	Help::register($MODULE_NAME, "auction", "auction.txt", "all", "How does auction work");	
	Help::register($MODULE_NAME, "unbid", "bid.txt", "all", "How to bid");	
?>