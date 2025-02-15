<?php
/*
 ** Author: Mindrila (RK1)
 ** Description: Function file for the ONLINE_MODULE
 ** Version: 1.0
 **
 ** Under BudaBot's license.
 */

function get_online_list($prof = "all") {
	$db = DB::get_instance();
	global $chatBot;
	
	if ($prof != 'all') {
		$prof_query = "AND `profession` = '$prof'";
	}
	
	if (Setting::get('online_group_by') == 'profession') {
		$order_by = "ORDER BY `profession`, `level` DESC";
	} else if (Setting::get('online_group_by') == 'guild') {
		$order_by = "ORDER BY `channel` ASC, `name` ASC";
	}

	$list = "";
	$db->query("SELECT p.*, o.name, o.channel, o.afk FROM `online_<myname>` o LEFT JOIN players p ON o.name = p.name WHERE o.channel_type = 'guild' {$prof_query} {$order_by}");

	$oldprof = "";
	$numonline = $db->numrows();
	if ($chatBot->vars['my_guild'] != '') {
		$guild_name = "[<myguild>] ";
	}
	if ($numonline == 1) {
		$list .= "<header>::::: 1 member online $guild_name:::::<end>\n";
	} else {
		$list .= "<header>::::: $numonline members online $guild_name:::::<end>\n";
	}
	$data = $db->fObject("all");
	// create the list with alts shown
	createList($data, $list, true);

	// Private Channel Part
	$db->query("SELECT p.*, o.name, o.channel, o.afk FROM `online_<myname>` o LEFT JOIN players p ON o.name = p.name WHERE o.channel_type = 'priv' {$prof_query} {$order_by}");

	$numguest = $db->numrows();
	if ($numguest == 1) {
		$list .= "\n\n<highlight><u>1 User in Private Channel<end></u>\n";
	} else {
		$list .= "\n\n<highlight><u>$numguest Users in Private Channel<end></u>\n";
	}
	$data = $db->fObject("all");
	// create the list of guests, without showing alts
	createList($data, $list, true);
	$numonline += $numguest;

	if ($numonline == 1) {
		$msg .= "1 member online";
	} else {
		$msg .= "{$numonline} members online";
	}

	// BBIN part
	if (Setting::get("bbin_status") == 1) {
		// members
		$db->query("SELECT * FROM bbin_chatlist_<myname> WHERE (`guest` = 0) {$prof_query} ORDER BY `profession`, `level` DESC");
		$numbbinmembers = $db->numrows();
		$data = $db->fObject("all");
		if ($numbbinmembers == 1) {
			$list .= "\n\n<highlight><u>1 member in BBIN<end></u>\n";
		} else {
			$list .= "\n\n<highlight><u>$numbbinmembers members in BBIN<end></u>\n";
		}
		createListByProfession($data, $list, false);
		
		// guests
		$db->query("SELECT * FROM bbin_chatlist_<myname> WHERE (`guest` = 1) {$prof_query} ORDER BY `profession`, `level` DESC");
		$numbbinguests = $db->numrows();
		$data = $db->fObject("all");
		if ($numbbinguests == 1) {
			$list .= "\n\n<highlight><u>1 guest in BBIN<end></u>\n";
		} else {
			$list .= "\n\n<highlight><u>$numbbinguests guests in BBIN<end></u>\n";
		}
		createListByProfession($data, $list, false);
		
		$numonline += $numbbinguests + $numbbinmembers;
		
		$msg .= " <green>BBIN<end>:".($numbbinguests + $numbbinmembers)." online";
	}

	return array ($numonline, $msg, $list);
}

function createList(&$data, &$list, $show_alts) {
	if (Setting::get('online_group_by') == 'profession') {
		return createListByProfession($data, $list, $show_alts);
	} else if (Setting::get('online_group_by') == 'guild') {
		return createListByChannel($data, $list, $show_alts);
	}
}

function createListByChannel(&$data, &$list, $show_alts) {
	$db = DB::get_instance();

	$oldprof = "";
	forEach ($data as $row) {
		$name = Text::make_link($row->name, "/tell $row->name", "chatcmd");
		 
		if ($row->profession == "") {
			$row->profession = "Unknown";
		}
		
		if ($current_channel != $row->channel) {
			$list .= "\n<tab><highlight>$row->channel<end>\n";
			$current_channel = $row->channel;
		}

		if ($row->afk == "kiting") {
			$afk = " <highlight>::<end> <red>KITING<end>";
		} else if ($row->afk == '1') {
			$afk = " <highlight>::<end> <red>AFK<end>";
		} else if ($row->afk != '') {
			$afk = " <highlight>::<end> <red>AFK - {$row->afk}<end>";
		} else {
			$afk = "";
		}
		
		if ($row->profession == "Unknown") {
			$list .= "<tab><tab><highlight>$name<end> - Unknown\n";
		} else {
			if ($show_alts == true) {
				$db->query("SELECT * FROM alts WHERE `alt` = '$row->name'");
				if ($db->numrows() == 0) {
					$alt = "<highlight>::<end> <a href='chatcmd:///tell <myname> alts $row->name'>Alts</a>";
				} else {
					$row1 = $db->fObject();
					$alt = "<highlight>::<end> <a href='chatcmd:///tell <myname> alts $row->name'>Alts of $row1->main</a>";
				}
					
				if ($row->guild == "") {
					$guild = "Not in a guild";
				} else {
					$guild = $row->guild." (<highlight>$row->guild_rank<end>)";
				}
				$list .= "<tab><tab><highlight>$name<end> (Lvl $row->level/<green>$row->ai_level<end>) <highlight>::<end> $guild$afk $alt\n";
			} else {
				if ($row->guild == "") {
					$guild = "Not in a guild";
				} else {
					$guild = $row->guild;
				}
				$list .= "<tab><tab><highlight>$name<end> (Lvl $row->level/<green>$row->ai_level<end>) <highlight>::<end> $guild$afk\n";
			}
		}
	}
}

function createListByProfession(&$data, &$list, $show_alts) {
	$db = DB::get_instance();

	$current_profession = "";
	forEach ($data as $row) {
		$name = Text::make_link($row->name, "/tell $row->name", "chatcmd");
		 
		if ($row->profession == "") {
			$row->profession = "Unknown";
		}
		
		if ($current_profession != $row->profession) {
			if (Setting::get("fancy_online") == 0) {
				// old style delimiters
				$list .= "\n<tab><highlight>$row->profession<end>\n";
				$current_profession = $row->profession;
			} else {
				// fancy delimiters
				$list .= "\n<img src=tdb://id:GFX_GUI_FRIENDLIST_SPLITTER>\n";
				if (Setting::get("icon_fancy_online") == 1) {
					if ($row->profession == "Adventurer")
						$list .= "<img src=rdb://84203>";
					else if ($row->profession == "Agent")
						$list .= "<img src=rdb://16186>";
					else if ($row->profession == "Bureaucrat")
						$list .= "<img src=rdb://46271>";
					else if ($row->profession == "Doctor")
						$list .= "<img src=rdb://44235>";
					else if ($row->profession == "Enforcer")
						$list .= "<img src=rdb://117926>";
					else if ($row->profession == "Engineer")
						$list .= "<img src=rdb://16307>";
					else if ($row->profession == "Fixer")
						$list .= "<img src=rdb://16300>";
					else if ($row->profession == "Keeper")
						$list .= "<img src=rdb://38911>";
					else if ($row->profession == "Martial Artist")
						$list .= "<img src=rdb://16289>";
					else if ($row->profession == "Meta-Physicist")
						$list .= "<img src=rdb://16283>";
					else if ($row->profession == "Nano-Technician")
						$list .= "<img src=rdb://45190>";
					else if ($row->profession == "Soldier")
						$list .= "<img src=rdb://16195>";
					else if ($row->profession == "Shade")
						$list .= "<img src=rdb://39290>";
					else if ($row->profession == "Trader")
						$list .= "<img src=rdb://118049>";
					else {
						// TODO need unknown icon
						$list .= "";
					}
				}
				$list .= " <highlight>$row->profession<end>";
				$current_profession = $row->profession;

				$list .= "\n<img src=tdb://id:GFX_GUI_FRIENDLIST_SPLITTER>\n";
			}
		}

		if ($row->afk == "kiting") {
			$afk = " <highlight>::<end> <red>KITING<end>";
		} else if ($row->afk == '1') {
			$afk = " <highlight>::<end> <red>AFK<end>";
		} else if ($row->afk != '') {
			$afk = " <highlight>::<end> <red>AFK - {$row->afk}<end>";
		} else {
			$afk = "";
		}
		
		if ($row->profession == "Unknown") {
			$list .= "<tab><tab><highlight>$name<end> - Unknown\n";
		} else {
			if ($show_alts == true) {
				$db->query("SELECT * FROM alts WHERE `alt` = '$row->name'");
				if ($db->numrows() == 0) {
					$alt = "<highlight>::<end> <a href='chatcmd:///tell <myname> alts $row->name'>Alts</a>";
				} else {
					$row1 = $db->fObject();
					$alt = "<highlight>::<end> <a href='chatcmd:///tell <myname> alts $row->name'>Alts of $row1->main</a>";
				}
					
				if ($row->guild == "") {
					$guild = "Not in a guild";
				} else {
					$guild = $row->guild." (<highlight>$row->guild_rank<end>)";
				}
				$list .= "<tab><tab><highlight>$name<end> (Lvl $row->level/<green>$row->ai_level<end>) <highlight>::<end> $guild$afk $alt\n";
			} else {
				if ($row->guild == "") {
					$guild = "Not in a guild";
				} else {
					$guild = $row->guild;
				}
				$list .= "<tab><tab><highlight>$name<end> (Lvl $row->level/<green>$row->ai_level<end>) <highlight>::<end> $guild$afk\n";
			}
		}
	}
}

?>