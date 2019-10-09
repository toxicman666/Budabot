<?php
	require_once 'Modvote.class.php';

	$MODULE_NAME = "MODVOTE_MODULE";
	
	DB::loadSQLFile($MODULE_NAME, "mod_votes");
	DB::loadSQLFile($MODULE_NAME, "mod_entrants");
	DB::loadSQLFile($MODULE_NAME, "mod_history");
	
	Command::register($MODULE_NAME, "", "mod.php", "mod", "all", "Manage mod vote entrants");
	Command::register($MODULE_NAME, "", "mod.php", "modclear", "all", "Clear mod vote entrants");	
	Command::register($MODULE_NAME, "", "modvote.php", "modvote", "all", "Cast a mod vote");
	
	Event::register($MODULE_NAME, "30mins", "modvote_check.php", "none", "Check if it's needed to start/end/display vote");
	Event::register($MODULE_NAME, "joinPriv", "show_votes.php", "none", "Displays vote for members joining bot");
	
	Setting::add($MODULE_NAME, "mod_vote_in_progress", "Mod vote in progress", "noedit", "text", '0');
	Setting::add($MODULE_NAME, "mod_vote_end_time", "Mod vote end time", "noedit", "text", '0');

	//Helpfile
	Help::register($MODULE_NAME, "modvote", "modvote.txt", "all", "How to vote");
	Help::register($MODULE_NAME, "mods", "mods.txt", "all", "General information on !modvote");
?>