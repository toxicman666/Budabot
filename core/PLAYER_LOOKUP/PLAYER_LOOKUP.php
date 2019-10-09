<?php
	$MODULE_NAME = "PLAYER_LOOKUP";
	
	require_once 'Player.class.php';
	require_once 'Guild.class.php';
	
	if ($db->get_type() == 'Mysql') {
//		DB::loadSQLFile($MODULE_NAME, 'players_mysql');
	} else if ($db->get_type() == 'Sqlite') {
		DB::loadSQLFile($MODULE_NAME, 'players_sqlite');
	}
	
	Setting::add($MODULE_NAME, "use_players_db", "use players db (self)", "noedit", "text", 'warbot');
	
?>