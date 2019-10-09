<?php

if (preg_match("/^addloot default$/i", $message)) {
	$db->exec("DELETE FROM tara_loot;");
	
	$items_file=array();
	$items_file=file("./modules/TARA_MODULE/tara_loot.txt");
	if(!$items_file) {
		$chatBot->send("No tara_loot.txt file found.",$sendto);
		return;
	}
	$items=array();
	foreach($items_file as $line){
		if ($line{0}!="#"){
			$line=str_replace("\n","",$line);
			$items[]=explode("|", $line,3); // where 0=category 1=short_name 2=long_name
		}
	}
	foreach($items as $item){
		if(count($item)<3) {
			$chatBot->send("Syntax error in tara_loot.txt",$sendto);
			return;
		}
	}
	$skipped=array();
	$count=0;
	foreach($items as $item){
		$db->query("SELECT * FROM tara_loot WHERE short_name='" . $item[1] . "';");
		if($db->numrows()>0){
			$skipped[]=$item[1];
		} else {
			$db->exec("INSERT INTO tara_loot (category,short_name,long_name) VALUES ('" . $item[0] . "','" . $item[1] . "','" . $item[2] . "');");
			$count++;
		}
	}
	$msg = "Loot restored to default.";
	if(count($skipped)>0) {
		$blob = "<header>:::: Error adding items ::::<end>\n\n";
		$blob .= "Items with following short names are already in database:\n<yellow>";
		foreach($skipped as $item){
			$blob .= $item . " ";
		}
		$msg .= " (" . Text::make_link("Skipped items",$blob,'blob') . ")";
	}
	$chatBot->send($msg,$sendto);
} else if (preg_match("/^addloot ([a-z]+)|([a-z]+)|([a-z]+)$/i", $message, $arr)) {
	// $category = $arr[1];  $short_name = $arr[2];  $long_name = $arr[3];
	if (count($arr)<3) {
		$chatBot->send("Not enough arguments",$sendto);
		return;
	}
	$items=array();
	foreach ($arr as $item){
		$items[]=str_replace("'", "''", $item);
	}
	$db->query("SELECT * FROM tara_loot WHERE short_name='" . $items[2] . "';");
	if($db->numrows()>0) {
		$chatBot->send("Item with a short name <highlight>" . $items[2] . "<end> is already in the table",$sendto);
		return;
	}
	$db->exec("INSERT INTO tara_loot (category,short_name,long_name) VALUES ('" . $items[1] . "','" . $items[2] . "','" . $items[3] . "');");
	$blob = "<header>:::: Item added ::::<end>\n\n";
	$blob .= "Category: <yellow>" . $items[1] . "<end>\n";
	$blob .= "Short name: <yellow>" . $items[2] . "<end>\n";
	$blob .= "Long name: <yellow>" . $items[3] . "<end>\n";
	
	$chatBot->send("Successfully added " . Text::make_link($arr[2],$blob,'blob'),$sendto);
} else if (preg_match("/^remloot ([a-z]+)$/i", $message, $arr)) {
	// $short_name = $arr[1];
	$item=str_replace("'", "''", $arr[1]);

	$db->query("SELECT * FROM tara_loot WHERE short_name='" . $item . "';");
	if($db->numrows()==0) {
		$chatBot->send("Item with a short name <highlight>" . $item . "<end> is <highlight>not<end> in the table",$sendto);
		return;
	}
	$db->exec("DELETE FROM tara_loot short_name='" . $item . "';");
	
	$chatBot->send("Successfully deleted <highlight>{$item}<end>",$sendto);
}

?>