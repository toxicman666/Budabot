<?php

// function accepts array of caller names and name of macro, returns string or link for assist macro
function make_assist_macro ($array,$name){

	$macro = array();
	foreach($array as $caller){
		if(!in_array($caller,$macro) && $caller!=""){
			$macro[]=$caller;
		}
	}
	
	$cnt = count($macro);
	if ($cnt==0){
		return "No assist set";
	} else if ($cnt==1){
		return "<a href='chatcmd:///macro $name /assist " . $macro[0] . "'>Assist " . $macro[0] . "</a>";
	} else {
		$macro=array_reverse($macro);
		$str="/macro $name /assist " . $macro[0];
		for ($i=1;$i<$cnt;$i++){
			$str.="&#92;n /assist " . $macro[$i];
		}
		return $str;
	}
	return "Error";
}

/* usage:
	$array=explode(" ",$callers_str);
	$caller=0;
	for ($i=1;$i<=6;$i++){
		unset($macro);
		$macro=array();
		$macro[]=$array[$caller];
		foreach($array as $arr){
			$macro[]=$arr;
		}
				
		$blob .= "\n\n<font color='#" . $colors[$i-1] . "'>Team $i ::</font> copy paste assist:\n";
		$blob .= "<font color='#" . $colors[$i-1] . "'>";
		$blob .= make_assist_macro($macro,"T" . $i);
		$blob .= "</font>";
		
		$caller++;
		if($caller>count($array)-1) $caller=0;
	}
*/
?>