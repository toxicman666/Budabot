<?php

if (isset($chatBot->data["TARA_MODULE"]["auction"])){
	$tleft=$chatBot->data["TARA_MODULE"]["auction"]["end"]-time();
	if($tleft<46&&$tleft>=44 || $tleft<31&&$tleft>=29 || $tleft<16&&$tleft>=14){
		if($tleft<46&&$tleft>=44) $seconds = 45;
		if( $tleft<31&&$tleft>=29) $seconds = 30;
		if($tleft<16&&$tleft>=14) $seconds = 15;
		$msg = "<yellow>This is an auction for " . $chatBot->data["TARA_MODULE"]["auction"]["itemlink"];
		$count=count($chatBot->data["TARA_MODULE"]["auction"]["bidders"]);
		if($count>0){
			if($count==1) $header = "bidder";
			else $header = "bidders";
			$bidders = "<header>:::: {$count} {$header} ::::<end>\n";
			$bidders .= ":: for <white>" . $chatBot->data["TARA_MODULE"]["auction"]["item_long"] . "<end>\n\n";
			foreach($chatBot->data["TARA_MODULE"]["auction"]["bidders"] as $bidder=>$bid){
				$bidders .= $bidder . "\n";
			}
			$msg .= " (" . Text::make_link("{$count} {$header}",$bidders,'blob') . ")";
		} else $msg .= " (No bidders)";	
		
		$chatBot->send($msg,'priv');
		$chatBot->send("<yellow>You have {$seconds} seconds to bid.<end>",'priv');
	} else if ($tleft<1&&$tleft>=-1) {
		$msg = "<yellow>Auction for " . $chatBot->data["TARA_MODULE"]["auction"]["itemlink"];
		$msg .= " is now closed - no more bids accepted.";
		$count=count($chatBot->data["TARA_MODULE"]["auction"]["bidders"]);
		if($count>0){
			if($count==1) $header = "bidder";
			else $header = "bidders";
			$bidders = "<header>:::: {$count} {$header} ::::<end>\n";
			$bidders .= ":: for <white>" . $chatBot->data["TARA_MODULE"]["auction"]["item_long"] . "<end>\n\n";
			foreach($chatBot->data["TARA_MODULE"]["auction"]["bidders"] as $bidder=>$bid){
				$bidders .= $bidder . "\n";
			}
			$msg .= " (" . Text::make_link("{$count} {$header}",$bidders,'blob') . ")";
		} else $msg .= " (No bidders)";	
		
		if(count($chatBot->data["TARA_MODULE"]["auction"]["bidders"])==0){
			$chatBot->send("<yellow>Auction has ended!<end>",'priv');
			$chatBot->send("<yellow>No bidders on " . $chatBot->data["TARA_MODULE"]["auction"]["itemlink"] . " - item is to be deleted<end>",'priv');
			$item = $chatBot->data["TARA_MODULE"]["auction"]["item"];
			$raid_id = Tara::last_raid_id();
			$db->exec("INSERT INTO tara_points_history (`account`,`name`,`change`,`raid_id`,`item`) VALUES ('','',0,'{$raid_id}','{$item}');");
			unset($chatBot->data["TARA_MODULE"]["auction"]);
			return;
		}
		$chatBot->send($msg,'priv');
		$chatBot->send("<yellow>Auction will complete in 10 seconds.<end>",'priv');
		$chatBot->send("<yellow>If you did not mean to bid: /tell <myname> !unbid<end>",'priv');
	} else if ($tleft<-9) {
		$count=count($chatBot->data["TARA_MODULE"]["auction"]["bidders"]);
		if($count>1){
			$i=0; // sort by points DESC
			foreach($chatBot->data["TARA_MODULE"]["auction"]["bidders"] as $bidder=>$bid){
				$bidders_arr[$i]->player=$bidder;
				$bidders_arr[$i]->bid=$bid;
				$i++;
			}
			$sorted=false;
			while(!$sorted){
				$sorted=true;
				for($i=0;$i<(count($bidders_arr)-1);$i++){
					if(($bidders_arr[$i]->bid)<($bidders_arr[$i+1]->bid)){ // switch
						$temp=$bidders_arr[$i+1];
						$bidders_arr[$i+1]=$bidders_arr[$i];
						$bidders_arr[$i]=$temp;
						unset($temp);
						$sorted=false;
					}
				}
			}
			$winners=array();
			$winningbid=$bidders_arr[0]->bid;
			$secondbid=0;
			foreach($bidders_arr as $bidder){
				if ($bidder->bid == $winningbid) {
					$winners[]=$bidder;
					if (count($winners)>1) $secondbid=$bidder->bid;
				} else if ($bidder->bid>$secondbid) $secondbid=$bidder->bid;
			}
			if (count($winners)>1){
				$winner_index=rand(0,(count($winners)-1));
				$winner=$winners[$winner_index]->player;
			} else {
				$winner=$winners[0]->player;
			}
		} else if ($count==1){
			foreach($chatBot->data["TARA_MODULE"]["auction"]["bidders"] as $bidder=>$bid){
				$winner = $bidder;
				$winningbid=$bid;
			}
			$secondbid=0;
		} else {
			$chatBot->send("<yellow>Auction has ended!<end>",'priv');
			$chatBot->send("<yellow>No bidders on " . $chatBot->data["TARA_MODULE"]["auction"]["itemlink"] . " - item is to be deleted<end>",'priv');
			unset($chatBot->data["TARA_MODULE"]["auction"]);
			return;
		}
		
		$msg = "<yellow>{$winner} wins<end> " . $chatBot->data["TARA_MODULE"]["auction"]["itemlink"] . " for {$secondbid} points";
		$auction_help="<header>:::: Explanation of this auction ::::<end>\n\n";
		$auction_help.="{$winner} was the highest bidder with {$winningbid} points. The second highest bid (highest losing bid) was {$secondbid} points. Therefore {$winner} wins auction and pays {$secondbid} points.\n\n\n";
		$auction_help.=file_get_contents("./modules/TARA_MODULE/auction.txt");
		$msg .= " (" . Text::make_link("Why?",$auction_help,'blob') . ")";
		$chatBot->send("<yellow>Auction has ended!<end>",'priv');
		$chatBot->send($msg,'priv');
		
		// deduct points
		$raid_id = Tara::last_raid_id();
		$account = Tara::get_account_name($winner);
		$db->exec("UPDATE tara_points SET points=points-{$secondbid} WHERE name='{$account}';");
		// record history
		$secondbid=0-$secondbid;
		$item = $chatBot->data["TARA_MODULE"]["auction"]["item"];
		$db->exec("INSERT INTO tara_points_history (`account`,`name`,`change`,`raid_id`,`item`) VALUES ('{$account}','{$winner}',{$secondbid},'{$raid_id}','{$item}');");
		unset($chatBot->data["TARA_MODULE"]["auction"]);
	}
}

?>