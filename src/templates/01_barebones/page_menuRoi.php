<?php include('top.php');?>


<div style="padding: 5px; font-family: monospace; color: white; background-color: black;">
<?php
$abbv=basename($project);
echo "<b>$abbv</b><br>";
?>
</div>




<div style="padding: 5px; font-family: monospace;">

<?php
$fldrParent=str_replace("X:\\","\\\\Spike\\X_Drive\\",$project);
$lastPrefix="";
$fldrChildren=scandir($fldrParent);
foreach (array_reverse($fldrChildren) as $fldrChild){
    if ($fldrChild[0]=='.') continue;
    $prefix = explode(" ",$fldrChild)[0];
    if ($lastPrefix=="") $lastPrefix=$prefix;
    if ($prefix!=$lastPrefix) {
        $lastPrefix=$prefix;
        $sep="&nbsp;";
        #$sep=str_repeat("-",50);
        echo "<span style='color: #CCC; font-size: 50%;'>$sep</span><br>";
    }
    $path=$fldrParent."\\".$fldrChild;
    $abbrev=basename($path);
    echo("<a href='/SWHLabPHP/src/?page=roi&project=$path' target='content'>$abbrev</a><br>");
}
?>

<?php include('bot.php');?>