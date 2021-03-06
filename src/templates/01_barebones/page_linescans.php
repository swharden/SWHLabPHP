<?php include('top.php');?>

<span style='font-size: 200%'><b>Linescan Index</b></span><br>
<code><?php echo $project;?></code><br><br><br>

<?php
function LoadNotes($noteFileName){
    $f = fopen($noteFileName, "r");
    $raw=fread($f,filesize($noteFileName));
    fclose($f);
    $lines=[];
    foreach (explode("\n",$raw) as $line){
        $line=trim($line);
        if (strlen($line)<3) continue;
        if ($line[0]=='#') continue;
        $lines[]=$line;
    }
    return $lines;
}
function GetNoteFor($notes,$folderName){
    $out="";
    foreach ($notes as $note){
        if (startsWith($note,$folderName)){
            $out=$note."\n";
        }
    }
    return trim($out);
}
$notes = LoadNotes($project."/linescans.txt");
$notesStr = join("\n",$notes);
?>

<?php

$folders=scandir($project);
sort($folders);
foreach ($folders as $fname){
    if (substr($fname,0,1)=='.') {continue;}
    $path=realpath($project.'/'.$fname);
    if (!is_dir($path)) {continue;}
    if (!startsWith($fname,"LineScan-")){continue;}
    $pathAnalysis=realpath($path."/analysis/");
    if (isset($_GET['notes']) and !strpos($notesStr, $fname)){continue;}
    if (isset($_GET['datecode']) and !strpos($fname, $_GET['datecode'])){continue;}
    $identifier = str_replace("LineScan-","",$fname);
    $idLink = "?page=linescans&project=$project&datecode=$identifier";
    
    // NEW LINESCAN
    echo "<div style='background-color: #336699; color: white;'>";
    echo "<span style='font-size: 200%; padding-left: 5px;'><a style='color: white;' href='$idLink'>$fname</a></span>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;";
    //echo "<span style='color: #AAA;'>[<a href='' style='color: #AAA;'>link</a>]</span>";
    echo "<br><code class='menu_cell_description'>$path</code>";
    echo "</div>";
    echo "<blockquote style='font-family: courier;'>";

    // SHOW NOTES
    $note = GetNoteFor($notes,$fname);
    $note = str_replace($fname,"",$note);
    $note = str_replace("�","&mu;",$note);
    if (strlen($note)>3){
        echo "<span style='background-color: #faffe8;'>";
        echo "<b>NOTES: </b>$note<br>";
        echo "</span>";
    }
    
    // SHOW DATA FILES
    echo "<b>DATA FILES:</b> ";
    foreach (scandir($pathAnalysis) as $picFname){
        flush();ob_flush(); // update the browser
        if (endsWith($picFname,'.csv')){
            $picLink = webpath($pathAnalysis.'/'.$picFname);
            echo "<br><a href='$picLink'>$picFname</a> ";
            if ($picFname=="data_GoR.csv") csv_baseline($pathAnalysis."/".$picFname);
            if ($picFname=="data_dGoR.csv") csv_peak($pathAnalysis."/".$picFname);
            if ($picFname=="data_dGoR_byframe_peak.csv") csv_avg_stderr($pathAnalysis."/".$picFname);
            if ($picFname=="data_dGoR_byframe_area.csv") csv_avg_stderr($pathAnalysis."/".$picFname);
        }
    }
    echo "<br>";
    
    // SHOW PICTURES
    $pics=[];
    foreach (scandir($pathAnalysis) as $picFname){if (endsWith($picFname,'.png')){$pics[]=$picFname;}}
	html_pics($pics, $prepend="$path/analysis/", $height="200", $width="200");
        
    echo "</blockquote>";
    echo "<br>";
}

?>

<?php include('bot.php');?>