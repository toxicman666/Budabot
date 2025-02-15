<?php
   /*
   ** Author: Derroylo (RK2)
   ** Description: AFK Handling
   ** Version: 1.0
   **
   ** Developed for: Budabot(http://sourceforge.net/projects/budabot)
   **
   ** Date(created): 23.11.2005
   ** Date(last modified): 21.11.2006
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

if (preg_match("/^.?afk$/i", $message, $arr)) {
    $db->exec("UPDATE online_<myname> SET `afk` = 1 WHERE `name` = '$sender' AND added_by = '<myname>' AND channel_type = 'priv'");
    $msg = "<highlight>$sender<end> is now AFK";
	$chatBot->send($msg, $sendto);
} else if (preg_match("/^.?afk (.*)$/i", $message, $arr)) {
	$reason = str_replace("'", "''", $arr[1]);
    $db->exec("UPDATE online_<myname> SET `afk` = '$reason' WHERE `name` = '$sender' AND added_by = '<myname>' AND channel_type = 'priv'");
    $msg = "<highlight>$sender<end> is now AFK";
	$chatBot->send($msg, $sendto);
} else if (preg_match("/^.?kiting$/i", $message, $arr) && $numrows != 0) {
	$db->exec("UPDATE online_<myname> SET `afk` = 'kiting' WHERE `name` = '$sender' AND added_by = '<myname>' AND channel_type = 'priv'");
	$msg = "<highlight>$sender<end> is now kiting";
	$chatBot->send($msg, $sendto);
} else {
	$syntax_error = true;
}

?>
