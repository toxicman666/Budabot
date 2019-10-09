<?php


if (preg_match("/^addassist ([a-z0-9- ]+)$/i", $message, $arr)) {
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
	$readarr=explode(" ", Setting::get('assist'));
//	$readarr=array_reverse($readarr);
	if(count($readarr)>0) {
		foreach ($readarr as $oldassist)
			if(!in_array($oldassist,$arr)&&$oldassist!=""){
				$arr[]=$oldassist;
//				$arr_str = $oldassist . " " .  trim($arr_str);
			}
	}
	
	if(count($arr)>1){
//		$arr=array_reverse($arr);
		$msg = "<font color=\"#FFFF00\">/macro Ass /assist " . $arr[0];
		$arr_str = $arr[0];
		for($i=1;$i<count($arr);$i++) {
			$msg.= "\\n /assist " . $arr[$i];
			$arr_str .= " " . $arr[$i];
		}
		$msg.="</font>";
		$arr_str=trim($arr_str);
		Setting::save('assist', $arr_str);
	} else if (count($arr)==1){
		$name = $arr[0];
		Setting::save('assist', $name);
		$link = "<header>::::: Assist Macro on $name :::::\n\n";
		$link .= "<a href='chatcmd:///macro Ass /assist $name'>Click here to make an assist on $name macro</a>";
		$msg = Text::make_link("Assist Macro on $name", $link);
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