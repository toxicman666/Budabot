<?php
	function clan_sort($a, $b){
		return (strcmp($a->name, $b->name)<0) ? -1 : 1;
	}
		
?>
