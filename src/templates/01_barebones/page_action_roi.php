<?php include('top.php'); ?>

<div style="background-color: red; color: white;">
<code>
<span style="font-size: 200%; font-weight: bold;">ROI ANALYSIS</span><br>
<?php echo $project;?>
</code>
</div>

<code style="background-color: #FFEEEE;">
<?php

foreach (scandir($project) as $fname){
    if (startsWith($fname,"fig_")){
        echo "DELETING: $fname<br>";   
        unlink($project."/".$fname);
    }
}

$rpath="C:\Program Files\R\R-3.4.0\bin\Rscript.exe";
$rscript="C:\Users\swharden\Documents\GitHub\ROI-Analysis-Pipeline\R\updated.R";
//$rproject="\\\\spike\\X_Drive\\Data\\SCOTT\\2017-05-10 GCaMP6f\\2017-05-10 GCaMP6f PFC OXTR cre\\2017-05-31 cell1";
$rproject=$project;

$command = "\"$rpath\" --vanilla \"$rscript\" \"$rproject\"";

echo "<pre><b>command:</b>$command</pre>";

flush();ob_flush(); // update the browser    
$pCom = new COM("WScript.Shell");
$pShell = $pCom->Exec($command);
$sStdOut = $pShell->StdOut->ReadAll;    # Standard output
$sStdErr = $pShell->StdErr->ReadAll;    # Error
flush();ob_flush(); // update the browser    

echo "<hr><pre><b>OTUPUT:</b>$sStdOut</pre>";
echo "<hr><pre><b>ERROR:</b>$sStdErr</pre><hr>";

echo("<h2 style='background-color: yellow;'>[<a href='/SWHLabPHP/src/?page=roi&project=$project'>VIEW RESULTS</a>]</h2>");



?>

<br>
<b>ROI ANALYSIS COMPLETE.</b>
</code>

<?php include('bot.php'); ?>