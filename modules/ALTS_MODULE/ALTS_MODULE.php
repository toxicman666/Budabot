<?php
	require_once 'Alts.class.php';

	$MODULE_NAME = "ALTS_MODULE";
	
	DB::loadSQLFile($MODULE_NAME, "alts");
	
	// Alternative Characters
	Command::register($MODULE_NAME, "", "alts.php", "alts", "all", "Alt Char handling");

	Setting::add($MODULE_NAME, "alts_wc_members", "Autoinvite WC members alts", "edit", "options", "0", "off;on", '0;1', "admin");
	Setting::add($MODULE_NAME, "max_wc_alts", "Maximum possible alts for WC", "edit", "options", "4", "3;4;5;6;7;8", '3;4;5;6;7;8', "admin");		
	Command::register($MODULE_NAME, "", "altsadmin.php", "altsadmin", "mod", "Alt Char handling (admin)");
	Command::register($MODULE_NAME, "", "replacemain.php", "replacemain", "admin", "Replace characters main");	
	
	//Helpfile
	Help::register($MODULE_NAME, "alts", "alts.txt", "all", "How to set alts");
	Help::register($MODULE_NAME, "altsadmin", "altsadmin.txt", "mod", "How to set alts (admins)");
?>