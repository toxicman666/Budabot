<?php
	require_once 'Timer.class.php';

	$MODULE_NAME = "TIMERS_MODULE";

	Event::register($MODULE_NAME, "setup", "setup.php");

	// Timer Module
	Command::register($MODULE_NAME, "", "timers.php", "timers", "all", "Set timers/Show running Timers");
	Command::register($MODULE_NAME, "", "planttimer.php", "planttimer", "all", "Set timers for recent tower victories");	
	CommandAlias::register($MODULE_NAME, "timers", "timer");
	CommandAlias::register($MODULE_NAME, "planttimer", "planttimers");	
	Command::register($MODULE_NAME, "", "countdown.php", "countdown", "all", "Set a countdown");
	Command::register($MODULE_NAME, "", "countdown.php", "cd", "all", "Set a countdown");

	Event::register($MODULE_NAME, "2sec", "timers_check.php", "timer", "Checks timers and periodically updates chat with time left");
	Event::register($MODULE_NAME, "5min", "warnings_check.php", "timer", "Checks if needed to display warning");	
	
	Setting::add($MODULE_NAME, "timers_window", "Show running timers in a window or directly", "edit", "options", "1", "window only;chat only;window after 3;window after 4;window after 5", '1;2;3;4;5', "mod");
	Setting::add($MODULE_NAME, "check_tl7_percent", "Displays warning when tl7 zone hits high player percentage", "edit", "options", "1", "off;on", '0;1', "admin");
	Setting::add($MODULE_NAME, "check_clan_twinks", "Displays warning when there are enough clan twinks online", "edit", "options", "1", "off;on", '0;1', "admin");	
	
	//Help files
	Help::register($MODULE_NAME, "timer", "timer.txt", "all", "Set/Show Timers");
	Help::register($MODULE_NAME, "planttimer", "planttimer.txt", "all", "Set/Show Plant Timers");	
?>