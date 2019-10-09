<?php

   
if (preg_match("/^remheal ([a-z0-9-]+)$/i", $message, $arr)) {
	$healass=Setting::get('healassist');
  	if ($healass!="") {
		$name=ucfirst($arr[1]);
		$arr=explode(" ", $healass);
		if(in_array($name,$arr)){
			$ass_str="";
			foreach($arr as $nm)
				if($nm!=$name){
					$newarr[]=$nm;
					$ass_str .= " " . $nm;
				}
			Setting::save('healassist', trim($ass_str));
			if(count($newarr)==1){
				$link = "<header>::::: HealAssist Macro on $newarr[0]:::::\n\n";
				$link .= "<a href='chatcmd:///macro HEAL /assist $newarr[0]'>Click here to make an assist macro on $newarr[0]</a>";
				$msg = Text::make_link("HealAssist on $newarr[0]", $link);
			} else if (count($newarr)>1){
				$msg = "<font color=\"#FFFF00\">/macro HEAL /assist " . $newarr[0];
				for($i=1;$i<count($newarr);$i++) $msg.= "\\n /assist " . $newarr[$i];
				$msg.="</font>";
			} else {
				$chatBot->send("HealAssist was cleared.",$sendto);
			}
		} else {
			$chatBot->send("$name is not in list.",$sendto);
		}
	} else {
		$chatBot->send("No HealAssist set.", $sendto);
	}
	if($msg!=""){
		$chatBot->send($msg, 'priv');
		$chatBot->send($msg, 'priv');
		$chatBot->send($msg, 'priv');	
	}
} else if (preg_match("/^remheal$/i", $message)) {
	Setting::save('healassist', '');
	$chatBot->send("HealAssist was cleared.",$sendto);
} else {
	$syntax_error = true;
}
?>