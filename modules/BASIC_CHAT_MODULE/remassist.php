<?php

   
if (preg_match("/^remassist ([a-z0-9-]+)$/i", $message, $arr)) {
	$ass=Setting::get('assist');
  	if ($ass!="") {
		$name=ucfirst($arr[1]);
		$arr=explode(" ", $ass);
		if(in_array($name,$arr)){
			$ass_str="";
			foreach($arr as $nm)
				if($nm!=$name){
					$newarr[]=$nm;
					$ass_str .= " " . $nm;
				}
			Setting::save('assist', trim($ass_str));
			if(count($newarr)==1){
				$link = "<header>::::: Assist Macro on $newarr[0]:::::\n\n";
				$link .= "<a href='chatcmd:///macro Ass /assist $newarr[0]'>Click here to make an assist macro on $newarr[0]</a>";
				$msg = Text::make_link("Assist $newarr[0]", $link);
			} else if (count($newarr)>1){
				$msg = "<font color=\"#FFFF00\">/macro Ass /assist " . $newarr[0];
				for($i=1;$i<count($newarr);$i++) $msg.= "\\n /assist " . $newarr[$i];
				$msg.="</font>";
			} else {
				$chatBot->send("Assist was cleared.",$sendto);
			}
		} else {
			$chatBot->send("$name is not in list.",$sendto);
		}
	} else {
		$chatBot->send("No assist set.", $sendto);
	}
	if($msg!=""){
		$chatBot->send($msg, 'priv');
		$chatBot->send($msg, 'priv');
		$chatBot->send($msg, 'priv');	
	}
} else if (preg_match("/^remassist$/i", $message)) {
	Setting::save('assist', '');
	$chatBot->send("Assist was cleared.",$sendto);
} else {
	$syntax_error = true;
}
?>