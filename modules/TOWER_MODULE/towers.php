<?php

if (preg_match("/^towers (.+)$/i", $message, $arr)) {
	
	$url = "http://towers:towersarenice@88.198.16.250/towers/index.py?output=aochat&q=" . urlencode(html_entity_decode($arr[1]));
	
	$html = file_get_contents($url);

	
	$chatBot->send(Text::make_link("Towers",$html,'blob'),$sendto);

	
}