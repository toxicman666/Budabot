<?php 
	$MODULE_NAME = "HELP";

	//Commands
	Command::activate("msg", "$MODULE_NAME/general_help.php", "about");
	Command::activate("priv", "$MODULE_NAME/general_help.php", "about");
	Command::activate("msg", "$MODULE_NAME/general_help.php", "help");
	Command::activate("priv", "$MODULE_NAME/general_help.php", "help");
	
	//Help Files
//	Help::register($MODULE_NAME, "attend", "attend.txt", "all", "Attending Battle");
//	Help::register($MODULE_NAME, "lead", "lead.txt", "all", "Leading Battle");	
?>