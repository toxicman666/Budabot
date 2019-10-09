<?php

if (preg_match("/^raidloot$/i", $message)) {
	// check if already running auction
	if (isset($chatBot->data["TARA_MODULE"]["auction"])){
		$chatBot->send("<orange>Auction in progress<end>",$sendto);
		return;
	}
	// check raid topic and raid status
	$topic=Setting::get("raid_topic");
	$status=Setting::get("raid_status");
	if($topic!="tara"&&$topic!="pvpwin"){
		$chatBot->send("<orange>There is no loot for this raid<end>",$sendto);
		return;
	}
	if($status!=0){
		$chatBot->send("<orange>Award points before rolling loot<end>",$sendto);
		return;
	}
	
	$db = DB::get_instance();
	$db->query("SELECT * FROM tara_loot");
	if($db->numrows()===0){
		$chatBot->send("<orange>Error! No loot in database<end>",$sendto);
		return;
	}
	$raidloot=array();
	while ($row=$db->fObject()){
		$raidloot[$row->category][$row->short_name]=$row->long_name;
	}
	foreach($raidloot as $category_name=>$category){
		$blob="<header>:::: {$category_name} ::::<end>\n\n";
		foreach($category as $item=>$value){
			$blob .= Tara::get_item_blob($value);
			$blob .= " ({$item}) [" . Text::make_link("Start auction","/tell <myname> !raidloot {$item}",'chatcmd') . "]\n\n";
		}
		$msg=Text::make_link($category_name,$blob,'blob');
		$chatBot->send($msg,'priv');
	}
} else if (preg_match("/^raidloot ([a-z]+)$/i", $message, $arr)) {
	$item = str_replace("'", "''", $arr[1]);
	// check if already running auction
	if (isset($chatBot->data["TARA_MODULE"]["auction"])){
		$chatBot->send("<orange>Auction in progress<end>",$sendto);
		return;
	}
	// check raid topic and raid status
	$topic=Setting::get("raid_topic");
	$status=Setting::get("raid_status");
	if($topic!="tara"&&$topic!="pvpwin"){
		$chatBot->send("<orange>There is no loot for this raid<end>",$sendto);
		return;
	}
	if($status!=0){
		$chatBot->send("<orange>Award points before rolling loot<end>",$sendto);
		return;
	}
	
	// check item
	$db->query("SELECT * FROM tara_loot WHERE short_name LIKE '{$item}';");
	if($db->numrows()===0){
		$chatBot->send("<orange>No item <end><highlight>{$item}<end><orange> in loot table.<end>",$sendto);
		return;
	}
	$itemrow=$db->fObject();
	
	// start auction
	$chatBot->data["TARA_MODULE"]["auction"]["item"]=$itemrow->short_name;
	$chatBot->data["TARA_MODULE"]["auction"]["item_long"]=$itemrow->long_name;
	$chatBot->data["TARA_MODULE"]["auction"]["end"]=time()+60;
	$chatBot->data["TARA_MODULE"]["auction"]["bidders"]=array();
	$chatBot->data["TARA_MODULE"]["auction"]["leader"]=$sender;
	
	$itemblob = "<header>:::: Auction for {$itemrow->long_name} ::::<end>\n\n";
	$itemblob .= "This auction is for:\n\n";
	$itemblob .= Tara::get_item_blob($itemrow->long_name);
	$item_help=file_get_contents("./modules/TARA_MODULE/item_help/" . $itemrow->short_name . ".txt");
	if ($item_help) $itemblob .= "\n\n" . $item_help;
	$itemlink = Text::make_link($itemrow->long_name,$itemblob,'blob');
	$chatBot->data["TARA_MODULE"]["auction"]["itemlink"]=$itemlink;
	
	$msg = "<yellow>{$sender} has started auction for<end> {$itemlink}";
	$chatBot->send($msg,'priv');
	$chatBot->send("<yellow>You have 1 minute to bid.<end>",'priv');
	
	$bid_help=file_get_contents("./modules/TARA_MODULE/bid.txt");
	$msg = "To bid: /tell <myname> bid &lt;points&gt; (" . Text::make_link("More help",$bid_help,'blob') . ")";
	$chatBot->send($msg,'priv');
} else if (preg_match("/^abort$/i", $message, $arr)) {
	if(!isset($chatBot->data["TARA_MODULE"]["auction"])){
		$chatBot->send("No auction in progress.",$sendto);
		return;
	}
	unset($chatBot->data["TARA_MODULE"]["auction"]);
	$chatBot->send("<yellow>Auction aborted by {$sender}<end>",'priv');
} else if (preg_match("/^bid ([0-9]+|all)$/i", $message, $arr)) {
	if(!isset($chatBot->data["TARA_MODULE"]["auction"])){
		$chatBot->send("No auction in progress.",$sender);
		return;
	}
	$inraid = Tara::in_raidlist($sender);
	if ($inraid===false) {
		$chatBot->send("You are not in raidlist.",$sendto);
		return;
	}
	//	else if ($inraid!=$sender && isset($chatBot->chatlist[$inraid])) {

	
	if ($arr[1]=="all"){
		$bid = -1;
	} else $bid = abs(intval($arr[1]));	// adding a check anyways
//	if ($bid<0){	// cannot bid negative cause '-' is not allowed in preg_match
//		$chatBot->send("Invalid bid.",$sender);
//		return;
//	}
	$points = Tara::get_points($sender);
	if ($points===false){
		$chatBot->send("<orange>You are not registered.<end>",$sender);
		return;		
	} else if ($points<0){
		$msg = "You cannot bid until you have positive amount of points (now: {$points} " . Text::make_link("Why?", "<header>:::: Negative points ::::<end>\n\n<orange>Points can go negative if you register an alt that has ever received www.omnihq.net 20 points bonus. This only happens if you registered multiple forum accounts, which is inappropriate. You will not be able to bid untill you get out of the debt.<end>", 'blob') . ")";
		$chatBot->send($msg,$sender);
		return;		
	} else if ($bid>$points){
		$chatBot->send("You do not have enough points to make this bid. You can bid between 0 and {$points}.",$sender);
		return;
	}
	if ($bid==-1) $bid = $points;
	$item = $chatBot->data["TARA_MODULE"]["auction"]["item_long"];
	if($chatBot->data["TARA_MODULE"]["auction"]["end"]<time()){
		$chatBot->send("No more bids accepted for this auction.",$sender);
		return;
	}
	if (isset($chatBot->data["TARA_MODULE"]["auction"]["bidders"][$sender])){
		if($bid==$chatBot->data["TARA_MODULE"]["auction"]["bidders"][$sender]){
			$chatBot->send("No change made",$sender);
			return;
		} else {
			$chatBot->data["TARA_MODULE"]["auction"]["bidders"][$sender]=$bid;
			$chatBot->send("You have updated your bid to <highlight>$bid<end> points. To remove your bid /tell <myname> !unbid",$sender);
			$chatBot->send("<yellow>{$sender} has updated his bid.<end>",'priv');
		}
	} else {
		$chatBot->data["TARA_MODULE"]["auction"]["bidders"][$sender]=$bid;
		$chatBot->send("You are bidding <highlight>$bid<end> points on <highlight>{$item}<end>. To remove your bid /tell <myname> !unbid",$sender);
		$chatBot->send("<yellow>{$sender} is bidding.<end>",'priv');		
	}
} else if (preg_match("/^unbid$/i", $message)) {
	if(!isset($chatBot->data["TARA_MODULE"]["auction"])){
		$chatBot->send("No auction in progress.",$sender);
		return;
	}
	$item = $chatBot->data["TARA_MODULE"]["auction"]["item_long"];

	if (!isset($chatBot->data["TARA_MODULE"]["auction"]["bidders"][$sender])){
		$chatBot->send("You have not placed a bid in this auction.",$sender);
	} else {
		unset($chatBot->data["TARA_MODULE"]["auction"]["bidders"][$sender]);
		$chatBot->send("You have removed your bid.",$sender);
		$chatBot->send("$sender has removed his bid.",'priv');
	}	
} else {
	$syntax_error = true;
}

?>