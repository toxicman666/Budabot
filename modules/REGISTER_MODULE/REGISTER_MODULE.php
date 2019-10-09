<?php

	$MODULE_NAME = "REGISTER_MODULE";

	Command::register($MODULE_NAME, "", "confirm.php", "confirm", "all", "Confirms an account name on omnihq.net");
	Command::register($MODULE_NAME, "", "update.php", "update", "all", "Updates the access level on omnihq.net");	
	Subcommand::register($MODULE_NAME, "", "update.php", "update all", "admin", "update", "Updates all users");	
	Help::register($MODULE_NAME, "confirm", "confirm.txt", "all", "How to confirm your forum account");
	Help::register($MODULE_NAME, "update", "confirm.txt", "all", "How to update your forum account");
	
?>