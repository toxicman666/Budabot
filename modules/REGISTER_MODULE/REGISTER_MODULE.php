<?php

	$MODULE_NAME = "REGISTER_MODULE";

	Command::register($MODULE_NAME, "", "confirm.php", "confirm", "all", "Confirms an account name on omnihq.net");
	Command::register($MODULE_NAME, "", "register.php", "register", "all", "Register character to bot");	
	Command::register($MODULE_NAME, "", "update.php", "update", "all", "Updates the access level on omnihq.net");
	Subcommand::register($MODULE_NAME, "", "update.php", "update all", "admin", "update", "Updates all users");	
	Command::register($MODULE_NAME, "", "rules.php", "rules", "all", "Displays rules");
	Command::register($MODULE_NAME, "", "forums.php", "forums", "all", "Displays your forum status");
	CommandAlias::register($MODULE_NAME, "forums", "forum");	

	
	Help::register($MODULE_NAME, "rules", "register_help.txt", "all", "Register help");	
	Help::register($MODULE_NAME, "forums", "register_help.txt", "all", "Register help");	
	Help::register($MODULE_NAME, "register", "register.txt", "all", "How to register your character");
	Help::register($MODULE_NAME, "confirm", "confirm.txt", "all", "How to confirm your forum account");
	Help::register($MODULE_NAME, "update", "confirm.txt", "all", "How to update your forum account");
	
?>