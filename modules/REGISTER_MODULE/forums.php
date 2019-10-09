<?php
$group_id_confirmed = 4;  // confirmed users
$group_id_150 = 7; // 150+
$group_id_tara = 6; // tara
$group_id_205 = 5; // 205+
$group_id_wc = 3; // warleaders
$role_id = 2;

if (preg_match("/^forums$/i", $message)) {
	$whois = Player::get_by_name($sender);
	if ($whois->faction == 'clan') {
		$chatBot->send("<orange>You have your own bot.<end>",$sender);
		return;
	}	
	
  	$main = Alts::get_main($sender);
	$alts = Alts::get_alts($main);
	if(count($alts)>0){
		foreach($alts as $alt){
			if ($alt==$sender) continue;
			$sql = "SELECT ag.id, jo.email FROM omnihqdb.jos_users jo JOIN omnihqdb.jos_agora_users ag ON jo.username = ag.username LEFT JOIN omnihqdb.jos_agora_user_group gr ON gr.user_id=ag.id WHERE jo.name='$alt' AND gr.group_id = $group_id_confirmed;";
			$db->query($sql);
			if ($db->numrows() != 0) {
				$chatBot->send("<orange>Your alt $alt is registered at forums. Aborting<end>",$sender);
				return;
			}
		}
	}
	if($main!=$sender){
		$sql = "SELECT ag.id, jo.email FROM omnihqdb.jos_users jo JOIN omnihqdb.jos_agora_users ag ON jo.username = ag.username LEFT JOIN omnihqdb.jos_agora_user_group gr ON gr.user_id=ag.id WHERE jo.name='$main' AND gr.group_id = $group_id_confirmed;";
		$db->query($sql);
		if ($db->numrows() != 0) {
			$chatBot->send("<orange>Your main $main is registered at forums. Aborting<end>",$sender);
			return;
		}
	}
	
	// warbot bans
	$sql = "SELECT name FROM taratime.banlist_<myname> WHERE name = '$sender';";
	$db->query($sql);
	if ($db->numrows() > 0) {
		$chatBot->send("<orange>You are banned from this network.<end>");
		return;
	}
	$sql = "SELECT ag.id, jo.email, orgname, orgrank FROM omnihqdb.jos_users jo JOIN omnihqdb.jos_agora_users ag ON jo.username = ag.username WHERE jo.name = '$sender';";
	$db->query($sql);
	if($db->numrows()===0){
		$blob = ":::: <yellow>How to register at OmniHQ<end> ::::\n\nIf you are already registered, log the toon you registered with or try " . Text::make_link("updating your info","/tell <myname> update",'chatcmd') . "\n\nGo to " . Text::make_link("www.omnihq.net","/start http://www.omnihq.net/component/user/?task=register",'chatcmd') . " and follow instructions to create account\n<white>(Note that the Display Name has to be exact name of your main character)<end>\n\nActivate account AND log in\n\n" . Text::make_link("Confirm account ingame","/tell <myname> !confirm",'chatcmd') . "\n\n\n<white>Note: if you register multiple accounts and get extra points on registration, then decide to merge accounts, your extra points will be deducted from alt.";
		$msg = "<orange>You are not registered at www.omnihq.net.<end> Forum registration gives you extra 20 points. (" . Text::make_link("How?",$blob,'blob') . ")";
		$chatBot->send($msg,$sender);
		return;
	}
	$row = $db->fObject();
	$ag_id = $row->id;
	
	$sql = "SELECT id, group_id FROM omnihqdb.jos_agora_user_group WHERE user_id = $ag_id AND role_id!=1;";
	$db->query($sql);
	
	$access[$sender]=1;
	// check if the user is confirmed
	if ($db->numrows() == 0) {
		$chatBot->send("Username '$sender' does not have access to any groups.", $sendto);
		return;
	}
	$groups=array();
	while($row=$db->fObject()){
		$groups[]=$row->group_id;
	}
	
	$blob = "<header>:::: {$sender}'s Access Groups ::::<end>\n";
	$blob .= "::" . Text::make_link("refresh", "/tell <myname> !forums", 'chatcmd') . " " . Text::make_link("update","/tell <myname> !update", 'chatcmd') . "::\n\n";
	if (!in_array($group_id_confirmed,$groups)){
		$blob .= "<orange>Your account still not confirmed<end> " . Text::make_link("confirm!", "/tell <myname> !confirm", 'chatcmd');
	} else {
		$blob .= "<white>:<end> Confirmed";
		if (in_array($group_id_150,$groups)) $blob .= "\n<white>:<end> 150+";
		if (in_array($group_id_205,$groups)) $blob .= "\n<white>:<end> 205+";
		if (in_array($group_id_tara,$groups)) $blob .= "\n<white>:<end> Tara";	
	}
	
	$msg = "You are registered at <highlight>www.omnihq.net<end> " . Text::make_link("Access Groups",$blob,'blob');
	$chatBot->send($msg,$sender);
} else {
	$syntax_error = true;
}

?>
