<?php
	require_once 'planters_functions.php';

	$MODULE_NAME = "PLANTERS_MODULE";
	
	DB::loadSQLFile($MODULE_NAME, "planters");
	
	Command::register($MODULE_NAME, "", "planters.php", "planters", "all", "See available planters");
	Command::register($MODULE_NAME, "", "towertypes.php", "towertypes", "all", "See tower types by lvl");	
	Command::register($MODULE_NAME, "", "manage_planters.php", "iamplanter", "all", "Register as planter");
	Command::register($MODULE_NAME, "", "manage_planters.php", "remplanter", "mod", "Register as planter");
	CommandAlias::register($MODULE_NAME, "planters", "planter");	
	
	Event::register($MODULE_NAME, "logOn", "planters.php", "none", "Gets online status of planters");
	Event::register($MODULE_NAME, "logOff", "planters.php", "none", "Gets offline status of planters");
	
	//Helpfile
	Help::register($MODULE_NAME, "planters", "planters.txt", "all", "How to check available planters");

?>