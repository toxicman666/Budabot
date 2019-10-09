<?php

if (preg_match("/^penalty$/i", $message, $arr)) {
	$begin = intval(gmdate("U"))-14400;
	$sql = "SELECT * FROM 
				(SELECT att_guild_name, time, att_faction
				FROM tower_attack
				HAVING time > $begin
				ORDER BY time DESC) t
			LEFT JOIN (SELECT guild_name,COUNT(guild_name) as count 
				FROM scout_info 
				WHERE is_current=1 
				GROUP BY guild_name) sc
			ON sc.guild_name = t.att_guild_name
			GROUP BY
				t.att_guild_name
			ORDER BY
				att_faction ASC,
				time DESC;";
	$db = DB::get_instance();	
	$db->query($sql);
	$msg="";
	if ($db->numrows() === 0) {
		$msg="There was no attacks during past 4 hours.";
	}
	else {
		$blob="";
		$clan=false;
		$omni=false;
		$neut=false;
		while ($row = $db->fObject()) {
			if($row->att_guild_name!=""){
				if($row->att_faction == "Clan" && !$clan){
					$blob.="\n<center><font color=#FFCC00>:: CLAN ::</font></center>\n";
					$clan=true;
				}
				if($row->att_faction == "Omni" && !$omni){
					$blob.="\n<center><font color=#00FFFF>:: OMNI ::</font></center>\n";
					$omni=true;
				}		
				if($row->att_faction == "Neutral" && !$neut){
					$blob.="\n<center><font color=#FFFFFF>:: NEUTRAL ::</font></center>\n";
					$neut=true;
				}
				$str_time="";
				$hours=floor((intval(gmdate("U"))-$row->time)/3600);
				$minutes=floor(((intval(gmdate("U"))-$row->time)%3600)/60);
				if($hours>0) $str_time.=$hours . " h ";
				$str_time.=$minutes . " mins";
				if ($row->count) $blob.= $row->count;
				else $blob .= "0";
				if ($row->count==1) $blob .= " base ";
				else $blob .= " bases ";
				$blob .= Text::make_link("{$row->att_guild_name}", "/tell <myname> lc org $row->att_guild_name", 'chatcmd') . "<br>:: " . Text::make_link("last attack","/tell <myname> battle org {$row->att_guild_name}",'chatcmd') . ": <highlight>" . $str_time . " ago<end>\n";
			}
		}	
		$msg=Text::make_link('Orgs on penalty', $blob, 'blob');
	}
	$chatBot->send($msg,$sendto);
} else {
	$syntax_error = true;
}