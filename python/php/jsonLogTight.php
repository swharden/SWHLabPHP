<?php
$string = file_get_contents("C:\\Users\\scott\\Documents\\GitHub\\SWHLabPHP\\python\\COMMAND_LOG.json");
$string = str_replace("\r","",$string );
$string = str_replace("\n}\n\n{",",\n",$string );
$json = json_decode($string,true);
foreach ($json as $entry => $entryJson){
	$color="#b2e8c4";
	if ($entryJson["returnCode"]>0) $color="#e8b2b2";
	echo "<div style='background-color: $color; font-family: monospace; padding: 5px;'>";
	echo "[".$entryJson["timeStamp"]."] ";
	echo $entryJson["command"]." (".number_format($entryJson["msElapsed"],2)." ms)";
	if ($entryJson["returnCode"]>0){
		echo "<blockquote><b>STDOUT:</b><br><pre>".$entryJson["stdout"]."</pre></blockquote>";
		echo "<blockquote><b>STDERR:</b><br><pre>".$entryJson["stderr"]."</pre></blockquote>";
	}
	echo "</div>";

}
?>

