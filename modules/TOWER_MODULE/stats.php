<?php

if (preg_match("/^topscout$/i", $message) || preg_match("/^top scout$/i", $message)) {

	$db = DB::get_instance();
	$blob = "<header>:::: Top 25 Scouts ::::<end>\n";
	
	$blob .= "\n<yellow>This month:<end>";
	$blob .= "\n<white>:name: :scouts:<end>\n";
	$db->query("SELECT IF(a.main IS NULL,s.scouted_by,a.main) AS name, COUNT(scouted_by) AS cnt FROM scout_info_history s LEFT JOIN alts a ON s.scouted_by=a.alt WHERE MONTH(s.scouted_on)=" . date('m') . " AND YEAR(s.scouted_on)=" . date('Y') . " AND s.force=0 GROUP BY name ORDER BY cnt DESC LIMIT 25;");
	$i=1;
	while ($row=$db->fObject()){
		$blob .= "<highlight>" . $i++ . " {$row->name}<end> {$row->cnt}\n";
	}	

	$blob .= "\n<yellow>Since beginning:<end>";
	$blob .= "\n<white>:name: :scouts:<end>\n";	
	$db->query("SELECT IF(a.main IS NULL,s.scouted_by,a.main) AS name, COUNT(scouted_by) AS cnt FROM scout_info_history s LEFT JOIN alts a ON s.scouted_by=a.alt WHERE s.force=0 GROUP BY name ORDER BY cnt DESC LIMIT 25;");
	$i=1;
	while ($row=$db->fObject()){
		$blob .= "<highlight>" . $i++ . " {$row->name}<end> {$row->cnt}\n";
	}		
	$msg = Text::make_link("Top scouts",$blob,'blob');
	$chatBot->send($msg,$sendto);	
	
} else {
	$syntax_error = true;
}

?>