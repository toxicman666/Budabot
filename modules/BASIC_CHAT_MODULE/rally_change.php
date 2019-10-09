<?php

if (preg_match("/^rally clear$/i", $message, $arr)) {
	Setting::save('rally',"");
	$chatBot->send("Rally cleared", $sendto);
} else if (preg_match("/^rally ([0-9]+) ([0-9]+)$/i", $message, $arr)) {
	$rallyx = $arr[1];
	$rallyy = $arr[2];
	$rally = Setting::get('rally');
	if($rally==""){
		$chatBot->send("No rally set - specify playfield",$sendto);
		return;
	}
	$rally_arr=explode(' ', $rally);
	$rally="{$rally_arr[0]} {$rallyx} {$rallyy}";
	Setting::save('rally',$rally);
	$rally="Current rally: <a href=\"text://<center><a href='chatcmd:///waypoint {$rallyx} {$rallyy} {$rally_arr[0]}' style='text-decoration:none'><font color=CCInfoHeader>Get waypoint<br><font color=CCLinkColor><img src='rdb://11336'><br>{$rallyx}x{$rallyy}</font></a></center>\">{$rallyx}x{$rallyy}</a>";
	$chatBot->send($rally,'priv');
	$chatBot->send($rally, 'priv');
	$chatBot->send($rally, 'priv');

} else if (preg_match("/^rally ([0-9a-z]+) ([0-9]+) ([0-9]+)$/i", $message, $arr)) {
	$playfield_name=$arr[1];
	$rallyx=$arr[2];
	$rallyy=$arr[3];
	$sql = "SELECT * FROM playfields WHERE `long_name` LIKE '{$playfield_name}' OR `short_name` LIKE '{$playfield_name}' LIMIT 1";
		
	$db->query($sql);	
	$playfield = $db->fObject();
	
	if ($playfield === null) {
		$msg = "Playfield '$playfield_name' could not be found";
		$chatBot->send($msg, $sendto);
		return;
	}
	$rally = "{$playfield->id} {$rallyx} {$rallyy}";
	Setting::save('rally',$rally);
	$rally="(Rally: <a href=\"text://<center><a href='chatcmd:///waypoint {$rallyx} {$rallyy} {$playfield->id}' style='text-decoration:none'><font color=CCInfoHeader>Get waypoint<br><font color=CCLinkColor><img src='rdb://11336'><br>{$rallyx}x{$rallyy}</font></a></center>\">{$rallyx}x{$rallyy}</a>) ";
	$chatBot->send($rally,$sendto);
	if($sendto=='priv'){
		$chatBot->send($rally, 'priv');
		$chatBot->send($rally, 'priv');
	}	
	
} else {
	$syntax_error = true;
}
