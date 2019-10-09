<?php
	require_once 'db_utils.php';

	$MODULE_NAME = "HELPBOT_MODULE";

	DB::loadSQLFile($MODULE_NAME, "roll_kos");
	DB::loadSQLFile($MODULE_NAME, "playfields");

//	Command::register($MODULE_NAME, "", "kos.php", "kos", "guild", "Shows the Kill On Sight List");
	Command::register($MODULE_NAME, "", "time.php", "time", "all", "Shows the time in the different timezones");
	Command::register($MODULE_NAME, "", "whois.php", "whois", "all", "Display a character's info");
	Command::register($MODULE_NAME, "", "whois.php", "whoisall", "all", "Display a character's info for all dimensions");
	Command::register($MODULE_NAME, "", "whoisorg.php", "whoisorg", "all", "Display org info");
	Command::register($MODULE_NAME, "", "calc.php", "calc", "all", "Calculator");
//	Command::register($MODULE_NAME, "", "oe.php", "oe", "all", "OE");
	Command::register($MODULE_NAME, "", "player_history.php", "history", "all", "Show a history of a player");

	Command::register($MODULE_NAME, "", "playfields.php", "playfields", "all", "Shows all the playfields including IDs and short names");
	Command::register($MODULE_NAME, "", "waypoint.php", "waypoint", "all", "Creats a waypoint link");
	CommandAlias::register($MODULE_NAME, "playfields", "playfield");	

	// Flip or Roll command
	Command::register($MODULE_NAME, "", "roll.php", "flip", "all", "Flip a coin");
	Command::register($MODULE_NAME, "", "roll.php", "roll", "all", "Roll a random number");
	Command::register($MODULE_NAME, "", "roll.php", "verify", "all", "Verifies a flip/roll");

	Command::register($MODULE_NAME, "", "sync_once.php", "sync", "admin", "Attempts a sync");
	Event::register($MODULE_NAME, "5min", "sync_attempt.php", "players", "Attempt to sync from XML");

	// Help files
	Help::register($MODULE_NAME, "whois", "whois.txt", "all", "Show char stats at current and all dimensions");
    Help::register($MODULE_NAME, "oe", "oe.txt", "all", "Calculating the OE ranges");
    Help::register($MODULE_NAME, "roll", "roll.txt", "all", "How to use the flip and roll command");
    Help::register($MODULE_NAME, "history", "history.txt", "all", "History of a player");
    Help::register($MODULE_NAME, "time", "time.txt", "all", "Timezones");

?>
