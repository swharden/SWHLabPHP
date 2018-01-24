<?php 


if (!isset($_REQUEST["cellFolder"])){
    echo "Error: no cell folder defined. Use <code>?cellFolder=X:/some/folder/</code>";
    exit();
}
//$project='D:/X_Drive/Data\projects\2017-06-16 OT-Cre mice\data\2017-08-28 Mannitol 2P\17908000_Cell1_mannitol';
$project=str_replace("/","\\",$_REQUEST["cellFolder"]);
$project=str_replace("X:","D:\X_Drive",$project);
if (!is_dir($project)){
    echo "Error: folder does not exist: <code>$project</code>";
    exit();
}

?>
<?php include('old_swhlab.php');?>
<html>
<head>

<style type="text/css">
body {
	margin: 0px;
    padding: 0px;
	font-family: sans-serif;
}

a {
    color: blue;
    text-decoration: none;
}

a:hover {  
    color: blue;
    text-decoration: underline;  
}


.menu_category{
    //padding-top: 10px;
    padding: 3px;
    margin-top: 10px;
    white-space: nowrap;
    font-family: monospace;
    font-weight: bold;
    background-color: #EEE;
}

.menu_cell_ID{
    font-family: monospace;
    padding: 0px 10px 0px 10px;
}

.menu_cell_description{
    //color: #DDDDDD;
    font-size: 75%;
    white-space: nowrap;
    opacity: .5;
    padding-left: 7px;
    //font-style: italic;
    //font-family: serif;
}

.debuglog{
    font-size: 75%;
    color: CCCCCC;
    white-space: nowrap;
}

.picframe_shadow{
    margin: 10px;
    border: 1px solid black;
    box-shadow: 5px 5px 10px rgba(0, 0, 0, .2);
}

button {
    //border: 0;
    //border-radius: 0;
    //background: none;
    //-webkit-appearance: none;
    //border: 1px solid black;
    //padding: 0px 2px 0px 2px;
    //margin: 5px;
    //box-shadow: 2px 2px 5px rgba(0, 0, 0, .25);
    //background-color: #666;
}

.adminbar{
    font-size: 75%;
    font-family: monospace;
    padding: 5px;
    background-color: #FFCCCC;
}
</style>

<script>
function copyToClipboard(elementId) {

  // Create a "hidden" input
  var aux = document.createElement("input");

  // Assign it the value of the specified element
  aux.setAttribute("value", document.getElementById(elementId).innerHTML);

  // Append it to the body
  document.body.appendChild(aux);

  // Highlight its content
  aux.select();

  // Copy the highlighted text
  document.execCommand("copy");

  // Remove it from the body
  document.body.removeChild(aux);

}
</script>

</head>
<body>
<!--<h1 style="color:#CCCCCC;">SWHLab</h1><hr>-->
<?php
$COLORCODES=[];

// default color for no assigned color
$COLORCODES[]=['','#FFFFFF'];

// color for unknown color codes (ERROR?)
$COLORCODES[]=['?','#EEEEEE'];

// g for "good" [GREEN]
$COLORCODES[]=['g','#00FF00'];
$COLORCODES[]=['g1','#00CC00'];
$COLORCODES[]=['g2','#009900'];

// b for "bad" or "error" or "problem" [RED]
$COLORCODES[]=['b','#FF9999'];

// i for "ignore" or "disregard" or "hide" [GRAY]
$COLORCODES[]=['i','#CCCCCC'];

// s for "separate" or "different" [BLUE]
$COLORCODES[]=['s','#CCCCFF'];
$COLORCODES[]=['s1','#9999DD'];
$COLORCODES[]=['s2','#6666BB'];
$COLORCODES[]=['s3','#333399'];

// w for "warn" or "confusing" or "reanalyze" [YELLOW]
$COLORCODES[]=['w','#FFFF00'];
?>
<?php timer(0); // reset page generation timer ?>






<div style="padding: 10px;">

<span style='font-size: 200%'><b>Linescan Project Index</b></span><br>

<code><?php copy_button_write($project); ?></code>









<hr><h1>Experiment Notes</h1>

<div style='background-color: #CCC; padding: 1px; padding-left: 10px; font-family: monospace;'>
<?php $path=str_replace("D:\X_Drive","X:",realpath($project)).'\experiment.txt';?>
<div><b><?php echo $path;?></b></div>
<div style='background-color: #EEE; padding: 10px;'><?php echo file_to_html($project.'/experiment.txt'); ?></div>
</div>

<!--<table style="padding-left: 20px;">
<tr><td style="background-color: #CCC; padding: 10px; border: 1px solid #CCC; border-left: 5px solid #CCC;">asdf</td></tr>
<tr><td style="background-color: #EEE; padding: 10px; border: 1px solid #CCC; border-left: 5px solid #CCC;"><code>
<?php echo file_to_html($project.'/experiment.txt'); ?>
</code></td></tr></table>
-->


<?php

function folder_list_ABFs($folder){
    if (!file_exists($folder)) return;
    $files = scandir($folder);
    foreach ($files as $fname){
        if (!endsWith($fname,'.abf')) continue;
        $fname2=realpath($folder.'/'.$fname);
        echo "<code>";
        copy_button_write($fname2,False);
        echo "</code>";
        echo "<br>";
    }
}

echo "<hr><h1>e-phys: images</h1>";
display_all_pics($project."/ephys/");
echo "<hr><h1>e-phys: ABFs</h1>";
folder_list_ABFs($project."/ephys/");

?>









<hr><h1>Misc Imaging</h1>
<?php


function csv_master_peaks_by_column($fname=""){   
    // given a CSV file, return the average value of the first n rows (per column)
    // start and end are the baseline duration in number of rows
    
    //$fname = "X:\\Data\\SCOTT\\2017-08-28 Mannital 2P\\17906016_Cell3_VC20hz\\analysis\\linescans_dGoR.csv";

    $f = fopen($fname, "r");
    $raw=fread($f,filesize($fname));
    $peaks=[];
    $labels=[];
    $rows=0;
    foreach (explode("\n",$raw) as $line){
        
        $line = explode(",",$line);
        if (sizeof($labels)==0){
            $labels=$line;
            continue;
        }
        
        if (!(sizeof($line)>1)) continue;
        if (sizeof($peaks)==0){
            // fill first row with data
            foreach ($line as $item) $peaks[]=(float)$item;
        } else {
            // data is already in an array (1 value per column), so add to it
            for ($i=0; $i<sizeof($line); $i++){
                if ((float)$line[$i]>$peaks[$i]) $peaks[$i]=(float)$line[$i];
            };
        }
        $rows+=1; // keep track of how many rows we added
    }
    
    echo "<br><code>";
    echo "<b>peaks by column:</b>";
    
    for ($i=1; $i<sizeof($peaks); $i++){
        echo sprintf("<br>%s, %.04f",  $labels[$i], $peaks[$i]*100);
        echo "&nbsp;&nbsp;";
    }
    echo "</code>";
    
}


function tifsNeedingAnalysis($files,$files2){
    $needAnalysis=[];
    foreach ($files as $fname1){
        if (endsWith($fname1,".tif")||endsWith($fname1,".TIF")) {
            $fname1=str_replace(".TIF",".tif",$fname1);
            if ((!in_array("$fname1.jpg",$files2))&&(!in_array("$fname1-0.jpg",$files2))) {
                $needAnalysis[]=$fname1;
            }
        }
        
    }
    return $needAnalysis;
}

function tiff_to_png_folder($folder){
    if (!file_exists($folder)) return;
    if (!file_exists($folder."/swhlab/")) mkdir($folder."/swhlab/");

    $files = scandir($folder);
    $files2 = scandir($folder.'/swhlab/');
    $tifFiles=tifsNeedingAnalysis($files,$files2);
    if (!count($tifFiles)) return;

    // a conversion is required
    $tifCount=sizeof($tifFiles);
    $scriptPath='D:\X_Drive\Lab Documents\network\htdocs\SWHLabPHP\recode\src\scripts\convertImages.py';
    $__PATH_PYTHON__='C:\ProgramData\Anaconda3\python.exe';
    $cmd="\"$__PATH_PYTHON__\" \"$scriptPath\" \"$folder\"";

    echo "<div style='font-family: monospace;'>";
    echo "<b>$tifCount TIFS REQUIRE TIF->JPG CONVERSION:</b> ";
    foreach ($tifFiles as $fname) echo "$fname ";
    echo "... ";
    
    flush();ob_flush();
    exec($cmd);       
    flush();ob_flush();
    
    echo "<b>COMPLETE!</b></div>";

}

function display_all_pics($folder){
    tiff_to_png_folder($folder);
    $folder=$folder.'/swhlab/';
    if (!file_exists($folder)) return;
    $files=scandir($folder);
    sort($files);
    foreach ($files as $fname){
        $fname=$folder.'/'.$fname;
        if (endsWith($fname,".png") || endsWith($fname,".jpg")){
            $url=str_replace("D:\\X_Drive\\Data\\","/X/Data/",$fname);
            echo "<a href='$url'><img src='$url' height='300'></a> ";
        }
    }
}
?>


<?php
display_all_pics($project."/imaging/");
?>










<hr><h1>Z-Series</h1>
<?php
if (!file_exists($project.'/2P')) return;
$files=scandir($project.'/2P/');
sort($files);
foreach ($files as $fname){
    if (startsWith($fname,"ZSeries")){
        $mipFolder=$project.'/2P/'.$fname.'/MIP';
        echo "<br><code>$mipFolder</code><br>";
        display_all_pics($mipFolder);
    }
}
?>

<h1>Data</h1>
<?php
$files=[];
if (file_exists($project."/analysis/")) $files=scandir($project."/analysis/");
sort($files);
foreach ($files as $fname){
    if (endsWith($fname,".csv")){
        $url=$project."/analysis/".$fname;
        $url=str_replace("X:\\Data\\","/X/Data/",$url);
        echo "<br><br><br><hr>";
        echo "<span style='font-size: 120%; font-weight: bold;'><a href='$url'>$fname</a></span>";
        echo copy_button_write($project."/analysis/".$fname,True);
        echo "<br>";
        
        if ((endsWith($fname,"z_peaks.csv"))||(endsWith($fname,"z_peaksNormed.csv"))){
            $thisFile=$project."/analysis/".$fname;
            $f = fopen($thisFile, "r");
            $raw=fread($f,filesize($thisFile));
            $raw=str_replace("\n","<br>",$raw);
            echo "<code>$raw</code>";
            continue;
        }
        
        foreach ($files as $fname2){
            if (!endsWith($fname2,".png")) continue;
            if (startsWith($fname2,$fname)){
                $url=$project."/analysis/".$fname2;
                //$url=str_replace("X:\\Data\\","/X/Data/",$url);
                $url=str_replace("D:\\X_Drive\\Data\\","/X/Data/",$url);
                echo "<a href='$url'><img src='$url' width='300'></a>";
            }
        }
        
    }
}
?>


<h1>Reference Images</h1>
<?php
$files=scandir($project."/linescans/");
sort($files);
foreach ($files as $fname){
    $folder=$project."/linescans/".$fname;
    if (!is_file($folder."/analysis/data_dataG.csv")) continue;
    echo "<hr><h3>$fname</h3>";
    display_all_pics($folder."/References/");
    echo "<br>";
    display_all_pics($folder."/analysis/");
}
?>

</div>






<?php if(($page=="cellID")) : ?>
<div class="adminbar">
    ADMIN MENU: | 
    <span TITLE="page generated in <?php echo timer(1); ?> at <?php html_timestamp();?>">hover</span> | 
    <a href="?page=log">command history</a> | 
    <a href="?page=action&project=<?php echo $project;?>">actions</a> |    
    <a href="?page=action_tif&project=<?php echo $project;?>">TIF->JPG conv</a> | 
    <a href="?page=action_analyze_all&project=<?php echo $project;?>" target="_blank">analyze ABFs page</a> | 
    <a href="?page=action_analyze&project=<?php echo $project;?>" target="_blank">analyze ABFs graph</a> | 
    <a href="?page=action_caps&project=<?php echo $project;?>">ext case fix</a> | 
    <a href="?page=action_deleteCell&project=<?php echo $project;?>&cellID=<?php echo $cellID; ?>">delete cell</a> | 
    <a href="?page=action_delete&project=<?php echo $project;?>">delete ALL</a> | 
    <a href="../../../../../../../../">HOME</a> | 
</div>
<?php endif; ?>

<!--<?php html_msg(); ?>-->
</body>
</html>