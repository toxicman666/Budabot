<?php

if ($message && !preg_match("/^clans all$/i", $message) && !preg_match("/^clans$/i", $message) && !preg_match("/^clans end$/i", $message)) {
	$syntax_error = true;
	return;
}

$end = false;
if (preg_match("/^clans end$/i", $message)) {
	if(isset($chatBot->data["CLANS_MODULE"])){
		$chatBot->data["CLANS_MODULE"]["sendto"] = $sendto;
		$end = true;
	} else {
		$chatBot->send("No checks in progress. Use '!clans'",$sendto);
		return;
	}
} else if (preg_match("/^clans$/i", $message) || preg_match("/^clans all$/i", $message)) {
	// Check if we are already doing a list.
	if (isset($chatBot->data["CLANS_MODULE"])) {
		$msg = "Already doing a list. Use: !clans end";
		$chatBot->send($msg, $sendto);
		return;
	}
	if(preg_match("/^clans all$/i", $message))		
		$chatBot->data["CLANS_MODULE"]["show_all"] = 1;

	$chatBot->data["CLANS_MODULE"]["sendto"] = $sendto;
	$chatBot->data["CLANS_MODULE"]["result"] = array();
	$chatBot->data["CLANS_MODULE"]["check"] = array();
	$chatBot->data["CLANS_MODULE"]["added"] = array();
	
	$db = DB::get_instance();	
	$sql = "SELECT * FROM clans;";
	$db->query($sql);
	
	$clans_read = array();
	while(($row = $db->fObject())!==NULL){
		unset($item);
		$item->name = $row->name;
		$item->main = $row->main;
		$clans_read[]  = $item;
	}
	$chatBot->data["CLANS_MODULE"]["read"]=count($clans_read);
	// Check each name if they are already on the buddylist (and get online status now)
	// Or make note of the name so we can add it to the buddylist later.
	foreach ($clans_read as $player) {
		$chatBot->data["CLANS_MODULE"]["result"][$player->name]["name"] = $player->name;
		$buddy_online_status = Buddylist::is_online($player->name);
		if ($buddy_online_status !== null) {
			$chatBot->data["CLANS_MODULE"]["result"][$player->name]["online"] = $buddy_online_status;
		} else { 
			if ($chatBot->get_uid($player->name)) {
				$chatBot->data["CLANS_MODULE"]["check"][$player->name] = 1;
			}
		}
		if(!empty($player->main)){
			$chatBot->data["CLANS_MODULE"]["result"][$player->name]["main"] = $player->main;
			if(!isset($chatBot->data["CLANS_MODULE"]["result"][$player->main])){
				$chatBot->data["CLANS_MODULE"]["result"][$player->main]["name"] = $player->main;
				$buddy_online_status = Buddylist::is_online($player->main);
				if ($buddy_online_status !== null) {
					$chatBot->data["CLANS_MODULE"]["result"][$player->main]["online"] = $buddy_online_status;
				} else { 
					if ($chatBot->get_uid($player->main)) {
						$chatBot->data["CLANS_MODULE"]["check"][$player->main] = 1;
					}
				}			
			}
		}
	}
	// prime the list and get things rolling by adding some buddies
	$i = 0;
	foreach ($chatBot->data["CLANS_MODULE"]["check"] as $name => $value) {
		
		if(Buddylist::add($name, 'online_clans'))
			$chatBot->data["CLANS_MODULE"]["added"][$name] = 1;
		else Clans::rem($name);
		unset($chatBot->data["CLANS_MODULE"]["check"][$name]);
		if (++$i == 50) {
			break;
		}
	}
	
	$chatBot->send("Checking online status for clans...", $sendto);
	
} else if (($type == "logOn" || $type == "logOff") && isset($chatBot->data["CLANS_MODULE"]["added"][$sender])) {

	if ($type == "logOn") {
		$chatBot->data["CLANS_MODULE"]["result"][$sender]["online"] = 1;
	} else if ($type == "logOff") {
		$chatBot->data["CLANS_MODULE"]["result"][$sender]["online"] = 0;
	}

	Buddylist::remove($sender, 'online_clans');
	unset($chatBot->data["CLANS_MODULE"]["added"][$sender]);
	
	forEach ($chatBot->data["CLANS_MODULE"]["check"] as $name => $value) {
		if(Buddylist::add($name, 'online_clans'))
			$chatBot->data["CLANS_MODULE"]["added"][$name] = 1;
		else Clans::rem($name);
		unset($chatBot->data["CLANS_MODULE"]["check"][$name]);
		break;
	}
}

if (isset($chatBot->data["CLANS_MODULE"]) && count($chatBot->data["CLANS_MODULE"]["check"]) == 0 && count($chatBot->data["CLANS_MODULE"]["added"]) == 0  || $end) {

	$clans=array();
	$mains=array();
	$ontwink=0;
	$total=0;
	// for warnings:
	$tl=0;
	$max=0;
	$warn_count = Setting::get('warn_clans');
	
	foreach($chatBot->data["CLANS_MODULE"]["result"] as $person){
		unset ($clan);
		unset ($main);
		$name=$person["name"];
		$main=$person["main"];
				
		$clan->name=$name;
		if($main!=null) $clan->main=$main;
		else $clan->main="";
		
		$whois=Player::get_by_name($name);
		$clan->prof=$whois->profession;
		$clan->lvl=$whois->level;
		$clan->status=0;
		$title_lvl = Clans::get_tl($whois->level);
		if ($person["online"]==1) {
			$clan->status+=1;
			if($title_lvl!=7) $ontwink++;	
		}
		if ($main!=null){
			if($chatBot->data["CLANS_MODULE"]["result"][$main]["online"]==1){
				$clan->status+=2;
				if(!in_array($main,$mains)) $mains[]=$main;
			}
		}
		if ($clan->status>0||isset($chatBot->data["CLANS_MODULE"]["show_all"])){
			$clans[$title_lvl][]=$clan; // sort by title lvl
			$total++;
		}
	}
	
	// check if we need to show warning
	if(isset($chatBot->data["CLANS_MODULE"]["warning"])){
		$count=array();
		for($i=1; $i<=6; $i++){ // don't check tl7
			foreach($clans[$i] as $c){
				if ($c->status==1||$c->status==3) $count[$i]++;
			}
			if($max<$count[$i]){
				$max = $count[$i];
				$tl = $i;
			}
		}
		if($max<$warn_count){
			// no need to display anything, abort
			forEach ($chatBot->data["CLANS_MODULE"]["added"] as $name => $value) {
				Buddylist::remove($name, 'online_clans');
			}
			unset($chatBot->data["CLANS_MODULE"]);
			return;
		}
	}
	
	$blob = "::::::<orange>CLANS<end>::::::\n";
	for ($i=1; $i<=7; $i++){
		if(count($clans[$i])>0){
			$miniblob="";
			$off_miniblob="";
			$minicount=0;
			foreach($clans[$i] as $clan){
				if($clan->status==1){
					$miniblob .= "<green>{$clan->name}<end>";
					if($clan->main!="") $miniblob .= " (<red>{$clan->main}<end>) ";
					$miniblob .= " - {$clan->lvl} {$clan->prof}\n";
					$minicount++;
				} else if($clan->status==2){
					$off_miniblob .= "<red>{$clan->name}<end> (<green>{$clan->main}<end>) ";
					$off_miniblob .= " - {$clan->lvl} {$clan->prof}\n";
				} else if($clan->status==3){
					$miniblob .= "<green>{$clan->name} ({$clan->main})<end> ";
					$miniblob .= " - {$clan->lvl} {$clan->prof}\n";
					$minicount++;
				} else {
					$off_miniblob .= "<red>{$clan->name}<end>";
					if($clan->main!="") $off_miniblob .= " (<red>{$clan->main}<end>) ";
					$off_miniblob .= " - {$clan->lvl} {$clan->prof}\n";
				}
			}
			$blob .= "\n::tl{$i} (" . $minicount . "/" . count($clans[$i]) . "):\n" . $miniblob . $off_miniblob;
		}
	}
	if(!isset($chatBot->data["CLANS_MODULE"]["show_all"])){
		if(($ontwink+count($mains))>0){
		/*	if(count($mains)>0){
				$blob .= "\n::::::<orange>MAINS<end>::::::\n";
				foreach($mains as $m){
					$whois=Player::get_by_name($m);
					$blob .= "\n<green>{$m}<end> - {$whois->level} {$whois->profession}";
				}
			} */
			$msg = Text::make_link("{$ontwink} clan twinks online, " . count($mains) . " on mains",$blob,'blob');
			if(isset($chatBot->data["CLANS_MODULE"]["warning"])){
				$msg = "<orange>Warning: " . $msg . " :: {$max} on tl{$tl}<end>";
			}
		} else $msg = "No clan twinks online.";
	} else {
		$msg = Text::make_link("{$total} clan twinks listed",$blob,'blob');
	}
	
	if(isset($chatBot->data["CLANS_MODULE"]["sendto"])){
		$chatBot->send($msg,$chatBot->data["CLANS_MODULE"]["sendto"]);
	}
	// in case it was ended early
	forEach ($chatBot->data["CLANS_MODULE"]["added"] as $name => $value) {
		Buddylist::remove($name, 'online_clans');
	}
	unset($chatBot->data["CLANS_MODULE"]);
}

?>
