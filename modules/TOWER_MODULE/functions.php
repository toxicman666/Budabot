<?php

function parseTable($html){
  // Find the table
  preg_match("/<table.*?>.*?<\/[\s]*table>/s", $html, $table_html);
 
  // Get title for each row
  preg_match_all("/<th.*?>(.*?)<\/[\s]*th>/", $table_html[0], $matches);
  $row_headers = $matches[1];
 
  // Iterate each row
  preg_match_all("/<tr.*?>(.*?)<\/[\s]*tr>/s", $table_html[0], $matches);
 
  $table = array();
 
  foreach($matches[1] as $row_html)
  {
    preg_match_all("/<td.*?>(.*?)<\/[\s]*td>/", $row_html, $td_matches);
    $row = array();
    for($i=0; $i<count($td_matches[1]); $i++)
    {
      $td = strip_tags(html_entity_decode($td_matches[1][$i]));
      $row[$row_headers[$i]] = $td;
    }
 
    if(count($row) > 0)
      $table[] = $row;
  }
  return $table;
}

function getTowerType($ql) {
	$towerType = '';
	
	if ($ql >= 276) {
		$towerType = "VIII";
	} else if ($ql >= 226) {
		$towerType = "VII";
	} else if ($ql >= 201) {
		$towerType = "VI";
	} else if ($ql >= 177) {
		$towerType = "V";
	} else if ($ql >= 129) {
		$towerType = "IV";
	} else if ($ql >= 82) {
		$towerType = "III";
	} else if ($ql >= 34) {
		$towerType = "II";
	} else {
		$towerType = "I";
	}
	
	return $towerType;	
}

function getOpenTimeSql($current_time) {
	$first_high_val = $current_time + (3600*7);
	$first_low_val = $current_time;
	$second_high_val = $current_time - 86400 + (3600*7);
	$second_low_val = $current_time - 86400;

	return "((s.close_time BETWEEN $first_low_val AND $first_high_val) OR (s.close_time BETWEEN $second_low_val AND $second_high_val))";
}

function getGasLevel($close_time) {
	$current_time = time() % 86400;

	$site = new stdClass();
	$site->current_time = $current_time;
	$site->close_time = $close_time;
	
	if ($close_time < $current_time) {
		$close_time += 86400;
	}

	$time_until_close_time = $close_time - $current_time;
	$site->time_until_close_time = $time_until_close_time;
	
	if ($time_until_close_time < 3600 * 1) {
		$site->gas_change = $time_until_close_time;
		$site->gas_level = '5%';
		$site->next_state = 'closes';
		$site->color = "<orange>";
	} else if ($time_until_close_time < 3600 * 6) {
		$site->gas_change = $time_until_close_time;
		$site->gas_level = '25%';
		$site->next_state = 'closes';
		$site->color = "<green>";
	} else {
		$site->gas_change = $time_until_close_time - (3600 * 6);
		$site->gas_level = '75%';
		$site->next_state = 'opens';
		$site->color = "<red>";
	}
	
	return $site;
}

function formatSiteInfo($row) {
	global $chatBot;
	
	$t_type = getTowerType($row->ct_ql);
	$close_time = gmdate("H:i:s T", $row->close_time);

	$out_of_date = '';
	if ($row->is_current == 0) {
		$out_of_date = "<red>(Out of date - requires rescouting)<end>";
	}	

	if($row->faction=="Omni")
		$faction="<font color=#00ffff>" . $row->faction . "</font>";
	else if ($row->faction=="Clan")
		$faction="<font color=#ff9900>" . $row->faction . "</font>";
	else
		$faction=$row->faction;
	
	
	$topic = '';
	if ($row->topic == '') {
		$topic .= "Not set";
	} else {
		$topic .= "{$row->topic} {$rally} [by {$row->topic_by}] [<a href='chatcmd:///tell <myname> basetopic {$row->short_name} {$row->site_number}'>Use this topic</a>]";
	}
	
	$waypoint = Text::make_link($row->x_coord . "x" . $row->y_coord, "/waypoint {$row->x_coord} {$row->y_coord} {$row->playfield_id}", 'chatcmd');

	$blob =
"<font color=#66aa66>Short name:</font> <white>{$row->short_name} {$row->site_number}<end>
<font color=#66aa66>Long name:</font> <white>{$row->site_name}, {$row->long_name}<end>
<font color=#66aa66>Level range:</font> <white>{$row->min_ql}-{$row->max_ql}<end>
<font color=#66aa66>Centre coordinates:</font> <a href='chatcmd:///waypoint {$row->x_coord} {$row->y_coord} {$row->playfield_id}'>{$row->x_coord}x{$row->y_coord}</a>
<font color=#66aa66>Standard topic:</font> <white>{$topic}<end>
<font color=CCInfoHeader>";
	if(($row->faction!="Omni")||(Setting::get('hide_omni_scout')!=1)||($row->is_current == 0)||($row->org_ban==1)) $blob .= "Scouted {$type}on {$row->scouted_on} by {$row->scouted_by}:<end> {$out_of_date}
<font color=#66aa66>Current owner:</font> <white>{$row->guild_name}  ({$faction})<end>
<font color=#66aa66>CT QL:</font> <white>{$row->ct_ql}<end>   <font color=#66aa66>Type:</font> <white>{$t_type}<end>   <font color=#66aa66>Close time:</font> <white>{$close_time}<end>\n";
	else $blob .= "<font color=CCInfoHeader>(Scout info hidden)<end>\n";
	$blob .= "<a href='chatcmd:///tell <myname> attacks {$row->short_name} {$row->site_number}'>Recent attacks on this base</a>
<a href='chatcmd:///tell <myname> victory {$row->short_name} {$row->site_number}'>Recent victories on this base</a>";
	
	return $blob;
}

function formatSiteInfo_omni($row) {
	global $chatBot;
	
	$t_type = getTowerType($row->ct_ql);
	$close_time = gmdate("H:i:s T", $row->close_time);

	$out_of_date = '';
	if ($row->is_current == 0) {
		$out_of_date = "<red>(Out of date - requires rescouting)<end>";
	}	

	if($row->faction=="Omni")
		$faction="<font color=#00ffff>" . $row->faction . "</font>";
	else if ($row->faction=="Clan")
		$faction="<font color=#ff9900>" . $row->faction . "</font>";
	else
		$faction=$row->faction;
	
	
	$topic = '';
	if ($row->topic == '') {
		$topic .= "Not set";
	} else {
		$topic .= "{$row->topic} {$rally} [by {$row->topic_by}] [<a href='chatcmd:///tell <myname> basetopic {$row->short_name} {$row->site_number}'>Use this topic</a>]";
	}
	
	$waypoint = Text::make_link($row->x_coord . "x" . $row->y_coord, "/waypoint {$row->x_coord} {$row->y_coord} {$row->playfield_id}", 'chatcmd');

	$blob =
"<font color=#66aa66>Short name:</font> <white>{$row->short_name} {$row->site_number}<end>
<font color=#66aa66>Long name:</font> <white>{$row->site_name}, {$row->long_name}<end>
<font color=#66aa66>Level range:</font> <white>{$row->min_ql}-{$row->max_ql}<end>
<font color=#66aa66>Centre coordinates:</font> <a href='chatcmd:///waypoint {$row->x_coord} {$row->y_coord} {$row->playfield_id}'>{$row->x_coord}x{$row->y_coord}</a>
<font color=#66aa66>Standard topic:</font> <white>{$topic}<end>
<font color=CCInfoHeader>";
	$blob .= "Scouted {$type}on {$row->scouted_on} by {$row->scouted_by}:<end> {$out_of_date}
<font color=#66aa66>Current owner:</font> <white>{$row->guild_name}  ({$faction})<end>
<font color=#66aa66>CT QL:</font> <white>{$row->ct_ql}<end>   <font color=#66aa66>Type:</font> <white>{$t_type}<end>   <font color=#66aa66>Close time:</font> <white>{$close_time}<end>\n";
	$blob .= "<a href='chatcmd:///tell <myname> attacks {$row->short_name} {$row->site_number}'>Recent attacks on this base</a>
<a href='chatcmd:///tell <myname> victory {$row->short_name} {$row->site_number}'>Recent victories on this base</a>";
	
	return $blob;
}

?>
