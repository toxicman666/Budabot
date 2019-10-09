<?php

if (preg_match("/spam (.+)/i", $message, $arr)) {
	$chatBot->send("spam $sender $arr[1]", $this->settings['otspambot']);
	$chatBot->send($this->settings['otspambot'] . ' has been notified.', $sendto);
} else {
	$syntax_error = true;
}

?>