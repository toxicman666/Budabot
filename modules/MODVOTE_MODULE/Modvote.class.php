<?php

class Modvote {
	public static function get_entrants($bot){
		$db = DB::get_instance();
		$db->query("SELECT * FROM mod_entrants WHERE bot='{$bot}';");
		$entrants=array();
		$i=0;
		while ($row=$db->fObject()){
			$entrants[$i]["name"]=$row->char;
			$entrants[$i]["sponsor"]=$row->sponsor;
			$entrants[$i]["org"]=$row->org;
			$entrants[$i]["id"]=$row->id;
			$i++;
		}
		return $entrants;
	}
	public static function add_entrant($bot,$guild,$mod=null,$sponsor=null){
		$db = DB::get_instance();
		$db->query("SELECT * FROM mod_entrants WHERE bot='{$bot}' AND org='{$guild}';");
		if ($db->numrows()>0) {
			$row = $db->fObject();
			$mod_id = $row->id;
			$db->exec("DELETE FROM mod_entrants WHERE id={$mod_id};");
			$db->exec("DELETE FROM mod_votes WHERE mod_id={$mod_id};");
		}
		
		if ($mod!==null)
			$db->exec("INSERT INTO mod_entrants (`char`,`bot`,`sponsor`,`org`) VALUES('{$mod}','{$bot}','{$sponsor}','{$guild}');");
	}
	public static function display_vote($sn=false){
		global $chatBot;
		$db = DB::get_instance();
		if ($sn!==false) {
			$main = Alts::get_main($sn);
			$db->query("SELECT e.*,v.main,v.mod_id,v.vote FROM mod_entrants e LEFT JOIN (SELECT mod_id,main,vote FROM mod_votes WHERE main='{$main}') v ON e.id=v.mod_id ORDER BY e.bot ASC, e.char ASC;");
		} else {
			$db->query("SELECT e.* FROM mod_entrants e ORDER BY e.bot ASC, e.char ASC;");
		}
		

		$blob = "<header>:::: Votes for new Moderators ::::<end>\n";
		$blob .= "  | " . Text::make_link("Refresh","/tell <myname> !modvote",'chatcmd') . " | " . Text::make_link("History","/tell <myname> !modvote history",'chatcmd') . " | " . Text::make_link("Help","/tell <myname> !help modvote",'chatcmd') . " | " . Text::make_link("Info","/tell <myname> !help mods",'chatcmd') . " |\n";
		$vote_end = intval(Setting::get('mod_vote_end_time'));
		$left = Util::unixtime_to_readable($vote_end-time());
		$blob .= "Vote will end at: " . gmdate('j/M/y G:i',$vote_end) . " GMT\n({$left} from now)\n\n";
		$unvoted = false;
		while ($row=$db->fObject()){
			$blob .= "[<green>{$row->bot}<end>] <white>{$row->char}<end> ";
			$blob .= Text::make_link("yes","/tell <myname> modvote {$row->bot} {$row->char} 1",'chatcmd');
			$blob .= " ";
			$blob .= Text::make_link("no","/tell <myname> modvote {$row->bot} {$row->char} 0",'chatcmd');
			if ($row->vote!==null) {
				if ($row->vote==1) $blob .= " <green>(yes)<end>";
				else $blob .= " <red>(no)<end>";
			} else {
				$unvoted = true;
			}
			$blob .= "\n";
			$blob .= ":: by <highlight>{$row->sponsor}<end> from <highlight>{$row->org}<end>\n";	
		}
		$blob .= "\n<highlight>in order to add a mod for your org do:<end>\n<white>/tell <myname> mod 'bot' 'newmod'<end>";
		
		$msg = Text::make_link("Votes for bot Moderators",$blob,'blob');
		
		if ($sn===false) {
			$msg .= " :: <yellow>{$left} left<end>";
			$chatBot->send($msg,'priv');
		} else {
			if ($unvoted) $msg .= " <red>You are yet to complete the vote!<end> (Please cast your vote for each entrant)";
			$chatBot->send($msg,$sn);
		}
	}
	public static function cast_vote($main,$guild,$mod_id,$vote){
		$db = DB::get_instance();
		$db->exec("DELETE FROM mod_votes WHERE main='{$main}' AND mod_id={$mod_id};");
		$db->exec("INSERT INTO mod_votes (`main`,`org`,`mod_id`,`vote`) VALUES ('{$main}','{$guild}',{$mod_id},{$vote})");
	}
	public static function start_vote(){
		global $chatBot;
		$new_end = time()+259200;
		Setting::save('mod_vote_end_time',$new_end);
		Setting::save('mod_vote_in_progress',1);
		$chatBot->send("<font color=\"#FF6666\">Starting vote for new bot Moderators. You have 3 days to cast your votes.</font>",'priv');
		Modvote::display_vote();
	}
	public static function end_vote(){
		global $chatBot;
		
		$blob = "<header>:::: Moderators vote winners ::::<end>\n\n";
		$blob .= "<highlight>:: [bot] : moderator : votes %<end>\n";
		
		$db = DB::get_instance();
		$db->query("SELECT * FROM (SELECT mod_id,COUNT(votes) AS count_votes ,COALESCE(SUM(votes)/COUNT(votes),0) AS votes_format FROM (SELECT *,SUM(vote)/COUNT(vote) AS votes FROM (SELECT av.* FROM mod_votes av LEFT JOIN mod_entrants ae on av.mod_id=ae.id WHERE ae.bot='taratime') a GROUP BY a.org,a.mod_id) v GROUP BY v.mod_id ORDER BY votes_format DESC, count_votes DESC) tt JOIN mod_entrants ee ON tt.mod_id=ee.id WHERE tt.votes_format>0.5 AND tt.count_votes>=6 LIMIT 5;");
		$taratime_winners = $db->fObject('all');
		if (count($taratime_winners)>=2){
			$tara_str = "";
			foreach($taratime_winners as $winner){
				$votes = intval(100 * $winner->votes_format);
				$db->exec("INSERT INTO mod_history (`time`,`mod`,`bot`,`sponsor`,`org`,`votes`) VALUES(" . time() . ",'{$winner->char}','{$winner->bot}','{$winner->sponsor}','{$winner->org}',{$votes});");
				$tara_str .= " " . $winner->char;
				$blob .= "[<green>{$winner->bot}<end>] <white>{$winner->char}<end> {$votes}%\n";
			}
			$chatBot->send("!replacemods" . $tara_str,"Taratime");
		} else {
			$blob .= "<orange>Not enough winners found for Taratime. Old moderators stay<end>";
		}
		$blob .= "\n";
		
		$db->query("SELECT * FROM (SELECT mod_id,COUNT(votes) AS count_votes ,COALESCE(SUM(votes)/COUNT(votes),0) AS votes_format FROM (SELECT *,SUM(vote)/COUNT(vote) AS votes FROM (SELECT av.* FROM mod_votes av LEFT JOIN mod_entrants ae on av.mod_id=ae.id WHERE ae.bot='warbot') a GROUP BY a.org,a.mod_id) v GROUP BY v.mod_id ORDER BY votes_format DESC, count_votes DESC) tt JOIN mod_entrants ee ON tt.mod_id=ee.id WHERE tt.votes_format>0.5 AND tt.count_votes>=6 LIMIT 5;");
		$warbot_winners = $db->fObject('all');
		if (count($warbot_winners)>=2){
			$warbot_str = "";
			foreach($warbot_winners as $winner){
				$votes = intval(100 * $winner->votes_format);
				$db->exec("INSERT INTO mod_history (`time`,`mod`,`bot`,`sponsor`,`org`,`votes`) VALUES(" . time() . ",'{$winner->char}','{$winner->bot}','{$winner->sponsor}','{$winner->org}',{$votes});");
				$warbot_str .= " " . $winner->char;
				$blob .= "[<green>{$winner->bot}<end>] <white>{$winner->char}<end> {$votes}%\n";
			}
			$chatBot->send("!replacemods" . $warbot_str,"Warbot");
			$chatBot->send("!replacemods" . $warbot_str,"Twinkbot");
		} else {
			$blob .= "<orange>Not enough winners found for Warbot. Old moderators stay<end>";
		}
		
		$vote_end = intval(Setting::get('mod_vote_end_time'));
		$next_vote = "<white>Next vote will be available at " . gmdate('j/M/y G:i',$vote_end+4838400-259200) . "<end>";
		$blob .= "\n\n" . $next_vote;
		
		$db->exec("DELETE FROM mod_votes;");
		Setting::save('mod_vote_in_progress',0);
		
		$chatBot->send("<font color=\"#FF6666\">BEEP BEEP BEEP BEEP BEEP BEEP BEEP!!!</font>",'priv');
		$chatBot->send(Text::make_link("Moderators vote Results",$blob,'blob') . " " . $next_vote,'priv');
		$chatBot->send("<yellow>All bot Moderators have been replaced with the winners.<end>",'priv');
	}
}

?>