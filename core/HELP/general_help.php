<?php

if (preg_match("/^about$/i", $message) || preg_match("/^help about$/i", $message)) {
	$data = file_get_contents("./core/HELP/about_" . $chatBot->vars["name"] . ".txt");
	$msg = Text::make_link("About <myname>", $data);
	$chatBot->send($msg, $sendto);
} else if (preg_match("/^help$/i", $message)) {
	$blob = "<header>:::: <myname> help ::::<end>\n\n";
	$general = file_get_contents("./core/HELP/general.txt");
	$blob .= "<yellow>::<end> ";
	$blob .= $general;
	
	$blob .= "\n\n<yellow>::<end> Leader commands\n";
	$commands = array("leader","topic","assist","heal","rally","check","loot","count");
	foreach($commands as $cmd){
		$blob .= Text::make_link($cmd,"/tell <myname> !help $cmd",'chatcmd') . " ";
	}
	
	$blob .= "\n\n<yellow>::<end> Land control\n";
	$commands = array("lc","scout","open","opentimes");
	foreach($commands as $cmd){
		$blob .= Text::make_link($cmd,"/tell <myname> !help $cmd",'chatcmd') . " ";
	}
	
	$blob .= "\n\n<yellow>::<end> Statistics\n";
	$commands = array("attacks","victory","planttimer");
	foreach($commands as $cmd){
		$blob .= Text::make_link($cmd,"/tell <myname> !help $cmd",'chatcmd') . " ";
	}
	$blob .= Text::make_link("penalty","/tell <myname> !penalty",'chatcmd') . " ";
	
	$blob .= "\n\n<yellow>::<end> Other commands\n";
	$commands = array("clan","planters","twinks");
	foreach($commands as $cmd){
		$blob .= Text::make_link($cmd,"/tell <myname> !help $cmd",'chatcmd') . " ";
	}
	
//	$blob .= "\n\n<yellow>::<end> " . Text::make_link("Attending battle","/tell <myname> !help attend",'chatcmd');
//	$blob .= "\n\n<yellow>::<end> " . Text::make_link("Leading battle","/tell <myname> !help lead",'chatcmd');

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