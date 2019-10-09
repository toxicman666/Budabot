<?php

if ($message && !preg_match("/^planters (.+)$/i", $message) && !preg_match("/^planters$/i", $message)) {
	$syntax_error = true;
	return;
}

$end = false;
if (preg_match("/^planters end$/i", $message)) {
	if(isset($chatBot->data["PLANTERS_MODULE"])){
		$chatBot->data["PLANTERS_MODULE"]["sendto"] = $sendto;
		$end = true;
	} else {
		$chatBot->send("No checks in progress. Use '!planters'",$sendto);
		return;
	}
} else if (preg_match("/^planters$/i", $message) || preg_match("/^planters ([0-9a-z]+) ([0-9]+)$/i", $message, $arr)) {
	// Check if we are already doing a list.
	if (isset($chatBot->data["PLANTERS_MODULE"])) {
		$msg = "Already doing a list. Use: !planters end";
		$chatBot->send($msg, $sendto);
		return;
	}
	if($arr[1]){
		$playfield_name = strtoupper($arr[1]);
		$playfield = Playfields::get_playfield_by_name($playfield_name);
		if ($playfield === null) {
			$msg = "Playfield '$playfield_name' could not be found";
			$chatBot->send($msg, $sendto);
			return;
		}

		$site_number = $arr[2];
		$sql = "SELECT * FROM tower_site t1
			JOIN tower_info t2 ON (t1.playfield_id = t2.playfield_id AND t1.site_number = t2.site_number)
			JOIN playfields p ON (t1.playfield_id = p.id)
			WHERE t1.playfield_id = $playfield->id AND t1.site_number = $site_number";
		$db->query($sql);
		if ($db->numrows() == 0) {
			$msg = "Invalid site number.";
			$chatBot->send($msg,$sendto);
			return;
		}
		$row = $db->fObject();
		$min = planters_getTowerType($row->min_ql);
		$max = planters_getTowerType($row->max_ql);

		for ($i=$min;$i<=$max;$i++)
			$chatBot->data["PLANTERS_MODULE"]["tower_type"][]=$i;
			
		$chatBot->data["PLANTERS_MODULE"]["title"] = "Available Planters for {$arr[1]} {$arr[2]}";
	} else {
		$chatBot->data["PLANTERS_MODULE"]["title"] = "Available Planters";
	}
	
	$sql = "SELECT name FROM planters;";
	$db = DB::get_instance();	
	$db->query($sql);
	if ($db->numrows() === 0) {
		$chatBot->send("No planters in database.",$sendto);
		return;
	}

	$players_read = array();
	while(($row = $db->fObject())!==NULL){
		$players_read[]  = $row->name;
	}
	$players = array();
	foreach($players_read as $player){
	//	$players[] = $player;
		$main = Alts::get_main($player);
		$alts = Alts::get_alts($main);
		$players[] = $main;
		foreach ($alts as $alt){
			$players[] = $alt;
		}
	
	}
	$chatBot->data["PLANTERS_MODULE"]["sendto"] = $sendto;
	$chatBot->data["PLANTERS_MODULE"]["result"] = array();
	$chatBot->data["PLANTERS_MODULE"]["check"] = array();
	$chatBot->data["PLANTERS_MODULE"]["added"] = array();

	// Check each name if they are already on the buddylist (and get online status now)
	// Or make note of the name so we can add it to the buddylist later.
	foreach ($players as $player) {

		
		$chatBot->data["PLANTERS_MODULE"]["result"][$player]["name"] = $player;

		$buddy_online_status = Buddylist::is_online($player);
		if ($buddy_online_status !== null) {
			$chatBot->data["PLANTERS_MODULE"]["result"][$player]["online"] = $buddy_online_status;
		} else { 
			if ($chatBot->get_uid($player)) {
				$chatBot->data["PLANTERS_MODULE"]["check"][$player] = 1;
			}
		}
	}
	
//	$asd = print_r($chatBot->data["PLANTERS_MODULE"]["check"],true);
//	$chatBot->send($asd,$sendto);

	// prime the list and get things rolling by adding some buddies
	$i = 0;
	foreach ($chatBot->data["PLANTERS_MODULE"]["check"] as $name => $value) {
		$chatBot->data["PLANTERS_MODULE"]["added"][$name] = 1;
		unset($chatBot->data["PLANTERS_MODULE"]["check"][$name]);
		Buddylist::add($name, 'online_planters');
		if (++$i == 10) {
			break;
		}
	}
	
	
	$chatBot->send("Checking online status for planters...", $sendto);
	
} else if (($type == "logOn" || $type == "logOff") && isset($chatBot->data["PLANTERS_MODULE"]["added"][$sender])) {

	if ($type == "logOn") {
		$chatBot->data["PLANTERS_MODULE"]["result"][$sender]["online"] = 1;
	} else if ($type == "logOff") {
		$chatBot->data["PLANTERS_MODULE"]["result"][$sender]["online"] = 0;
	}

	Buddylist::remove($sender, 'online_planters');
	unset($chatBot->data["PLANTERS_MODULE"]["added"][$sender]);
	
	forEach ($chatBot->data["PLANTERS_MODULE"]["check"] as $name => $value) {
		$chatBot->data["PLANTERS_MODULE"]["added"][$name] = 1;
		unset($chatBot->data["PLANTERS_MODULE"]["check"][$name]);
		Buddylist::add($name, 'online_planters');
		break;
	}
}

if (isset($chatBot->data["PLANTERS_MODULE"]) && count($chatBot->data["PLANTERS_MODULE"]["added"]) == 0 && count($chatBot->data["PLANTERS_MODULE"]["check"]) == 0 || $end) {
	$planters_orgs = array();
	$planters_no_org = array();
	foreach ($chatBot->data["PLANTERS_MODULE"]["result"] as $planter){
		if ($planter['online']==1){
			$whois=Player::get_by_name($planter['name']);
			if($whois->guild != "")
				$planters_orgs[$whois->guild][]=$planter['name'];
			else
				$planters_no_org[]=$planter['name'];
		}	
	}
	
	$blob = "";
	foreach ($planters_orgs as $org_name => $org_arr){
		$org = str_replace("'", "''", $org_name);
		$sql = "SELECT * FROM tower_site t1
		JOIN scout_info s ON (t1.playfield_id = s.playfield_id AND t1.site_number = s.site_number)
		JOIN tower_info t2 ON (t1.playfield_id = t2.playfield_id AND t1.site_number = t2.site_number)
		JOIN playfields p ON (t1.playfield_id = p.id)
		WHERE s.guild_name LIKE '$org'";
		$db->query($sql);
		if ($db->numrows()>4) continue;
		$can_plant = "";
		if($db->numrows()>0){
			$have = array();
			while($row = $db->fObject()){
				$have[] = planters_getTowerType($row->ct_ql);
			}
			if (isset($chatBot->data["PLANTERS_MODULE"]["tower_type"])){
				foreach ($chatBot->data["PLANTERS_MODULE"]["tower_type"] as $tt){
					if (!in_array($tt,$have)) $can_plant .= " " . planters_getItalicType($tt);
				}
				if($can_plant=="") continue;
			} else {
				for ($i=1;$i<=7;$i++){
					if(!in_array($i,$have)) $can_plant .= " " . planters_getItalicType($i);
				}
			}
		}
		$blob .= "\n\n<green>{$org_name}<end> (";
		$blob .= Text::make_link("lc","/tell <myname> lc org {$org_name}",'chatcmd');
		$blob .= "):\n<highlight>can plant " . Text::make_link("type(s)","/tell <myname> !towertypes",'chatcmd') . "<end>:";
		if ($can_plant=="") $blob .= " any\n";
		else $blob .= "{$can_plant}\n";
		$blob .= "Planter(s):";
		foreach ($org_arr as $member){
			$blob .= " " . Text::make_link($member,"/tell {$member}",'chatcmd');
		}
	}
	
	if (count($planters_no_org)>0){
		$blob .= "\n\n<orange>Other planters<end>:\n";
		foreach ($planters_no_org as $planter){
			$blob .= "{$planter} ";
		}
	}
	
	$to = $chatBot->data["PLANTERS_MODULE"]["sendto"];
	
	if ($blob != ""){
		$blob = $chatBot->data["PLANTERS_MODULE"]["title"] . $blob;
		$msg = Text::make_link($chatBot->data["PLANTERS_MODULE"]["title"],$blob,'blob');
		$chatBot->send($msg,$to);
	} else {
		$chatBot->send("No planters available.",$to);
	}
		
	// in case it was ended early
	forEach ($chatBot->data["PLANTERS_MODULE"]["added"] as $name => $value) {
		Buddylist::remove($name, 'online_planters');
	}
	unset($chatBot->data["PLANTERS_MODULE"]);
}

?>
