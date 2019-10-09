<?php
	require_once 'Server_status.class.php';
	
	$MODULE_NAME = "SERVER_STATUS_MODULE";

	
	//Server Status
	Command::register($MODULE_NAME, "", "server.php", "server", "all", "Shows the Server status");	
	Setting::add($MODULE_NAME, "warn_percent", "Minimum percentage for warning", "edit", "text", '2.2');
	Setting::add($MODULE_NAME, "server_tl7", "Show tl7 tower zones first", "edit", "options", "1", "off;on", '0;1', "admin");
	
	//Help files
    Help::register($MODULE_NAME, "server", "server.txt", "guild", "Show the server status");
?>