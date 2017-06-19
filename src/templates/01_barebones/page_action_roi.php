<?php include('top.php'); ?>

<div style="background-color: red; color: white;">
<code>
<span style="font-size: 200%; font-weight: bold;">ROI ANALYSIS</span><br>
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

<code style="background-color: #FFEEEE;">
<?php
echo "<h3>Deleting old image files...</h3>";
foreach (scandir($project) as $fname){
    if (startsWith($fname,"fig_")){
        echo "DELETING: $fname<br>";   
        unlink($project."/".$fname);
    }
}

// PYTHON
#$pyscript="C:\Users\swharden\Documents\GitHub\ROI-Analysis-Pipeline\pyROI\ROI_video_graph.py";
#$command = "python \"$pyscript\" \"$project\"";
#fancy_execution("Python Execution",$command);
echo "<h3>skipping python video analysis...</h3>";

// R
echo "<h3>starting R analysis...</h3>";
$rpath="C:\Program Files\R\R-3.4.0\bin\Rscript.exe";
$rscript="C:\Users\swharden\Documents\GitHub\ROI-Analysis-Pipeline\sandbox\beronica\updated.R";
$command2 = "\"$rpath\" --vanilla \"$rscript\" \"$project\"";
fancy_execution("R Execution",$command2);


?>

<br>
<b>ROI ANALYSIS COMPLETE.</b>
<?php
echo("<h2 style='background-color: yellow;'>[<a href='/SWHLabPHP/src/?page=roi&project=$project'>VIEW RESULTS</a>]</h2>");
?>
</code>

<?php include('bot.php'); ?>