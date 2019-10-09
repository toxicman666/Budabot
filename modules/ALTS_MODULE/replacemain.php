<?php

if (preg_match("/^replacemain (.+) (.+)$/i", $message, $names)) {
	if ($names[1] == '' || $names[2] == '') {
		$syntax_error = true;
		return;
	}

	$name_main = ucfirst(strtolower($names[1]));
	$name_newmain = ucfirst(strtolower($names[2]));
	$name_main = Alts::get_main($name_main);
	$account = Tara::get_account_name($name_main);
	if ($account != $name_main){
		$chatBot->send("<orange>Error! {$name_main} does not own account {$account}<end>",$sendto);
		return;
	}
	$tara = Alts::get_tara_alts($name_main);
	if (!in_array($name_newmain, $tara)){
		$chatBot->send("<highlight>{$name_newmain}<end> is not confirmed <highlight>{$name_main}<end>'s alt",$sendto);
		return;
	}
	
	$db = DB::get_instance();
	// remove new main from alts
	$db->exec("DELETE FROM alts WHERE alt='{$name_newmain}'");
	// replace main in alts
	$db->exec("UPDATE alts SET main='{$name_newmain}' WHERE main='{$name_main}';");
	// replace account name
	$db->exec("UPDATE tara_points SET name='{$name_newmain}' WHERE name='{$name_main}';");
	// add old main to alts
	$db->exec("INSERT INTO alts (`alt`,`main`,`approved`) VALUES ('{$name_main}','{$name_newmain}',1);");
	// change account name in history
	$db->exec("UPDATE tara_points_history SET account='{$name_newmain}' WHERE account='{$name_main}';");
	
	$chatBot->send("<highlight>{$name_main}<end>'s main changed to <highlight>{$name_newmain}<end>.", $sendto);
	
} else {
	$syntax_error = true;
}

?>