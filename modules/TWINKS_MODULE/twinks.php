<?php

if ($message && !preg_match("/^twinks ([0-9]+) ([0-9]+)$/i", $message) && !preg_match("/^twinks$/i", $message) && !preg_match("/^twinks end$/i", $message)) {
	$syntax_error = true;
	return;
}

$end = false;
if (preg_match("/^twinks end$/i", $message)) {
	if(isset($chatBot->data["TWINKS_MODULE"])){
		$chatBot->data["TWINKS_MODULE"]["sendto"] = $sendto;
		$end = true;
	} else {
		$chatBot->send("No checks in progress. Use '!twinks'",$sendto);
		return;
	}
} else if (preg_match("/^twinks$/i", $message) || preg_match("/^twinks ([0-9]+) ([0-9]+)$/i", $message, $arr)) {
	// Check if we are already doing a list.
	if (isset($chatBot->data["TWINKS_MODULE"])) {
		$msg = "Already doing a list. Use: !twinks end";
		$chatBot->send($msg, $sendto);
		return;
	}
	if(isset($arr[1])){

		if ($arr[2]>174)
			$arr[2]=174;
		if ($arr[1]<1)
			$arr[1]=1;
			
		if($arr[1]>$arr[2]){
			$arr[1]=1;
			$arr[2]=174;
		}

		$chatBot->data["TWINKS_MODULE"]["title"] = "{$arr[1]}-{$arr[2]} Twinks online";
	} else {
		$chatBot->data["TWINKS_MODULE"]["title"] = "Twinks online";
		$arr=array();
		$arr[1]=1;
		$arr[2]=174;
	}
	
	$sql = "SELECT tt.owner,tt.name,p.level,p.profession AS prof,IF(tw.name IS NULL,0,1) AS inbot FROM (SELECT IF(a.main IS NULL,t.name,a.main) AS owner,IF(a.name IS NULL,t.name,a.name) AS name FROM twinks t LEFT JOIN (SELECT alt AS name,main FROM alts UNION SELECT main AS alt,main AS name FROM alts) a ON t.name=a.name OR t.name=a.main) tt LEFT JOIN online_twinkbot tw ON tt.name=tw.name LEFT JOIN players p ON tt.name=p.name HAVING level>={$arr[1]} AND level<={$arr[2]} ORDER BY p.level ASC;";
	$db = DB::get_instance();	
	$db->query($sql);
	$total = $db->numrows();
	if ($total === 0) {
		$chatBot->send("No twinks found for {$arr[1]}-{$arr[2]}.",$sendto);
		unset($chatBot->data["TWINKS_MODULE"]);
		return;
	}

	$twinks_read = $db->fObject('all');
	
	$players = array();
	foreach($twinks_read as $player){
		$alts = Alts::get_alts($player->owner);
		$players[$player->name]["toons"][]=$player->owner;
		foreach ($alts as $alt){
			$players[$player->name]["toons"][]=$alt;
		}
		$players[$player->name]["lvl"] = $player->level;
		$players[$player->name]["prof"] = $player->prof;
		$players[$player->name]["inbot"] = $player->inbot;
	}
	
	$chatBot->data["TWINKS_MODULE"]["sendto"] = $sendto;
	$chatBot->data["TWINKS_MODULE"]["result"] = array();
	$chatBot->data["TWINKS_MODULE"]["check"] = array();
	$chatBot->data["TWINKS_MODULE"]["added"] = array();
	$chatBot->data["TWINKS_MODULE"]["players"] = $players;

	// Check each name if they are already on the buddylist (and get online status now)
	// Or make note of the name so we can add it to the buddylist later.
	foreach ($players as $player){
		foreach($player["toons"] as $name){
			
			$chatBot->data["TWINKS_MODULE"]["result"][$name]["name"] = $name;

			$buddy_online_status = Buddylist::is_online($name);
			if ($buddy_online_status !== null) {
				$chatBot->data["TWINKS_MODULE"]["result"][$name]["online"] = $buddy_online_status;
			} else { 
				if ($chatBot->get_uid($name)) {
					$chatBot->data["TWINKS_MODULE"]["check"][$name] = 1;
				}
			}
		}
	}
	
	// prime the list and get things rolling by adding some buddies
	$i = 0;
	foreach ($chatBot->data["TWINKS_MODULE"]["check"] as $name => $value) {
		unset($chatBot->data["TWINKS_MODULE"]["check"][$name]);
		if(Buddylist::add($name, 'online_twinks'))
			$chatBot->data["TWINKS_MODULE"]["added"][$name] = 1;
		else $db->exec("DELETE FROM twinks WHERE name='{$name}';");
		if (++$i == 50) {
			break;
		}
	}
	
	
	$chatBot->send("Checking online status for twinks ({$total} in range)...", $sendto);
	
} else if (($type == "logOn" || $type == "logOff") && isset($chatBot->data["TWINKS_MODULE"]["added"][$sender])) {

	if ($type == "logOn") {
		$chatBot->data["TWINKS_MODULE"]["result"][$sender]["online"] = 1;
	} else if ($type == "logOff") {
		$chatBot->data["TWINKS_MODULE"]["result"][$sender]["online"] = 0;
	}

	Buddylist::remove($sender, 'online_twinks');
	unset($chatBot->data["TWINKS_MODULE"]["added"][$sender]);
	
	forEach ($chatBot->data["TWINKS_MODULE"]["check"] as $name => $value) {
		
		unset($chatBot->data["TWINKS_MODULE"]["check"][$name]);
		if(Buddylist::add($name, 'online_twinks'))
			$chatBot->data["TWINKS_MODULE"]["added"][$name] = 1;
		else $db->exec("DELETE FROM twinks WHERE name='{$name}';");
		break;
	}
}

if (isset($chatBot->data["TWINKS_MODULE"]) && count($chatBot->data["TWINKS_MODULE"]["added"]) == 0 && count($chatBot->data["TWINKS_MODULE"]["check"]) == 0 || $end) {

	if(count($chatBot->data["TWINKS_MODULE"]["players"])===0){
		$chatBot->send("Error displaying twinks.",$sendto);
		unset($chatBot->data["TWINKS_MODULE"]);
		return;
	}


	$blob = "";
	$total = 0;
	foreach($chatBot->data["TWINKS_MODULE"]["players"] as $name=>$player){
		unset($online);
		$online = array();
		foreach($player["toons"] as $toon){
			if($chatBot->data["TWINKS_MODULE"]["result"][$toon]["online"]==1) $online[]=$toon;
		}
		if(count($online)>0){
			$blob .= "\n" . ($player["inbot"]==1?"<green>":(in_array($name,$online)?"<a href='chatcmd:///tell twinkbot !invite {$name}'>":"<white>")) . "{$name}" . (($player["inbot"]!=1 && in_array($name,$online))?"</a>":"<end>") . " <highlight>- {$player["lvl"]} {$player["prof"]} -<end> <green>on<end>:";
			foreach($online as $n) $blob .= " " . Text::make_link($n,"/tell {$n}",'chatcmd');
			$total++;
		}
	}
	$blob = "<header>:::: Twinks online ({$total}) ::::<end>\n" . $blob;
	$blob .= "\n\n(<green>Green<end> name = in Twinkbot)";
	$blob .= "\n(<blue><u>Blue</u><end> name = invite to Twinkbot)";
	
	$to = $chatBot->data["TWINKS_MODULE"]["sendto"];
	
	if ($total>0){
		$msg = Text::make_link($chatBot->data["TWINKS_MODULE"]["title"] . " ({$total})",$blob,'blob');
		$chatBot->send($msg,$to);
	} else {
		$chatBot->send("No " . $chatBot->data["TWINKS_MODULE"]["title"] . " ({$total})",$to);
	}
		
	// in case it was ended early
	forEach ($chatBot->data["TWINKS_MODULE"]["added"] as $name => $value) {
		Buddylist::remove($name, 'online_twinks');
	}
	unset($chatBot->data["TWINKS_MODULE"]);
}

?>
