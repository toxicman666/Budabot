<?php


if (preg_match("/assist$/i", $message)) {
	$ass=Setting::get('assist');
	$teamass=Setting::get('teamassist');
  	if ($ass!="") {
		$arr=explode(" ", $ass);
		if(count($arr)==1){
			$link = "<header>::::: Assist Macro on $ass:::::\n\n";
			$link .= "<a href='chatcmd:///macro Ass /assist $ass'>Click here to make an assist macro on $ass</a>";
			$msg = Text::make_link("Assist $ass", $link);
		} else {
			$msg = "<font color=\"#FFFF00\">/macro Ass /assist " . $arr[0];
			for($i=1;$i<count($arr);$i++) $msg.= "\\n /assist " . $arr[$i];
			$msg.="</font>";
		}
	} else if ($teamass!="") {
		$ass=trim($teamass);
		$arr=explode(" ", $ass);
		if (count($arr)==0){
			$chatBot->send("No Assist set.", $sendto);
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
// this macro crashes Client.exe
//		$link .= "\n" . Text::make_link("test",make_assist_macro($macro,"T" . $i),'chatcmd');
		$msg = Text::make_link("TeamAssist Macros", $link) . " : <font color=\"#FFFF00\">set assist by team number</font>";
	} else {
		$chatBot->send("No assist set.", $sendto);
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