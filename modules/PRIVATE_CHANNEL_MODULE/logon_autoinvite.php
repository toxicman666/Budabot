<?php
   /*
   ** Author: Derroylo (RK2)
   ** Description: Private Channel (Auto-Invite)
   ** Version: 1.0
   **
   ** Developed for: Budabot(http://sourceforge.net/projects/budabot)
   **
   ** Date(created): 18.02.2006
   ** Date(last modified): 10.12.2006
   ** 
   ** Copyright (C) 2005, 2006 Carsten Lohmann
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

$db->query("SELECT * FROM members_<myname> WHERE name = '$sender' AND autoinv = 1");
if ($db->numrows() != 0 && !isset($chatBot->chatlist[$sender])) {
    $msg = "You have been auto invited to the <highlight><myname><end> channel.";
    $chatBot->privategroup_invite($sender);
    $chatBot->send($msg, $sender);
	
	if(Setting::get('add_to_members_on_join')==1){
		$db->exec("DELETE FROM members_<myname> WHERE `name` = '$sender'");
		Buddylist::remove($sender, 'member');
	}
}

?>