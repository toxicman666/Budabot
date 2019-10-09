<?php

if(Setting::get('alts_wc_members')==1){
	if (preg_match("/^orgs$/i", $message, $arr)) {
		$db->query("SELECT * FROM members_<myname> m LEFT JOIN alts a ON a.alt=m.name WHERE a.alt IS NULL;");
		$orgs = array();
		$not_in_org = array();
		$data = $db->fObject('all');
		foreach($data as $row){
			$whois = Player::get_by_name($row->name);
			if ($whois->guild) $orgs[$whois->guild][]=$whois;
			else $not_in_org[]=$whois;
		}
		ksort($orgs);
		
		$blob = "<header>:::: WC Representatives ::::<end>\n";
		$blob .= "::total orgs: " . count($orgs) . "\n";
		foreach($orgs as $orgname=>$org){
			$blob .= "\n<white>{$orgname}<end> (" . count($org) . ")\n";
			foreach($org as $member){
				$blob .= " {$member->name}";
				if ($member->guild_rank_id==0) $blob .= " <highlight>(Leader)<end>";
				
				$onmain = false;
				$on = false;
				$main = Alts::get_main($member->name);
				if ($main) {
					$alts = Alts::get_alts($main);
					$alts[]=$main;
				} else $alts[]=$member->name;
				foreach($alts as $alt){
					$online_status = Buddylist::is_online($alt);
					if ($online_status){
						if ($alt!=$member->name) $on.= " $alt";
						else $onmain=true;
					}
				}
				if($onmain||$on) {
					$blob .= " <green>Online<end>";
					if ($onmain&&$on) $on = " {$member->name}{$on}";
					if ($on) $blob .= " on:<highlight>$on<end>\n";
					else $blob .= "\n";
				} else $blob .= "\n".$logged_off;	
			}
		}
		if (count($not_in_org)>0){
			$blob .= "\n<highlight>Not in org:<end> (" . count($not_in_org) . ")\n";
			foreach($not_in_org as $member){
				$blob .= " {$member->name}";
			}
		}
		$msg = Text::make_link("WC Representatives",$blob,'blob');
		$chatBot->send($msg,$sendto);
	}
} else {
	$syntax_error = true;
}
?>