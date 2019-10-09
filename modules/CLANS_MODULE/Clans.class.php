<?php

class Clans {
	public static function get_tl($lvl) {
		if($lvl>=1 && $lvl<15)
			return 1;
		else if ($lvl>=15 && $lvl<50)
			return 2;
		else if ($lvl>=50 && $lvl<100)
			return 3;
		else if ($lvl>=100 && $lvl<150)
			return 4;
		else if ($lvl>=150 && $lvl<190)
			return 5;
		else if ($lvl>=190 && $lvl<205)
			return 6;
		else if ($lvl>=205 && $lvl<=220)
			return 7;
		else return 0;
	}
	public static function rem($name) {
		global $db;
		$name = ucfirst($name);
		$sql = "SELECT * FROM clans WHERE name='{$name}';";
		$db->query($sql);
		if($db->numrows() !== 0){
			$sql = "DELETE FROM clans WHERE name='{$name}';";
			$db->exec($sql);
			return true;
		} else return false;
	}
}

?>