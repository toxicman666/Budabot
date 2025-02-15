<?php
   /*
   ** Author: Derroylo (RK2)
   ** Description: Removes a admin from the adminlist
   ** Version: 0.1
   **
   ** Developed for: Budabot(http://sourceforge.net/projects/budabot)
   **
   ** Date(created): 30.01.2007
   ** Date(last modified): 30.01.2007
   **
   ** Copyright (C) 2007 C. Lohmann
   **
   ** Licence Infos:
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

if (preg_match("/^remadmin (.+)$/i", $message, $arr)){
	$who = ucfirst(strtolower($arr[1]));

	if ($chatBot->admins[$who]["level"] != 4) {
		$chatBot->send("<red>$who is not an Administrator of this Bot.<end>", $sendto);
		return;
	}
	
	if ($chatBot->vars["SuperAdmin"] != $sender){
		$chatBot->send("<red>You need to be Super-Administrator to kick a Administrator<end>", $sendto);
		return;
	}
	
	unset($chatBot->admins[$who]);
	$db->exec("DELETE FROM admin_<myname> WHERE `name` = '$who'");

	Buddylist::remove($who, 'admin');

	$chatBot->send("<highlight>$who<end> has been removed as an administrator.", $sendto);
	$chatBot->send("Your Administrator access to <myname> has been removed.", $who);
} else {
	$syntax_error = true;
}

?>