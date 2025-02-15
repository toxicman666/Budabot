<?php
   /*
   ** Author: Sebuda (RK2)
   ** Description: Uploads banned players to the local var
   ** Version: 0.1
   **
   ** Developed for: Budabot(http://sourceforge.net/projects/budabot)
   **
   ** Date(created): 21.01.2006
   ** Date(last modified): 10.12.2006
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

$db->query("CREATE TABLE IF NOT EXISTS banlist (name VARCHAR(25) NOT NULL PRIMARY KEY, admin VARCHAR(25), time INT, reason TEXT, banend INT, char_id INT, org_ban BOOLEAN)");

$db->query("CREATE TABLE IF NOT EXISTS banhistory (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, name VARCHAR(25), admin VARCHAR(25), time INT, reason TEXT, banend INT, length INT, char_id INT, org_ban BOOLEAN, wasbannedby VARCHAR(25) DEFAULT NULL)");

Ban::upload_banlist();

?>