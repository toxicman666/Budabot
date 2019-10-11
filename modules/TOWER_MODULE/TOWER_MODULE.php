<?php
	require_once 'Towers.class.php';
	require_once 'functions.php';

	$MODULE_NAME = "TOWER_MODULE";

	DB::loadSQLFile($MODULE_NAME, "tower_attack");
	DB::loadSQLFile($MODULE_NAME, "scout_info");
	DB::loadSQLFile($MODULE_NAME, "scout_info_history");
	DB::loadSQLFile($MODULE_NAME, "tower_site");
	DB::loadSQLFile($MODULE_NAME, "tower_info");

	Command::register($MODULE_NAME, "", "towers.php", "towers", "mod", "Towers");
	
	Command::register($MODULE_NAME, "", "scout.php", "forcescout", "rl", "Adds tower info to watch list (bypasses some of the checks)");
	Command::register($MODULE_NAME, "", "scout.php", "scout", "rl", "Adds tower info to watch list");
	Command::register($MODULE_NAME, "", "remscout.php", "unscout", "rl", "Removes tower scout info");

	Command::register($MODULE_NAME, "", "opentimes.php", "opentimes", "all", "Shows status of scouted Clan towers");
	Command::register($MODULE_NAME, "", "opentimes.php", "opentimesomni", "rl", "Shows status of scouted Omni towers");	
	Command::register($MODULE_NAME, "", "lc.php", "lc", "all", "Shows status of towers");
	
	Command::register($MODULE_NAME, "", "open.php", "open", "all", "Shows status of clan towers");	
	Command::register($MODULE_NAME, "", "open.php", "openomni", "rl", "Shows status of omni towers");
	Command::register($MODULE_NAME, "", "penalty.php", "penalty", "all", "Shows bases on penalty");

	Command::register($MODULE_NAME, "", "scoutneeded.php", "scoutneeded", "all", "Lists all sites that need to be scouted");
	Command::register($MODULE_NAME, "", "scouthistory.php", "scouthistory", "all", "Shows history of scout updates");
	
	Command::register($MODULE_NAME, "", "attacks.php", "attacks", "all", "Shows the last Tower Attack messages");
	CommandAlias::register($MODULE_NAME, "attacks", "battle");
	CommandAlias::register($MODULE_NAME, "attacks", "battles");

  	Command::register($MODULE_NAME, "", "victory.php", "victory", "all", "Shows the last Tower Battle results");
	
  	Command::register($MODULE_NAME, "", "rush.php", "rush", "leader", "Order to rush");
  	Command::register($MODULE_NAME, "", "os.php", "os", "all", "Order to run from OS");	
  	Command::register($MODULE_NAME, "", "as.php", "as", "all", "Order to avoid AS");	
	
	Command::register($MODULE_NAME, "", "stats.php", "top", "all", "Top statistics");
	Command::register($MODULE_NAME, "", "stats.php", "topscout", "all", "Top scouts");
	CommandAlias::register($MODULE_NAME, "topscout", "epeen");
	CommandAlias::register($MODULE_NAME, "topscout", "topscouts");

	Setting::add($MODULE_NAME, "tower_attack_spam", "Layout types when displaying tower attacks", "edit", "options", "1", "off;compact;normal;full", '0;1;2;3', "mod");
	Setting::add($MODULE_NAME, "tower_faction_def", "Display certain factions defending", "edit", "options", "7", "none;clan;neutral;clan+neutral;omni;clan+omni;neutral+omni;all", '0;1;2;3;4;5;6;7', "mod");
	Setting::add($MODULE_NAME, "tower_faction_atk", "Display certain factions attacking", "edit", "options", "7", "none;clan;neutral;clan+neutral;omni;clan+omni;neutral+omni;all", '0;1;2;3;4;5;6;7', "mod");
	Setting::add($MODULE_NAME, "check_org_name_on_scout", "Verify that the org name has been attacked", "edit", "options", "1", "false;true", '0;1', "mod");
	Setting::add($MODULE_NAME, "check_close_time_on_scout", "DVerify that close time less than 1 hour later than the time is was destroyed", "edit", "options", "1", "false;true", '0;1', "mod");		
	Setting::add($MODULE_NAME, "hide_omni_scout", "Hide omni scout info", "edit", "options", "1", "on;off", "1;0");
	Setting::add($MODULE_NAME, "tower_record_delay", "Delay tower records", "edit", "options", "0", "0;1;2;3;4;5;6;7;8", "0;1;2;3;4;5;6;7;8");	
	
	Event::register($MODULE_NAME, "towers", "attack_messages.php", "none", "Record attack messages");
	Event::register($MODULE_NAME, "towers", "victory_messages.php", "none", "Record victory messages");
	Event::register($MODULE_NAME, "5min", "scout_query.php", "none", "Checks if any bases require update and updates it");	

	// help files
	Help::register($MODULE_NAME, "attacks", "attacks.txt", "all", "Show attack message commands and options");
	Help::register($MODULE_NAME, "victory", "victory.txt", "all", "Show victory message commands and options");
	Help::register($MODULE_NAME, "scout", "scout.txt", "all", "How to add a tower site to the watch list");
	Help::register($MODULE_NAME, "scouthistory", "scouthistory.txt", "all", "Scout update history");	
	Help::register($MODULE_NAME, "lc", "lc.txt", "all", "How to use land control commands");
	Help::register($MODULE_NAME, "open", "open.txt", "all", "How to use open/openomni commands");
	Help::register($MODULE_NAME, "opentimes", "opentimes.txt", "all", "How to use opentimes/opentimesomni commands");		
?>
