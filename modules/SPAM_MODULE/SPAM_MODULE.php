<?php
	$MODULE_NAME = "SPAM_MODULE";
	
	Command::register($MODULE_NAME, "", "spam.php", "spam", "leader", "Spams a message to Linknet");
	CommandAlias::register($MODULE_NAME, "spam", "linknet");
	
	Setting::add($MODULE_NAME, "otspambot", "Omni news relay bot", "noedit", "text", "Linknet", "", "", "admin");
	
	Help::register($MODULE_NAME, "spam", "spam.txt", "all", "Spam commands");
	
?>