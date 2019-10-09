<?php
   /*
   ** Author: Derroylo (RK2)
   ** Description: Checks if a player is online
   ** Version: 0.1
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

$msg = "";
if (preg_match("/^is (.+)$/i", $message, $arr)) {
    // Get User id
    $uid = $chatBot->get_uid($arr[1]);
    $name = ucfirst(strtolower($arr[1]));
    if (!$uid) {
        $msg = "Player <highlight>$name<end> does not exist.";
		$chatBot->send($msg, $sendto);
    } else {
        //if the player is a buddy then
		$online_status = Buddylist::is_online($name);
		if ($online_status === null) {
			$chatBot->data["ONLINE_MODULE"]['playername'] = $name;
			$chatBot->data["ONLINE_MODULE"]['sendto'] = $sendto;
			Buddylist::add($name, 'is_online');
		} else {
            $db->query("SELECT * FROM org_members_<myname> WHERE `name` = '$name';");
            if ($db->numrows() == 1) {
                $row = $db->fObject();
                if($row->logged_off != "0") {
                    $logged_off = " last seen at ".gmdate("l F d, Y - H:i", $row->logged_off)."(GMT)";
				}
            }
			$main = Alts::get_main($name);
			if($main) $db->query("SELECT * FROM members_<myname> m LEFT JOIN alts a ON a.alt=m.name WHERE a.main='$main' OR m.name='$main' ORDER BY logged_off DESC LIMIT 1;");
			else $db->query("SELECT * FROM members_<myname> WHERE name='$name';");
			if ($db->numrows()!==0) {
				$row = $db->fObject();
                if($row->logged_off != "0") {
                    $logged_off = " last seen <highlight>". Util::unixtime_to_readable(time()-$row->logged_off) ."<end> ago (".gmdate("F d, Y - H:i", $row->logged_off)." GMT)";
					if($row->alt!=NULL) $logged_off .= " on <highlight>{$row->alt}<end>";
					else $logged_off .= " on <highlight>{$row->name}<end>";
				}				
			}
			if ($main) {
				$alts = Alts::get_alts($main);
				$alts[]=$main;
			} else $alts[]=$name;
			foreach($alts as $alt){
				$online_status = Buddylist::is_online($alt);
				if ($online_status){
					if ($alt!=$name) $on.= " $alt";
					else $onmain=true;
				}
			}
            $msg = "Player <highlight>$name<end> is ";
			if($onmain||$on) {
				$msg .= "<green>online<end>";
				if ($onmain&&$on) $on = " {$name}{$on}";
				if ($on) $msg .= " on:<highlight>$on<end>";
			} else $msg .= "<red>offline<end>".$logged_off;
			$chatBot->send($msg, $sendto);
        }
    }
} elseif (($type == "logOn" || $type == "logOff") && $sender == $chatBot->data["ONLINE_MODULE"]['playername']) {
    if ($type == "logOn") {
		$status = "<green>online<end>";
	} else if ($type == "logOff") {
		$status = "<red>offline<end>";
	}
	$msg = "Player <highlight>$sender<end> is $status";
	$chatBot->send($msg, $chatBot->data["ONLINE_MODULE"]['sendto']);
	Buddylist::remove($sender, 'is_online');
	unset($chatBot->data["ONLINE_MODULE"]);
}
?>
