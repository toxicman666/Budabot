<?php
	$MODULE_NAME = "CONFIG";

	//Commands
	Command::activate("msg", "$MODULE_NAME/cmdcfg.php", "config", "admin");
//	Command::activate("guild", "$MODULE_NAME/cmdcfg.php", "config", "mod");
//	Command::activate("priv", "$MODULE_NAME/cmdcfg.php", "config", "mod");

	Command::activate("msg", "$MODULE_NAME/searchcmd.php", "searchcmd", "admin");
//	Command::activate("guild", "$MODULE_NAME/searchcmd.php", "searchcmd", "mod");
//	Command::activate("priv", "$MODULE_NAME/searchcmd.php", "searchcmd", "mod");
	
	Command::activate("msg", "$MODULE_NAME/addalias.php", "addalias", "admin");
//	Command::activate("guild", "$MODULE_NAME/addalias.php", "addalias", "mod");
//	Command::activate("priv", "$MODULE_NAME/addalias.php", "addalias", "mod");
	
	Command::activate("msg", "$MODULE_NAME/remalias.php", "remalias", "admin");
//	Command::activate("guild", "$MODULE_NAME/remalias.php", "remalias", "mod");
//	Command::activate("priv", "$MODULE_NAME/remalias.php", "remalias", "mod");

	//Help Files
	Help::register($MODULE_NAME, "config", "config.txt", "admin", "Configure Commands/Events of the Bot");
?>