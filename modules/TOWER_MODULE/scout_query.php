<?php

	$sql = "SELECT * FROM scout_info s
		JOIN playfields p ON (s.playfield_id = p.id)
		WHERE s.is_current = 0 OR s.scouted_by='Warbot' 
		ORDER BY p.long_name ASC, s.site_number ASC";
	
	$db->query($sql);
	if ($db->numrows() == 0)
		return;

	$notify = false;
	$playfields = array();
	while (($row = $db->fObject()) !== null) {
		$playfields[strtolower($row->long_name)]=$row->playfield_id;
		if($row->is_current==0) $notify=true;
	}
	
	$factions = array("omni","clan","neut");
	
	if(!isset($chatBot->data["SCOUT_QUERY"])){
		$chatBot->data["SCOUT_QUERY"]=0;
	}
	
	$url = "http://towers:towersarenice@88.198.16.250/towers/index.py?q=" . $factions[($chatBot->data["SCOUT_QUERY"]++)%3];
	$html = file_get_contents($url);

	$towers = array();

	$table = parseTable($html);

	foreach($table as $row){
		if(isset($playfields[$row["zone"]])){
			if (preg_match("/^([\d]+)% \(([\d]+):([\d]+):([\d]+) to ([\d]+)%\)$/i", $row["hot"], $arr)){
				
				$now = intval(date("G"))*3600+intval(date("i"))*60+intval(date("s"));
				$togo = intval($arr[2])*3600+intval($arr[3])*60+intval($arr[4]);
				
				if($arr[1]=="75"){
					$close=$now+$togo+3600*6;
				} else if($arr[1]=="25"){
					$close=$now+$togo+3600;
				} else if($arr[1]=="5"){
					$close=$now+$togo;
				}
				
		
				$towers[$playfields[$row["zone"]]][$row["site"]]["close"]=$close;
				$towers[$playfields[$row["zone"]]][$row["site"]]["ql"]=$row["ctlevel"];
				$towers[$playfields[$row["zone"]]][$row["site"]]["faction"]=$row["side"];
				$towers[$playfields[$row["zone"]]][$row["site"]]["org"]=$row["org"];
			}
		}
	}
	
	if(empty($towers)) return;
	
	$i=0;
	foreach($towers as $playfield_id=>$entries)
		foreach($entries as $site=>$info) {
			Towers::add_scout_site($playfield_id, $site, $info["close"], $info["ql"], $info["faction"], $info["org"], "Warbot", true);
			$i++;
		}
	
//	if($notify)	$chatBot->send("Updated {$i} tower sites",'priv');
?> 
