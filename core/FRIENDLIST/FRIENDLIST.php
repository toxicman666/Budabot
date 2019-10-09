<?php
	$MODULE_NAME = "FRIENDLIST";

	Command::activate("msg", "$MODULE_NAME/friendlist_cmd.php", "friendlist", "admin");
//	Command::activate("priv", "$MODULE_NAME/friendlist_cmd.php", "friendlist", "admin");
//	Command::activate("guild", "$MODULE_NAME/friendlist_cmd.php", "friendlist", "admin");
	
	Command::activate("msg", "$MODULE_NAME/rembuddy.php", "rembuddy", "admin");
//	Command::activate("priv", "$MODULE_NAME/rembuddy.php", "rembuddy", "admin");
//	Command::activate("guild", "$MODULE_NAME/rembuddy.php", "rembuddy", "admin");
	
	Command::activate("msg", "$MODULE_NAME/addbuddy.php", "addbuddy", "admin");
//	Command::activate("priv", "$MODULE_NAME/addbuddy.php", "addbuddy", "admin");
//	Command::activate("guild", "$MODULE_NAME/addbuddy.php", "addbuddy", "admin");
	
	// Help files
	Help::register($MODULE_NAME, "friendlist", "friendlist.txt", "admin", "Commands for viewing and manually changing the friend list");
?>