<?php

$vote_end = intval(Setting::get('mod_vote_end_time'));
if($vote_end>time() && $vote_end-time()<259200) {
	Modvote::display_vote($sender);
}

?>