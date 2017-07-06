<?php include('top.php'); ?>

<div style="background-color: red; color: white;">
<code>
<span style="font-size: 200%; font-weight: bold;">
ANALYZING LINESCAN DATA ...
</span><br>
<?php echo $project;?>
</code>
</div>

<?php
function fancy_execution($title,$command){
    flush();ob_flush(); // update the browser  
    echo "<h1>$title</h1>";
    echo "<pre><b>command:</b>$command</pre>";
    flush();ob_flush(); // update the browser    
    $pCom = new COM("WScript.Shell");
    $pShell = $pCom->Exec($command);
    $sStdOut = $pShell->StdOut->ReadAll;    # Standard output
    $sStdErr = $pShell->StdErr->ReadAll;    # Error
    flush();ob_flush(); // update the browser  
    echo "<pre><b>OTUPUT:</b>$sStdOut</pre>";
    if (strlen($sStdErr)){
        echo "<pre><b>ERROR:</b>$sStdErr</pre><hr>";
    }
    flush();ob_flush(); // update the browser  
}
?>

<code>
<?php 

$cmd="C:\Users\swharden\AppData\Local\Continuum\Anaconda3\python.exe";
$cmd.=' "C:\Users\swharden\Documents\GitHub\ROI-Analysis-Pipeline\pyLS\pyLineScan.py" "\\\\Spike\X_Drive\Data\SCOTT\2017-06-16 OXT-Tom\2p"';
if (isset($_GET['all'])) {
    fancy_execution("PyLS will analyze NEW linescans",$cmd." reanalyze");
} else {
    fancy_execution("PyLS will analyze NEW linescans",$cmd);
}
?>
</code>
<br><br>
<a href="http://192.168.1.225:8080/SWHLabPHP/src/">go back</a>

<?php include('bot.php'); ?>