<?php	

if (preg_match("/^rules$/i", $message)) {	
	$rules = file_get_contents("./modules/REGISTER_MODULE/rules.txt");
	$chatBot->send(Text::make_link("<myname> rules",$rules,'blob'),$sendto);
}

?>