<?php
	require_once 'Clans.class.php';
	require_once 'clans_functions.php';

	$MODULE_NAME = "CLANS_MODULE";
	
	DB::loadSQLFile($MODULE_NAME, "clans");
	
	Command::register($MODULE_NAME, "", "clans.php", "clans", "all", "See clan twinks online");
	Subcommand::register($MODULE_NAME, "", "clans_manage.php", "clans add (.+)", "all", "clans", "Add a clan twink to watch list");
	Subcommand::register($MODULE_NAME, "", "clans_manage.php", "clans rem (.+)", "rl", "clans", "Remove a clan twink from watch list");
	CommandAlias::register($MODULE_NAME, "clans", "clan");	
	Setting::add($MODULE_NAME, "warn_clans", "How many clans required to display warning", "edit", "options", "5", "3;4;5;6;7;8;9;10", '3;4;5;6;7;8;9;10', "admin");		

	Event::register($MODULE_NAME, "logOn", "clans.php", "none", "Gets online status for clans");
	Event::register($MODULE_NAME, "logOff", "clans.php", "none", "Gets offline status for clans");	
	//Helpfile
	Help::register($MODULE_NAME, "clans", "clans.txt", "all", "How to check clan twinks online");
	Help::register($MODULE_NAME, "clan", "clans.txt", "all", "How to check clan twinks online");	

?>