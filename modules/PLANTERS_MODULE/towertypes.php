<?php

if (preg_match("/^towertypes$/i", $message)) {
	$blob = "<orange>CT QL <end><yellow>::<end> <white>type<end>\n\n";
	$blob .= "<white>VII<end> <yellow>::<end> 226 to 255\n";
	$blob .= "<white>VI<end>  <yellow>::<end> 201 to 225\n";
	$blob .= "<white>V<end>   <yellow>::<end> 177 to 200\n";
	$blob .= "<white>IV<end>  <yellow>::<end> 129 to 176\n";
	$blob .= "<white>III<end>  <yellow>::<end>  82 to 128\n";
	$blob .= "<white>II<end>   <yellow>::<end>  34 to  81\n";
	$blob .= "<white>I<end>    <yellow>::<end>   1 to  33";
	$msg = Text::make_link("Tower types",$blob,'blob');
	$chatBot->send($msg,$sendto);
} else {
	$syntax_error = true;
}