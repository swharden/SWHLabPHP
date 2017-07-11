<?php include('top.php'); ?>
<?php

if (isset($_GET['error'])){
    // error log
    echo "<h1>ERROR HISTORY</h1><hr>";
    if (isset($_GET['clear'])) file_put_contents($PATH_COMMAND_ERROR,"");
    $string = file_get_contents($PATH_COMMAND_ERROR);
} else {
    // regular log
    echo "<h1>COMMAND HISTORY</h1><hr>";
    if (isset($_GET['clear'])) file_put_contents($PATH_COMMAND_LOG,"");
    $string = file_get_contents($PATH_COMMAND_LOG);
}


$string = str_replace("\r","",$string );
$string = str_replace("\n}\n\n{",",\n",$string );
$json = json_decode($string,true);
if (sizeof($json)>0){
    foreach ($json as $entry => $entryJson){
        $color="#def4e4";
        if ($entryJson["returnCode"]>0) $color="#f4dede";
        echo "<div style='background-color: $color; font-family: monospace; padding: 5px; margin-top: 2px;'>";
        echo "[".$entryJson["timeStamp"]."] ";
        echo $entryJson["command"]." (".number_format($entryJson["msElapsed"],2)." ms)";
        if ($entryJson["returnCode"]>0){
            echo "<blockquote><b>STDOUT:</b><br><pre>".$entryJson["stdout"]."</pre></blockquote>";
            echo "<blockquote><b>STDERR:</b><br><pre>".$entryJson["stderr"]."</pre></blockquote>";
        }
        echo "</div>";
    }
}

echo "<hr>";

if (isset($_GET['error'])){
    echo '[<a href="/SWHLabPHP/src/?page=log&clear&error">clear error log</a>] ';
} else {
    echo '[<a href="/SWHLabPHP/src/?page=log&clear">clear log</a>] ';
}

?>


<?php include('bot.php'); ?>