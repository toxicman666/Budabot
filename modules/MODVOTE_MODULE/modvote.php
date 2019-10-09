<?php

if (preg_match("/^modvote (taratime|warbot) ([a-z0-9-]+) (0|1)$/i", $message, $arr)) {
	$vote_end = intval(Setting::get('mod_vote_end_time'));
	if($vote_end<time() || $vote_end-time()>259200) {
		$chatBot->send("Next vote will be available at " . gmdate('j/M/y G:i',$vote_end+4838400-259200),$sendto);
		return;
	}
	
	$bot = ucfirst(strtolower($arr[1]));
	$newmod = ucfirst(strtolower($arr[2]));
	$vote = intval($arr[3]);
	
	$main = Alts::get_main($sender);
	$whois = Player::get_by_name($main);
	if ($whois === null) {
	    $msg = "<orange>Unable to get your main ($main) character info. Please try again later.<end>";
	    $chatBot->send($msg, $sender);
	    return;
	}
	
	$entrants = Modvote::get_entrants($bot);
	$mod_id = false;
	foreach($entrants as $entrant){
		if ($entrant["name"]==$newmod) {
			$mod_id = $entrant["id"];
			continue;
		}
	}
	if ($mod_id!==false) {
		Modvote::cast_vote($main,$whois->guild,$mod_id,$vote);
		$chatBot->send("You have voted on <highlight>{$newmod}<end> for <highlight>{$bot}<end>",$sendto);
	} else {
		$chatBot->send("<highlight>{$newmod}<end> is not in the list.",$sendto);
	}
} else if (preg_match("/^modvote history$/i", $message, $arr)) {
	$db = DB::get_instance();
	$db->query("SELECT * FROM mod_history ORDER BY id DESC, bot ASC LIMIT 30;");
	if ($db->numrows()==0){
		$chatBot->send("No vote history yet.",$sendto);
		return;
	}
	$blob = "<header>:::: Moderator vote winners history ::::<end>\n";
	$vote_end = intval(Setting::get('mod_vote_end_time'));
	if($vote_end<time() || $vote_end-time()>259200) {
		$blob .= "<white>Next vote will be available at " . gmdate('j/M/y G:i',$vote_end+4838400-259200) . "<end>\n\n";
	} else {
		$left = Util::unixtime_to_readable($vote_end-time());
		$blob .= "Vote will end at: " . gmdate('j/M/y G:i',$vote_end) . " GMT\n({$left} from now)\n\n";
	}

	$blob .= ": Last 30 winners\n";
	$blob .= "<highlight>::   date : [bot] : moderator : votes %<end>\n";
	while ($row = $db->fObject()){
		$blob .= gmdate('j/M/y',$row->time) . " [<green>{$row->bot}<end>] <white>{$row->mod}<end> {$row->votes}%\n";
	}
	$msg = Text::make_link("Moderator vote winners history",$blob, 'blob');
	$chatBot->send($msg,$sendto);
	
} else if (preg_match("/^modvote$/i", $message, $arr)) {
	$vote_end = intval(Setting::get('mod_vote_end_time'));
	if($vote_end<time() || $vote_end-time()>259200) {
		$next_vote = "Next vote will be available at " . gmdate('j/M/y G:i',$vote_end+4838400-259200) . " GMT";
		
		$db->query("SELECT e.* FROM mod_entrants e ORDER BY e.bot ASC, e.char ASC;");

		$blob = "<header>:::: Entrants for next Moderators Vote ::::<end>\n";
		$blob .= "  | " . Text::make_link("Refresh","/tell <myname> !modvote",'chatcmd') . " | " . Text::make_link("History","/tell <myname> !modvote history",'chatcmd') . " | " . Text::make_link("Help","/tell <myname> !help modvote",'chatcmd') . " | " . Text::make_link("Info","/tell <myname> !help mods",'chatcmd') . " |\n";

		$blob .= $next_vote . "\n\n";

		while ($row=$db->fObject()){
			$blob .= "[<green>{$row->bot}<end>] <white>{$row->char}<end>\n";
			$blob .= ":: by <highlight>{$row->sponsor}<end> from <highlight>{$row->org}<end>\n";	
		}
		$blob .= "\n<highlight>in order to add a mod for your org do:<end>\n<white>/tell <myname> mod 'bot' 'newmod'<end>";
		
		$msg = Text::make_link("Entrants for next Moderators Vote",$blob,'blob');
		$msg .= " - " . $next_vote;
		$chatBot->send($msg,$sendto);
	} else {
		if ($sendto=='prv') Modvote::display_vote();
		Modvote::display_vote($sender);
	}
} else {
	$syntax_error = true;
}

?>