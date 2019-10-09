<?php

class Server_status {
	public static function check_tl7(){
		$maxpercent = 0;
		$warn_percent = Setting::get('warn_percent');
		$server = new server();
	  	if ($server->errorCode == 0) {
			$high_lvl=array("Deep Artery Valley","Eastern Fouls Plain","Belial Forest","Mort","Central Artery Valley","Perpetual Wastelands","Southern Fouls Hills");
			
			$i=0;
			$high_lvl_data=array();
		    forEach ($server->data as $zone => $proz)
				if(in_array($zone,$high_lvl)) {
					$high_lvl_data[$i]->proz=str_replace("%","",$proz["players"]);
					$high_lvl_data[$i]->zone=$zone;
					$i++;				
					$percent=str_replace("%","",$proz["players"]);
					if($percent>$maxpercent) {
						$maxpercent = $percent;
						$max_zone = $zone;
					}
				}		
		}
		if ($maxpercent>=$warn_percent) {
			$sorted=false;
			while(!$sorted){
				$sorted=true;
				for($i=0;$i<(count($high_lvl_data)-1);$i++){
					if(($high_lvl_data[$i]->proz)<($high_lvl_data[$i+1]->proz)){ // switch
						$temp=$high_lvl_data[$i+1];
						$high_lvl_data[$i+1]=$high_lvl_data[$i];
						$high_lvl_data[$i]=$temp;
						$sorted=false;
					}
				}
			}
			
			$blob = "<highlight>Zones with tl7 towers:<end>\n\n";
			foreach($high_lvl_data as $pf)
				$blob .= "<highlight>{$pf->zone}<end>: {$pf->proz}%\n";	
			
			$db = DB::get_instance();
			$db->exec("INSERT INTO server_warnings (`zone`,`percent`,`time`) VALUES('{$max_zone}','{$maxpercent}'," . time() . ");");
			
			$msg = "<orange>Warning: {$maxpercent}% players in <end>" . Text::make_link($max_zone, $blob);
			return $msg;
		}
		else return false;
	}
}

?>