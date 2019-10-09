<?php

if (preg_match("/^topic clear$/i", $message, $arr)) {
  	Setting::save("topic_time", time());
  	Setting::save("topic_setby", $sender);
  	Setting::save("topic", "");
	Setting::save("rally", "");
	Setting::save('assist', '');
	Setting::save('healassist', '');
	Setting::save("orders","");
	$chatBot->data["BASIC_CHAT_MODULE"]["orders"] = "";
	$msg = "Topic has been cleared.";
    $chatBot->send($msg, $sendto);
} else if (preg_match("/^topic (.+)$/i", $message, $arr)) {
  	Setting::save("topic_time", time());
  	Setting::save("topic_setby", $sender);
  	Setting::save("topic", $arr[1]);
	Setting::save("rally","");
	$msg = "Updated topic: {$arr[1]}";
    $chatBot->send($msg, $sendto);
	if($type!='priv') $chatBot->send($msg . " [by {$sender}]", 'priv');
} else {
	$syntax_error = true;
}

?>