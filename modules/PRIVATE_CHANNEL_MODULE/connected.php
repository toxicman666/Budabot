<?php

$sql = "SELECT name FROM members_<myname> WHERE autoinv = 1";
$db->query($sql);
$data = $db->fObject('all');
forEach ($data as $row) {
	Buddylist::remove($row->name, 'member');
	Buddylist::add($row->name, 'member');
}

?>