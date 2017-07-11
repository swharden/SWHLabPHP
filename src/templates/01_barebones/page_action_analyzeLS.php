<?php include('top.php'); ?>

<?php 

$cmd="\"$PATH_PYTHON\" \"$PATH_ROI_PYLINESCAN\"";
$LSdirs = scandir($project);
$LSdirsNeedAnalysis=[];
$LSdirsAll=[];
foreach ($LSdirs as $LSdir){
    if (!startsWith($LSdir,"LineScan-")) continue;
    $LSdirsAll[]=$LSdir;
    if (!file_exists($project."/".$LSdir."/analysis/data_GoR.csv")) $LSdirsNeedAnalysis[]=$LSdir;
}

if (isset($_GET['all'])) {
    echo "<h1>Reanalyzing ALL linescans ...</h1>";
    foreach ($LSdirsAll as $path){
        $thisCmd="$cmd \"$project\\$path\"";
        echo "$thisCmd<br>";
        execute_cmd($thisCmd);
    }
} else {
    echo "<h1>Reanalyzing NEW linescans ...</h1>";
    foreach ($LSdirsNeedAnalysis as $path){
        $thisCmd="$cmd \"$project\\$path\"";
        echo "$thisCmd<br>";
        execute_cmd($thisCmd);
    }
}
?>

<meta http-equiv="refresh" content="0;url=/SWHLabPHP/src/?page=commands">

<?php include('bot.php'); ?>