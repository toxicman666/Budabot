<?php

$vote_end = intval(Setting::get('mod_vote_end_time'));
$in_progress = intval(Setting::get('mod_vote_in_progress'));
if($vote_end<=time()) {
	if(time()-$vote_end > 4579200 && $in_progress==0) Modvote::start_vote();	// if > 8 weeks start new vote
	else if ($in_progress==1) Modvote::end_vote();
} else if ($in_progress==1) Modvote::display_vote(); // display if in progress

?>