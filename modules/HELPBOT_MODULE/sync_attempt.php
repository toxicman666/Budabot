<?php

	if(!isset($chatBot->data["HELPBOT_MODULE"]["sync"])){
		$chatBot->data["HELPBOT_MODULE"]["sync"] = 1;
		$success = Player::sync_attempt();
		while($success){
			$success = Player::sync_attempt();
		}
		unset($chatBot->data["HELPBOT_MODULE"]["sync"]);
	}

?>
