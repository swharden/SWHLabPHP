<?php
$string = file_get_contents("COMMAND_LOG.json");
$string = str_replace("\r","",$string );
$string = str_replace("\n}\n\n{",",\n",$string );
$json = json_decode($string,true);
foreach ($json as $entry => $entryJson){
	echo "<pre><code>";
	foreach ($entryJson as $section => $value){
		echo "<b>\n$section</b>\n$value<br>";
	}
	echo "</code></pre><hr>";
}
?>