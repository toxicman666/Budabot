<?php


	if (count($chatBot->buddyList) != 0) {
	
		forEach ($chatBot->buddyList as $key => $value)
			if (count($value['types']) == 0) Buddylist::remove($value['name']);

	}
	
?>