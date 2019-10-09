<?php

	function planters_getTowerType($ql) {
		$towerType = '';
		
/*		if ($ql >= 276) {
			$towerType = "8";
		} else */
		if ($ql >= 226) {
			$towerType = "7";
		} else if ($ql >= 201) {
			$towerType = "6";
		} else if ($ql >= 177) {
			$towerType = "5";
		} else if ($ql >= 129) {
			$towerType = "4";
		} else if ($ql >= 82) {
			$towerType = "3";
		} else if ($ql >= 34) {
			$towerType = "2";
		} else {
			$towerType = "1";
		}
		
		return $towerType;	
	}
	function planters_getItalicType($digit){
		switch($digit){
			case 1: return "I";
			case 2: return "II";
			case 3: return "III";
			case 4: return "IV";
			case 5: return "V";
			case 6: return "VI";
			case 7: return "VII";
			case 8: return "VIII";
			default: return "X";
		}
	}
	


?>