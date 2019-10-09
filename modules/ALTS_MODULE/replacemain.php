<?php

if (preg_match("/^replacemain (.+) (.+)$/i", $message, $names)) {
	if ($names[1] == '' || $names[2] == '') {
		$syntax_error = true;
		return;
	}

	$name_main = ucfirst(strtolower($names[1]));
	$name_newmain = ucfirst(strtolower($names[2]));
	$name_main = Alts::get_main($name_main);

	if(Alts::is_wc_member($name_main)){
		$chatBot->send("<orange>Cannot modify main for WC members, remove from WC first.<end>",$sender);
		return;
	}
	
	$alts = Alts::get_alts($name_main);
	if (!in_array($name_newmain, $alts)){
		$chatBot->send("<highlight>{$name_newmain}<end> is not <highlight>{$name_main}<end>'s alt",$sendto);
		return;
	}
	
	$db = DB::get_instance();
	// remove new main from alts
	$db->exec("DELETE FROM alts WHERE alt='{$name_newmain}'");
	// replace main in alts
	$db->exec("UPDATE alts SET main='{$name_newmain}' WHERE main='{$name_main}';");
	// add old main to alts
	$db->exec("INSERT INTO alts (`alt`,`main`) VALUES ('{$name_main}','{$name_newmain}');");
	
	$chatBot->send("<highlight>{$name_main}<end>'s main changed to <highlight>{$name_newmain}<end>.", $sendto);
	
} else {
	$syntax_error = true;
}

?>