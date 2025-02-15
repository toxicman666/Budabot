<?php
   /*
   ** Author: Derroylo (RK2)
   ** Description: Uploads Settings to the db
   ** Version: 0.1
   **
   ** Developed for: Budabot(http://sourceforge.net/projects/budabot)
   **
   ** Date(created): 05.02.2006
   ** Date(last modified): 05.02.2007
   ** 
   ** Copyright (C) 2006, 2007 Carsten Lohmann
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
   
Setting::add('Basic Settings', 'default_module_status', 'Default Status for new Modules', 'edit', "options", $chatBot->settings["default_module_status"], 'ON;OFF', '1;0', 'mod');
Setting::add('Basic Settings', 'max_blob_size', 'Max chars for a window', 'edit', "number", $chatBot->settings["max_blob_size"], '', null, 'mod');

//Upload Settings from the db that are set by modules
$db->query("SELECT * FROM settings_<myname>");
$data = $db->fObject('all');
forEach ($data as $row) {
	$chatBot->settings[$row->name] = $row->value;
}

?>