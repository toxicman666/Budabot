<?php

/*
   ** Author: Chachy (RK2), based on code for Pande Loot Bot by Marinerecon (RK2)
   ** Description: DB Loot Module
   ** Version: 1.0
   **
   ** Developed for: Budabot(http://sourceforge.net/projects/budabot)
   **
   ** Date(created): 09.01.2009
   ** Date(last modified): 09.02.2009
   **
   ** This file is part of Budabot.
   **
   ** Budabot is free software; you can redistribute it and/or modify
   ** it under the terms of the GNU General Public License as published by
   ** the Free Software Foundation; either version 2 of the License, or
   ** (at your option) any later version.
   **
   ** Budabot is distributed in the hope that it will be useful,
   ** but WITHOUT ANY WARRANTY; without even the implied warranty of
   ** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   ** GNU General Public License for more details.
   **
   ** You should have received a copy of the GNU General Public License
   ** along with Budabot; if not, write to the Free Software
   ** Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!function_exists('get_db_loot')) {
	function get_db_loot($raid, $category) {
		$blob = Raid::find_raid_loot($raid, $category);
		$blob .= "\n\nDust Brigrade Loot By Chachy (RK2)";
		return Text::make_link("$raid $category Loot", $blob);
	}
}

if (preg_match("/^db1$/i", $message)){
	$chatBot->send(get_db_loot('Dust Brigade', 'Armor'), $sendto);
	$chatBot->send(get_db_loot('Dust Brigade', '1'), $sendto);
} else if (preg_match("/^db2$/i", $message)){
	$chatBot->send(get_db_loot('Dust Brigade', 'Armor'), $sendto);
	$chatBot->send(get_db_loot('Dust Brigade', '2'), $sendto);
} else {
	$syntax_error = true;
}

?>