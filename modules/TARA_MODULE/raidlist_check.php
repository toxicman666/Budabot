<?php

if (isset($chatBot->data["TARA_MODULE"]["raidlist"]) && isset($chatBot->data["TARA_MODULE"]["raid_status"])){
	if (count($chatBot->data["TARA_MODULE"]["raidlist"])>0 && $chatBot->data["TARA_MODULE"]["raid_status"]!=0){
		$timenow = time();
		foreach($chatBot->data["TARA_MODULE"]["raidlist"] as $name=>$time){
			$passed = $timenow - $time;
			if ($passed<121 && $passed>=119){
				$chatBot->send("You have been out of bot for 2 minutes. You have 1 minute to rejoin the bot, otherwise you will be removed from raidlist.",$name);
			} else if ($passed>=179){
				unset($chatBot->data["TARA_MODULE"]["raidlist"][$name]);
				$db->exec("DELETE FROM tara_raidlist WHERE name='{$name}';");
				$chatBot->send("You have been <yellow>removed from raidlist<end>, because you were out of bot for longer than 3 minutes.",$name);
				$chatBot->send("<yellow>{$name} has been auto removed from raidlist<end> (out of bot for 3 minutes)",'priv');
			}
		}
	}
} else {
	$db->query("SELECT * FROM tara_raidlist WHERE leave_time>0;");
	$chatBot->data["TARA_MODULE"]["raidlist"]=array();
	if ($db->numrows()>0){
		while ($row=$db->fObject()){
			$chatBot->data["TARA_MODULE"]["raidlist"][$row->name] = $row->leave_time;
		}
	}
	
	$chatBot->data["TARA_MODULE"]["raid_status"] = Setting::get("raid_status");
}

?>