<?php

if (preg_match("/^refund ([0-9]+)$/i", $message, $arr)) {
	$id = intval($arr[1]);
	$db->query("SELECT * FROM tara_points_history WHERE id={$id} AND item!='' AND refunded='';");
	if ($db->numrows()==0) {
		$chatBot->send("Event #{$id} not found or already refunded.",$sendto);
		return;
	}
	$row = $db->fObject();
	$row->change=0-$row->change;
	unset($chatBot->data["TARA_MODULE"]["refund"][$sender]);
	$chatBot->data["TARA_MODULE"]["refund"][$sender]=$row->id;
	$chatBot->send("<yellow>You are about to refund {$row->change} points for {$row->item} to {$row->name} ({$row->account}).<end> If it is correct use /tell <myname> !refund",$sendto);
} else if (preg_match("/^refund$/i", $message)) {
	if (isset($chatBot->data["TARA_MODULE"]["refund"][$sender])){
		$id = $chatBot->data["TARA_MODULE"]["refund"][$sender];
		$db->query("SELECT * FROM tara_points_history WHERE id={$id} AND item!='' AND refunded='';");
		if ($db->numrows()==0) {
			$chatBot->send("Error! Event #{$id} not found or already refunded.",$sendto);
			return;
		}
		$row = $db->fObject();
		$row->change=0-$row->change;
		$time = time();
		$db->exec("UPDATE tara_points_history SET refunded='{$sender}', refunded_time={$time} WHERE id={$id};");
		$db->exec("UPDATE tara_points SET points=points+{$row->change} WHERE name='{$row->account}';");
		unset($chatBot->data["TARA_MODULE"]["refund"][$sender]);
		$chatBot->send("<yellow>Successfully refunded {$row->change} points to {$row->name} ({$row->account})<end>",$sendto);
		$chatBot->send("<yellow>{$row->change} points were added to {$row->name} ({$row->account}) as a refund for {$row->item}<end>",'priv');
		$chatBot->send("<yellow>{$row->change} points were added to your account as a refund for {$row->item}<end>",$row->name);
	} else {
		$chatBot->send("Check users history in tell for event id and use /tell <myname> !refund <event_id>",$sendto);
	}
} else if (preg_match("/^unrefund ([0-9]+)$/i", $message, $arr)) {
	$id = intval($arr[1]);
	$db->query("SELECT * FROM tara_points_history WHERE id={$id} AND item!='' AND refunded!='';");
	if ($db->numrows()==0) {
		$chatBot->send("Event #{$id} not found or not refunded.",$sendto);
		return;
	}
	$row = $db->fObject();
	$row->change=0-$row->change;
	
	$time = time ();
	$db->exec("UPDATE tara_points_history SET refunded='', refunded_time={$time} WHERE id={$id};");
	$db->exec("UPDATE tara_points SET points=points-{$row->change} WHERE name='{$row->account}';");
	$chatBot->send("<yellow>Successfully removed {$row->change} points from {$row->name} ({$row->account})<end>",$sendto);
	$chatBot->send("<orange>{$row->change} points were deducted from your account due to earlier refund for {$row->item} being canceled<end>",$row->name);

} else if (preg_match("/^refundhistory$/i", $message)) {
	$db->query("SELECT * FROM tara_points_history WHERE refunded!='' OR refunded_time!=0 ORDER BY refunded_time DESC;");
	if ($db->numrows()==0) {
		$chatBot->send("No items were refunded yet",$sendto);
		return;
	}
	$blob = "<header>:::: Point refunds history ::::<end>\n\n";

	while($row=$db->fObject()){
		$row->change=0-$row->change;
		$blob .= "<yellow>::<end> ";
		if ($row->refunded!="")
			$blob .= "{$row->change} points refunded to {$row->name} ({$row->account}) for {$row->item} by {$row->refunded} on " . gmdate('j/M/y G:i',$row->refunded_time) . "\n";
		else
			$blob .= "{$row->change} points refund for {$row->item} canceled to {$row->name} ({$row->account}) on " . gmdate('j/M/y G:i',$row->refunded_time) . "\n";
	}
	
	$msg = Text::make_link("Refund history", $blob, 'blob');
	$chatBot->send($msg,$sendto);
} else {
	$syntax_error = true;
}

?>