<?php
$group_id_150 = 7; // 150+
$group_id_tara = 6; // tara
$role_id = 2;

if (preg_match("/^register$/i", $message)) {
	unset($chatBot->data["REGISTER_MODULE"][$sender]);
	// check if registered
	if (Tara::is_registered($sender)===true){
		$chatBot->send("You are already registered",$sendto);
		return;		
	}
	$account = Tara::get_account_name($sender);
	$replace="";
	if($account){
		if($account == $sender){
			$db->query("SELECT * FROM alts WHERE alt='$sender' AND approved=0;");
			if ($db->numrows()>0){
				// check if ever was in same raid
				$account = Alts::get_main($sender);
				$db->query("SELECT * FROM (SELECT * FROM tara_points_history WHERE account='$sender') p1 JOIN (SELECT * FROM tara_points_history WHERE account='$account') p2 ON p1.raid_id=p2.raid_id;");
				if($db->numrows()>0){
					$chatBot->send("<orange>This alt cannot be registered due to requirements set by rule 2.2: Two characters which have ever received points at the same raid cannot be registered as alts of the same player.<end>",$sendto);
					return;
				}
				// delete old acc and register as alt
				$replace = $sender;
			} else {
				// means that it's main and already registered
				$chatBot->send("You are already registered",$sendto);
				return;
			}
		}
	}
	$main = Alts::get_main($sender);
	if ($main!=$sender){
		if (Tara::is_registered($main)!==true){
			$blob = "<header>:::: If you don't want to register as an alt ::::<end>\n\n";
			$blob .= "1) " . Text::make_link("Remove me from alts of {$account}","/tell <myname> !alts rem {$sender}",'chatcmd') . "\n";
			$blob .= "2) " . Text::make_link("/tell <myname> !register","/tell <myname> !register",'chatcmd');
			$title = "You are about to <white>permanently<end> add an <yellow>alt<end> to <highlight>$account<end> (" . Text::make_link("not your main?",$blob,'blob') . ") with <myname>.";	
			$msg = "<orange>Your main {$main} is not registered, you must register on main first. <end> (" . Text::make_link("not your main?",$blob,'blob') . ")";
			$chatBot->send($msg,$sendto);
			return;
		}
		$account=$main;
	}
	
	// check faction/level
	$whois = Player::get_by_name($sender);
	if ($whois->faction == 'clan') {
		$chatBot->send("<orange>Clans can not register in this bot.<end>",$sendto);
		return;
	}
	if (($whois->level < 100)||($whois->level < 150 && $account===null)) {
		$chatBot->send("<orange>You are required to be level 150+ to register main and 100+ to register alt.<end>",$sendto);
		return;
	}
	
	// check if registered on forums in 150+ group (if not registered on other toon)
	if ($account===null) {
		$sql = "SELECT ag.id, jo.email FROM omnihqdb.jos_users jo JOIN omnihqdb.jos_agora_users ag ON jo.username = ag.username LEFT JOIN omnihqdb.jos_agora_user_group gr ON gr.user_id=ag.id WHERE jo.name='$sender' AND gr.group_id = $group_id_150 AND gr.role_id!=1;";
		$db->query($sql);
		if ($db->numrows() == 0) {
			$blob = ":::: <yellow>How to register at OmniHQ<end> ::::\n\nIf you are already registered, log the toon you registered with or try " . Text::make_link("updating your info","/tell <myname> update",'chatcmd') . "\n\nGo to " . Text::make_link("www.omnihq.net","/start http://www.omnihq.net/component/user/?task=register",'chatcmd') . " and follow instructions to create account\n<white>(Note that the Display Name has to be exact name of your main character)<end>\n\nActivate account AND log in\n\n" . Text::make_link("Confirm account ingame","/tell <myname> !confirm",'chatcmd') . "\n\n" . Text::make_link("Register with Taratime","/tell <myname> !register",'chatcmd') . "\n\n<white>Note: if you register multiple accounts and get extra points on registration, then decide to merge accounts, your extra points will be deducted from alt.";
			$msg = "<orange>You are not registered at www.omnihq.net.<end> Forum registration gives you extra 20 points to start with. (" . Text::make_link("How?",$blob,'blob') . ")";
			$chatBot->send($msg,$sender);
		}
	}
	
	if ($account!==null) {
		$chatBot->data["REGISTER_MODULE"][$sender]["acc"]=$account;
		$blob = "<header>:::: If you don't want to register as an alt ::::<end>\n\n";
		$blob .= "1) " . Text::make_link("Remove me from alts of {$account}","/tell <myname> !alts rem {$sender}",'chatcmd') . "\n";
		$blob .= "2) " . Text::make_link("/tell <myname> !register","/tell <myname> !register",'chatcmd');
		$title = "You are about to <white>permanently<end> add an <yellow>alt<end> to <highlight>$account<end> (" . Text::make_link("not your main?",$blob,'blob') . ").";
		if ($replace!="") {
			$chatBot->data["REGISTER_MODULE"][$sender]["replace"]=$replace;
			$title .= " This will add your points to <highlight>$account<end> account.";
		}
	} else {
		$chatBot->data["REGISTER_MODULE"][$sender]["acc"]=1;
		$title = "You are about to register with <myname>.";
	}
	
	$rules = file_get_contents("./modules/REGISTER_MODULE/rules.txt");
	$rules .= "\n\n" . Text::make_link("I Accept","/tell <myname> register accept",'chatcmd');
	
	$msg = $title . " " . Text::make_link("Read the rules carefully",$rules,'blob') . " and hit <yellow>Accept<end> in the bottom.";
	$chatBot->send($msg, $sender);

} else if (preg_match("/^register accept$/i", $message)) {
	$forum=0;
	if(!isset($chatBot->data["REGISTER_MODULE"][$sender])){
		$chatBot->send("You need to read rules first. /tell <myname> register",$sendto);
	} else {
		if($chatBot->data["REGISTER_MODULE"][$sender]["acc"]!=1){
			$main = Alts::get_main($sender);
			if($chatBot->data["REGISTER_MODULE"][$sender]["acc"]!=$main){
				$chatBot->send("<orange>Error! /tell <myname> !register<end>",$sendto);
				return;
			}
			if(isset($chatBot->data["REGISTER_MODULE"][$sender]["replace"])){
				$db->query("SELECT * FROM tara_points WHERE name='" . $chatBot->data["REGISTER_MODULE"][$sender]["replace"] . "';");
				if($db->numrows()==0){
					$chatBot->send("Error! " . $chatBot->data["REGISTER_MODULE"][$sender]["replace"] . " is not in DB.",$sendto);
					return;
				}
				$row = $db->fObject();
				$old_points = $row->points;
				$new_account = Alts::get_main($sender);
				if ($new_account==$sender){
					$chatBot->send("<orange>Fatal Error!<end>");
					return;
				}
				$account = Tara::get_account_name($chatBot->data["REGISTER_MODULE"][$sender]["replace"]);
				if(!$account || $account==""){
					$chatBot->send("<orange>Fatal Error! Account {$account} not found.<end>");
					return;				
				}
				$db->exec("UPDATE tara_points SET points=points+{$old_points} WHERE name='{$new_account}';");
				$db->exec("DELETE FROM tara_points WHERE name='{$account}';"); // delete old point acc
				$db->exec("UPDATE tara_points_history SET account='{$new_account}' WHERE account='{$account}';");
				// get forum acc
				$sql = "SELECT ag.id, jo.email, orgname, orgrank FROM omnihqdb.jos_users jo JOIN omnihqdb.jos_agora_users ag ON jo.username = ag.username WHERE jo.name = '{$account}';";
				$db->query($sql);
				if ($db->numrows()>0){
					$row = $db->fObject();
					$ag_id = $row->id;				
					$db->exec("DELETE FROM omnihqdb.jos_agora_user_group WHERE user_id={$ag_id} AND group_id!=1;"); // delete from forums on old name
				}
			}			
			$db->exec("UPDATE alts SET approved=1 WHERE alt LIKE '{$sender}';");
			$msg = "You were successfully registered <highlight>as an alt of " . $chatBot->data["REGISTER_MODULE"][$sender]["acc"] . "<end>.";
		} else {
			$main = Alts::get_main($sender);
			if($sender!=$main){
				$chatBot->send("<orange>Error! /tell <myname> !register<end>",$sendto);
				return;
			} else {
				$msg = "You were successfully registered. If you wish to add alts to this account, use '!alts add <name>', then '!register' from the alt.";
				$sql = "SELECT ag.id, jo.email FROM omnihqdb.jos_users jo JOIN omnihqdb.jos_agora_users ag ON jo.username = ag.username LEFT JOIN omnihqdb.jos_agora_user_group gr ON gr.user_id=ag.id WHERE jo.name='$sender' AND gr.group_id = $group_id_150 AND gr.role_id!=1;";
				$db->query($sql);
				if ($db->numrows()>0){
					$row = $db->fObject();
					$ag_id = $row->id;					
					$db->exec("INSERT INTO omnihqdb.jos_agora_user_group (`user_id`, `group_id`, `role_id`) VALUES ($ag_id, $group_id_tara, $role_id);");
					$forum=1;
				}
				$db->exec("INSERT INTO tara_points (name,forums) VALUES ('{$sender}',{$forum});");
			}
		}
		$db->query("SELECT * FROM alts WHERE alt LIKE '{$sender}';");
		if ($db->numrows()!==0) $db->exec("UPDATE `alts` SET `approved`=1 where `alt` LIKE '{$sender}';");
		
		$chatBot->send($msg,$sendto);
		if ($forum==1){
			$chatBot->send("<white>You were added to Taratime section at www.omnihq.net and awarded 20 bonus points.<end>",$sender);
		}
		unset($chatBot->data["REGISTER_MODULE"][$sender]);
	}
} else {
	$syntax_error = true;
}

?>
