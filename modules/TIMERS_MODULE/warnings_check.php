<?php
   
//Check clan twinks 

if (Setting::get('check_clan_twinks')==1){
		// Check if we are already doing a list.
		if (isset($chatBot->data["CLANS_MODULE"])) {
			return;
		}

		$chatBot->data["CLANS_MODULE"]["sendto"] = 'priv';
		$chatBot->data["CLANS_MODULE"]["warning"] = 1;
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
	//	$chatBot->data["CLANS_MODULE"]["read"]=count($clans_read);
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
}

//Check tl7 zones
if (Setting::get('check_tl7_percent')==1){
	$msg = Server_status::check_tl7();
	if($msg) $chatBot->send($msg,'priv');
}
 
?>