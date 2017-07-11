<?php include('top.php'); ?>


<?php

$string = file_get_contents($PATH_COMMAND_LIST);
$string2 = file_get_contents($PATH_COMMAND_LOG);
if (strlen($string)<3){
    echo "<h1>NO COMMANDS IN QUEUE</h1>";
    echo '<meta http-equiv="refresh" content="0;url=/SWHLabPHP/src/?page=log">';
} else {
    // show what commands remain
    $rem = substr_count($string,"python.exe")+1;
    $suc = substr_count($string2,'"returnCode": 0');
    $all = substr_count($string2,'"returnCode":');
    $err = $all-$suc;
    echo "<h1>PROCESSING COMMANDS <img src='templates/$template/thinking.gif' width=20></h1>";
    echo "<b>successes:</b> $suc<br>";
    echo "<b>errors:</b> $err<br>";
    echo "<b>remaining commands ($rem):</b>";
    echo "<blockquote><pre style='color: #666;'>$string</pre></blockquote>";
    
    // run the very next command
    $cmd="\"$PATH_PYTHON\" \"$PATH_COMMAND_PROCESS\" run1";
    echo "<b>RUNNING:</b><blockquote><code>$cmd</code></blockquote>";
    flush();ob_flush(); // update the browser
    exec($cmd);
    flush();ob_flush(); // update the browser
    echo "<b>Complete!</b>";
    flush();ob_flush(); // update the browser
    
    // refresh immediately
    echo '<META HTTP-EQUIV="refresh" CONTENT="0">';
    //echo "REFRESH";
}
?>

<?php include('bot.php'); ?>

