<?php
   
if (preg_match("/^kickall$/i", $message)) {
  	$msg = "Everyone will be kicked from this channel in 10 seconds. [by <highlight>$sender<end>]";
  	$chatBot->send($msg, 'priv');
  	$chatBot->data["priv_kickall"] = time() + 10;
	Event::activate("2sec", "PRIVATE_CHANNEL_MODULE/kickall_event.php");
} else {
	$syntax_error = true;
}
?>