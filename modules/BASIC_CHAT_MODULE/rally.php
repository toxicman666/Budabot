<?php

if (preg_match("/^rally$/i", $message, $arr)) {
	$rally=Setting::get('rally');
	if($rally=="")
		$rally="No rally set";
	else {
		$rally_arr=explode(' ',$rally);
		$rally="Current rally: <a href=\"text://<center><a href='chatcmd:///waypoint {$rally_arr[1]} {$rally_arr[2]} {$rally_arr[0]}' style='text-decoration:none'><font color=CCInfoHeader>Get waypoint<br><font color=CCLinkColor><img src='rdb://11336'><br>{$rally_arr[1]}x{$rally_arr[2]}</font></a></center>\">{$rally_arr[1]}x{$rally_arr[2]}</a>";	
	}
	$chatBot->send($rally, $sendto);
	if($chatBot->data["leader"]==$sender){
		$chatBot->send($rally, 'priv');
		$chatBot->send($rally, 'priv');
	}
} else {
	$syntax_error = true;
}
