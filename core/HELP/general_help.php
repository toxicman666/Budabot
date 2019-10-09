<?php


if (preg_match("/^about$/i", $message) || preg_match("/^help about$/i", $message)) {
	global $version;
	$data = file_get_contents("./core/HELP/about_" . $chatBot->vars["name"] . ".txt");
//	$data = str_replace('<version>', $version, $data);
	$msg = Text::make_link("About <myname>", $data);
	$chatBot->send($msg, $sendto);
} else if (preg_match("/^help$/i", $message)) {
	
	$blob = "<header>:::: <myname> help ::::<end>\n\n";
	$general = file_get_contents("./core/HELP/general.txt");
	$blob .= "<yellow>::<end> ";
	$blob .= $general;
	$blob .= "\n<yellow>::<end> Attending raid " . Text::make_link("(quick guide)","/tell <myname> !help attend",'chatcmd') . " <yellow>::<end>";
	
	$i = 0;
	$commands = array("rules","alts","raidlist","points","bid","auction","alts","spawn","loothistory","lootplayer","forums");
	foreach($commands as $cmd){
		if(($i++)%5==0) $blob .= "\n";
		$blob .= Text::make_link($cmd,"/tell <myname> !help $cmd",'chatcmd') . " ";
	}
	
	$blob .= "\n\n<yellow>::<end> Leading raid " . Text::make_link("(quick guide)","/tell <myname> !help lead",'chatcmd') . " <yellow>::<end>";

	$i = 0;
	$commands = array("leader","topic","raidstart","raidupdate","spam","box","troom","raidadd","raidkick","raidcheck","raidpoints","raidloot","raidend","assist","teamassist","heal","check","count","orders","setspawntime");
	foreach($commands as $cmd){
		if(($i++)%5==0) $blob .= "\n";
		$blob .= Text::make_link($cmd,"/tell <myname> !help $cmd",'chatcmd') . " ";
	}
	
	$blob .= "\n\n<yellow>::<end> Statistics <yellow>::<end>\n";
	$commands = array("history","top","stats");
	foreach($commands as $cmd){
		$blob .= Text::make_link($cmd,"/tell <myname> !help $cmd",'chatcmd') . " ";
	}
	
	$msg = Text::make_link("<myname> Help",$blob,'blob');
	$chatBot->send($msg, $sendto);
} else if (preg_match("/^help (.+)$/i", $message, $arr)) {
	$output = Help::find($arr[1], $sender);
	if ($output !== false) {
		$chatBot->send($output, $sendto);
	} else {
		$chatBot->send("No help found on this topic.", $sendto);
	}
}

?>