<?php

if (preg_match("/^teamassist clear$/i", $message, $arr)) {
	Setting::save('teamassist', '');
	if ($sendto!='prv') $chatBot->send("TeamAssist was cleared.",$sender);
	$chatBot->send("TeamAssist was cleared by <highlight>{$sender}<end>.",'priv');	
} else if (preg_match("/^teamassist ([a-z0-9- ]+)$/i", $message, $arr)) {
    $name = $arr[1];
    $uid = $chatBot->get_uid(ucfirst(strtolower($name)));

	$err="";
	$inbot="";
	$arr_str="";
	$name_arr = explode (" ",$name);
	$arr=array();
	foreach($name_arr as $nm){
		$nm = ucfirst(strtolower($nm));
		$id = $chatBot->get_uid(ucfirst(strtolower($nm)));
		if(!$id) $err.=" $nm";
		else if (!isset($chatBot->chatlist[$nm])){
			$inbot .=" $nm";
		} else {
			$arr[]=$nm;
			$arr_str = $nm . " " . $arr_str;
		}
	}
	if(count($arr)>0){
		$colors = array("FFFF66","99FF66","00FF66","DDFFFF","FF99FF","66FF00");
		$i=1;
		$caller = 0;
		$link = "<header>::::: TeamAssist Macros :::::<end>\n";
		$macroarr = array_reverse($arr);
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
		
		if(count($arr)<2){
			$chatBot->send("<orange>Need at least 2 callers for teamassist<end>",$sendto);
		} else {
			Setting::save('teamassist', trim($arr_str));
			
			$msg = Text::make_link("TeamAssist Macros", $link) . " : <font color=\"#FFFF00\">set assist by team number</font>";	
		}
	}
	if($err!=""){
		$err="Check:<highlight>" . $err . "<end>";
		$chatBot->send($err,$sendto);
	}
	if($inbot!=""){
		$err="Not in bot:<highlight>" . $inbot . "<end>";
		$chatBot->send($err,$sendto);
	}		
	if($msg!=""){
		$chatBot->send($msg, 'priv');
		$chatBot->send($msg, 'priv');
		$chatBot->send($msg, 'priv');
	}		
} else {
	$syntax_error = true;
}
?>