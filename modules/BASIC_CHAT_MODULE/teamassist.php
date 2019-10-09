<?php


if (preg_match("/teamassist$/i", $message)) {
	$ass=Setting::get('teamassist');
  	if ($ass!="") {
		$ass=trim($ass);
		$arr=explode(" ", $ass);
		if (count($arr)==0){
			$chatBot->send("No TeamAssist set.", $sendto);
			return;
		}
		$arr = array_reverse($arr);
		$colors = array("FFFF66","99FF66","00FF66","DDFFFF","FF99FF","66FF00");
		$i=1;
		$caller = 0;
		$link = "<header>::::: TeamAssist Macros :::::<end>\n";
		for($i=1;$i<=6;$i++){
			
			unset($macro);
			$macro=array();
			$macro[]=$arr[$caller];
			foreach($arr as $name){
				$macro[]=$name;
			}			

			$link .= "\n\n<font color='#" . $colors[$i-1] . "'>Team $i ::</font> copy paste assist:\n";
			$link .= "<font color='#" . $colors[$i-1] . "'>";
			$link .= make_assist_macro($macro,"T" . $i);
			$link .= "</font>";

			$caller++;
			if($caller>(count($arr)-1)) $caller=0;
		}

		$msg = Text::make_link("TeamAssist Macros", $link) . " : <font color=\"#FFFF00\">set assist by team number</font>";
	} else {
		$chatBot->send("No TeamAssist set.", $sendto);
		return;
	}
	
	if($msg!=""){
		if($chatBot->data["leader"]==$sender){
			$chatBot->send($msg, 'priv');
			$chatBot->send($msg, 'priv');
			$chatBot->send($msg, 'priv');
		} else {
			$chatBot->send($msg, $sendto);
		}
	}
} else {
	$syntax_error = true;
}
?>