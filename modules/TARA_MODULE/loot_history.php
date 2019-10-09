<?php

if (preg_match("/^loothistory$/i", $message)) {
	$db = DB::get_instance();

	$itemrow=$db->fObject();
	$db->query("SELECT * FROM tara_points_history p LEFT JOIN tara_raid_history r ON p.raid_id=r.id LEFT JOIN tara_loot l ON p.item=l.short_name WHERE p.item!='' AND p.refunded=''	ORDER BY p.id DESC LIMIT 30;");
	if ($db->numrows()===0){
		$chatBot->send("No loot history yet.",$sendto);
		return;
	}
	$blob = "<header>:: Loot history (last 30 results) ::<end>\n\n";
	while($row=$db->fObject()){
		if ($row->name!=""){
			$points = 0-$row->change;
			$blob .= "<yellow>{$row->name}<end> " . Text::make_link(gmdate('j/M',($row->time)),"/tell <myname> !raidhistory {$row->raid_id}",'chatcmd') . " won " . Text::make_link($row->short_name,"/tell <myname> !items {$row->long_name}",'chatcmd') . " for <orange>{$points}<end> points\n";
		} else {
			$blob .= "<yellow>------<end> " . Text::make_link(gmdate('j/M',($row->time)),"/tell <myname> !raidhistory {$row->raid_id}",'chatcmd') . " deleted " . Text::make_link($row->short_name,"/tell <myname> !items {$row->long_name}",'chatcmd') . " (No bidders)\n";
		}
	}
	$msg = Text::make_link("Loot history",$blob,'blob');
	$chatBot->send($msg,$sendto);
} else if (preg_match("/^loothistory ([a-z ]+)$/i", $message, $arr)) {
	$item=$arr[1];
	$db = DB::get_instance();
	$db->query("SELECT * FROM tara_loot WHERE short_name LIKE '{$item}' OR long_name LIKE '{$item}' LIMIT 1;");
	if ($db->numrows()===0){
		$chatBot->send("<highlight>{$item}<end> is not in the loot table",$sendto);
		return;
	}
	$itemrow=$db->fObject();
	$db->query("SELECT * FROM tara_points_history p LEFT JOIN tara_raid_history r ON p.raid_id=r.id WHERE p.item LIKE '{$itemrow->short_name}' AND p.refunded='' ORDER BY p.id DESC LIMIT 30;");
	if ($db->numrows()===0){
		$chatBot->send("No <highlight>{$item}<end> has dropped yet",$sendto);
		return;
	}
	$title = "<header>:: Loot history for {$itemrow->long_name} ::<end>\n";
	$blob = "";
	while($row=$db->fObject()){
		$points = 0-$row->change;
		if ($row->name!=""){
			$blob .= "<yellow>{$row->name}<end> " . Text::make_link(gmdate('j/M/y',($row->time)),"/tell <myname> !raidhistory {$row->raid_id}",'chatcmd') . " won " . Text::make_link($itemrow->short_name,"/tell <myname> !items {$itemrow->long_name}",'chatcmd') . " for <orange>{$points}<end> points\n";
		} else {
			$blob .= "<yellow>------<end> " . Text::make_link(gmdate('j/M/y',($row->time)),"/tell <myname> !raidhistory {$row->raid_id}",'chatcmd') . " deleted " . Text::make_link($row->short_name,"/tell <myname> !items {$row->long_name}",'chatcmd') . " (No bidders)\n";
		}
	}
	$db->query("SELECT COUNT(raid_id) AS cnt FROM (SELECT DISTINCT raid_id FROM tara_points_history WHERE item!='') as r;");
	$raids=$db->fObject();
	
	$db->query("SELECT COUNT(`id`) AS cnt, -SUM(`change`) AS pts FROM tara_points_history WHERE item LIKE '{$itemrow->short_name}' AND refunded='';");
	$dropped=$db->fObject();

	$db->query("SELECT COUNT(`id`) AS cnt, -SUM(d.change) AS pts FROM (SELECT * FROM tara_points_history WHERE item LIKE '{$itemrow->short_name}' AND refunded='' ORDER BY id DESC LIMIT 10) d;");
	$last_dropped=$db->fObject();
	
	$title .= "<yellow>::<end>Dropped: <white>{$dropped->cnt}<end> Droprate: <white>" . round(100*$dropped->cnt/$raids->cnt) . "%<end>\n<yellow>::<end>Average cost: <white>" . round($dropped->pts/$dropped->cnt) . "<end> Recent: <white>" . round($last_dropped->pts/$last_dropped->cnt) . "<end>\n\n<center>" . Tara::get_item_blob($itemrow->long_name) . "</center>\n<white>Last 30 wins:<end>\n";

	$msg = Text::make_link("Loot history for {$itemrow->long_name}",$title . $blob,'blob');
	$chatBot->send($msg,$sendto);
}  else if (preg_match("/^lootplayer$/i", $message) || preg_match("/^lootplayer ([a-z0-9-]+)$/i", $message, $arr)) {
	if(isset($arr[1])){
		$name=ucfirst(strtolower($arr[1]));
		$uid = $chatBot->get_uid($name);
		if(!$uid) {
			$chatBot->send("Invalid player <highlight>{$arr[1]}<end>",$sendto);
			return;
		}
		
	} else $name = $sender;
	$name = Tara::get_account_name($name);
	if(isset($chatBot->data["TARA_MODULE"]["auction"]) && $name!=$sender){
		$chatBot->send("<orange>Auction in progress, try again later.<end>",$sender);
		return;
	}
	$db = DB::get_instance();
	$db->query("SELECT p.*,r.time,l.long_name FROM tara_points_history p LEFT JOIN tara_raid_history r ON p.raid_id=r.id LEFT JOIN tara_loot l ON p.item=l.short_name WHERE p.account LIKE '{$name}' AND p.item!='' ORDER BY p.id DESC;");
	if ($db->numrows()===0){
		$chatBot->send("<highlight>{$name}<end> hasn't won anything yet",$sendto);
		return;
	}
	$blob = "<header>:: Loot history for player {$name} ::<end>\n";

	while($row=$db->fObject()){
		$blob .= "\n<yellow>{$row->name}<end> " . Text::make_link(gmdate('j/M/y',($row->time)),"/tell <myname> !raidhistory {$row->raid_id}",'chatcmd');
		$points = 0-$row->change;
		$blob .= " won <highlight>" . Text::make_link($row->item,"/tell <myname> !items {$row->long_name}",'chatcmd') . "<end> for <orange>{$points}<end> points";
		if ($chatBot->admins[$sender]["level"] >= 3 && $sendto==$sender) $blob .= " (id:{$row->id})";
		if ($row->refunded!="") $blob .= " <orange>[refunded by {$row->refunded}]<end>";

	}

	$msg = Text::make_link("Loot history for {$name}",$blob,'blob');
	$chatBot->send($msg,$sendto);
} else {
	$syntax_error = true;
}

?>