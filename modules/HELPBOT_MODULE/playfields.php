<?php

if(preg_match("/^playfields (\\d+)$/i", $message, $arr)){
	$playfield_match = $arr[1];
	$sql = "SELECT * FROM playfields WHERE id={$playfield_match}";
	$db->query($sql);
	if($db->numrows() != 0){
		$pf = $db->fObject();
		$chatBot->send("<highlight>Playfield {$playfield_match} is: <end>{$pf->long_name} ({$pf->short_name})",$sendto);
		return;
	} else {
		$chatBot->send("<highlight>Playfield {$playfield_match} not found<end>",$sendto);
		return;
	}
} else if (preg_match("/^playfields$/i", $message)) {
	$blob = "<header>:::::: Playfields ::::::<end>\n\n";
	
	$sql = "SELECT * FROM playfields ORDER BY long_name";
	$db->query($sql);
	while ($row = $db->fObject()) {
		$blob .= "{$row->id}   <green>{$row->long_name}<end>   <cyan>({$row->short_name})<end>\n";
	}
	
	$msg = Text::make_link("Playfields", $blob, 'blob');
	$chatBot->send($msg, $sendto);
} else {
	$syntax_error = true;
}

?>