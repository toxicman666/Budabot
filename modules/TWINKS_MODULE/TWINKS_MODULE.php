<?php

	$MODULE_NAME = "TWINKS_MODULE";
	
	DB::loadSQLFile($MODULE_NAME, "twinks");
	
	Command::register($MODULE_NAME, "", "twinks.php", "twinks", "all", "See twinks online");
	Command::register($MODULE_NAME, "", "manage_twinks.php", "iamtwink", "all", "Register as a twink");
	Command::register($MODULE_NAME, "", "manage_twinks.php", "twink", "all", "Register as a twink");
//	CommandAlias::register($MODULE_NAME, "twinks", "twink");	
	
	Event::register($MODULE_NAME, "logOn", "twinks.php", "none", "Gets online status of twinks");
	Event::register($MODULE_NAME, "logOff", "twinks.php", "none", "Gets offline status of twinks");
	
	//Helpfile
	Help::register($MODULE_NAME, "twinks", "twinks.txt", "all", "How to use twinks module");

?>