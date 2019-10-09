<?php
   /*
   ** Author: Derroylo (RK2)
   ** Description: Creates a Doc Assist Macro
   ** Version: 1.0
   **
   ** Developed for: Budabot(http://sourceforge.net/projects/budabot)
   **
   ** Date(created): 05.06.2006
   ** Date(last modified): 05.06.2006
   ** 
   ** Copyright (C) 2006 Carsten Lohmann
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

if (preg_match("/heal$/i", $message)) {
	$healass=Setting::get('healassist');
  	if ($healass!="") {
		$arr=explode(" ", $healass);
		if(count($arr)==1){
			$link = "<header>::::: Healassist Macro on $healass:::::\n\n";
			$link .= "<a href='chatcmd:///macro HEAL /assist $healass'>Click here to make a heal assist macro on $healass</a>";
			$msg = Text::make_link("Current Healassist is $healass", $link);
		} else {
			$msg = "<font color=\"#FFFF00\">/macro HEAL /assist " . $arr[0];
			for($i=1;$i<count($arr);$i++) $msg.= "\\n /assist " . $arr[$i];
			$msg.="</font>";
		}
	} else {
		$chatBot->send("No Healassist set.", $sendto);
	}
	if($msg!=""){
		if($chatBot->data["leader"]==$sender){
			$chatBot->send($msg, 'priv');
			$chatBot->send($msg, 'priv');
			$chatBot->send($msg, 'priv');
		} else {
			$chatBot->send($msg, $sendto);
		}
	}
} else {
	$syntax_error = true;
}
?>