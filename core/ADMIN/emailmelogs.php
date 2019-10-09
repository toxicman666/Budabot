<?php

if ($message && !preg_match("/^emailmelogs$/i", $message)) {
	$syntax_error = true;
	return;
}

// find email
$found = false;
$group_id_confirmed = 4;  // confirmed users forum group
$main = Alts::get_main($sender);

$sql = "SELECT ag.id, ag.email FROM omnihqdb.jos_users jo JOIN omnihqdb.jos_agora_users ag ON jo.username = ag.username LEFT JOIN omnihqdb.jos_agora_user_group gr ON gr.user_id=ag.id WHERE jo.name='$main' AND gr.group_id = $group_id_confirmed;";
$db->query($sql);
if ($db->numrows() > 0) {
	$row = $db->fObject();
	$found = true;
} else {
	$alts = Alts::get_alts($main);
	if(count($alts)>0) foreach($alts as $alt){
		if ($alt==$sender) continue;
		$sql = "SELECT ag.id, ag.email FROM omnihqdb.jos_users jo JOIN omnihqdb.jos_agora_users ag ON jo.username = ag.username LEFT JOIN omnihqdb.jos_agora_user_group gr ON gr.user_id=ag.id WHERE jo.name='$alt' AND gr.group_id = $group_id_confirmed;";
		$db->query($sql);
		if ($db->numrows() > 0) {
			$row = $db->fObject();
			$found = true;
			continue;
		}
	}
}

if (!$found){
	$chatBot->send("You do not have a confirmed forum account.",$sendto);
	return;
}
$email = $row->email;

//get a log filename
$today =  date("YM");
$fullfilename = "./logs/" . $chatBot->vars["name"] . "." . $chatBot->vars['dimension'] . "/{$today}.CHAT.log";
$filename = "{$today}.CHAT.log";

//define the receiver of the email
$to = $email;
//define the subject of the email
$subject = $chatBot->vars["name"] . " logs " . gmdate("Ymd H:i");
//create a boundary string. It must be unique
//so we use the MD5 algorithm to generate a random hash
$random_hash = md5(date('r', time()));
//define the headers we want passed. Note that they are separated with \r\n
$headers = "From: admin@omnihq.net\r\nReply-To: admin@omnihq.net";
//add boundary string and mime type specification
$headers .= "\r\nContent-Type: multipart/mixed; boundary=\"PHP-mixed-".$random_hash."\"";
//read the atachment file contents into a string,
//encode it with MIME base64,
//and split it into smaller chunks
$attachment = chunk_split(base64_encode(file_get_contents($fullfilename)));
//define the body of the message.
ob_start(); //Turn on output buffering
?>
--PHP-mixed-<?php echo $random_hash; ?> 
Content-Type: multipart/alternative; boundary="PHP-alt-<?php echo $random_hash; ?>"

--PHP-alt-<?php echo $random_hash; ?> 
Content-Type: text/plain; charset=us-ascii
Content-Transfer-Encoding: 7bit

Logs attached.

--PHP-alt-<?php echo $random_hash; ?> 
Content-Type: text/html; charset=us-ascii
Content-Transfer-Encoding: 7bit

<p>Logs attached.</p>

--PHP-alt-<?php echo $random_hash; ?>--

--PHP-mixed-<?php echo $random_hash; ?> 
Content-Type: text/plain; name="<?php echo $filename; ?>" 
Content-Transfer-Encoding: base64 
Content-Disposition: attachment 

<?php echo $attachment; ?>
--PHP-mixed-<?php echo $random_hash; ?>--

<?php
//copy current buffer contents into $message variable and delete current output buffer
$message = ob_get_clean();
//send the email
$mail_sent = @mail( $to, $subject, $message, $headers );
//if the message is sent successfully print "Mail sent". Otherwise print "Mail failed"
if ($mail_sent) {
	$msg = "Mail sent to " . $email;
} else {
	$msg = "Mail failed to " . $email;
}
$chatBot->send($msg,$sendto);
?> 
