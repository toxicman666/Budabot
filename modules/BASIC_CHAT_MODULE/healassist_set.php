<?php

if (preg_match("/^heal ([a-z0-9- ]+)$/i", $message, $arr)) {
    $name = $arr[1];
    $uid = $chatBot->get_uid(ucfirst(strtolower($name)));
    if ($uid) {
		$name = ucfirst(strtolower($name));
		Setting::save('healassist', $name);
		$link = "<header>::::: HealAssist Macro on $name :::::\n\n";
		$link .= "<a href='chatcmd:///macro HEAL /assist $name'>Click here to make an healassist on $name macro</a>";
		$msg = Text::make_link("HealAssist Macro on $name", $link);
		$chatBot->send($msg, 'priv');
		$chatBot->send($msg, 'priv');
		$chatBot->send($msg, 'priv');
	} else {
		$err="";
		$arr_str="";
		$name_arr = explode (" ",$name);
		$arr=array();
		foreach($name_arr as $nm){
			$nm = ucfirst(strtolower($nm));
			$id = $chatBot->get_uid(ucfirst(strtolower($nm)));
			if(!$id) $err.=" $nm";
			else if(!in_array($nm,$arr)){
				$arr[]=$nm;
				$arr_str = $nm . " " . $arr_str;
			}
		}
		if(count($arr)>1){
			$arr = array_reverse($arr);
			$msg = "<font color=\"#FFFF00\">/macro HEAL /assist " . $arr[0];
			for($i=1;$i<count($arr);$i++) $msg.= "\\n /assist " . $arr[$i];
			$msg.="</font>";
			$arr_str=trim($arr_str);
			Setting::save('healassist', $arr_str);
		} else if (count($arr)==1){
			$name = $arr[0];
			Setting::save('healassist', $name);
			$link = "<header>::::: HealAssist Macro on $name :::::\n\n";
			$link .= "<a href='chatcmd:///macro $name /assist $name'>Click here to make a healassist on $name macro</a>";
			$msg = Text::make_link("HealAssist Macro on $name", $link);
		}
		if($err!=""){
			$err="Check:<highlight>" . $err . "<end>";
			$chatBot->send($err,$sendto);
		}
		if($msg!=""){
			$chatBot->send($msg, 'priv');
			$chatBot->send($msg, 'priv');
			$chatBot->send($msg, 'priv');
		}		
	}
} else {
	$syntax_error = true;
}
?>