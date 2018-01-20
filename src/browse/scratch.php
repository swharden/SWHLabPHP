<?php

include('general.php');


function file_age_string($ageSec){
    
    // determine file age
    //$ageSec=time()-filemtime($fname);
    $ageMin=$ageSec/60;
    $ageHr=$ageMin/60;
    $ageDy=$ageHr/24;
    $ageYr=$ageDy/365.25;
    $ageString=date("F d Y H:i:s.", filemtime($fname));
    
    // determine string formatting
    if ($ageHr<1) $ageString=sprintf("%.01f min", $ageMin);
    else if ($ageDy<1) $ageString=sprintf("%.01f hr", $ageHr); 
    else if ($ageDy<90) $ageString=sprintf("%.01f d", $ageDy);
    else $ageString=sprintf("%.01f yr", $ageYr);

    return $ageString;
}

function html_link_file_age($file_path, $title=null, $url=null){
    // display a link to a file with a color-coded tag to indicate how recently it was modified
    $file_age_sec = time()-filemtime($file_path);
    $file_age_days = $file_age_sec/60/60/24;
    $file_age_string = file_age_string($file_age_sec);
    if (!$title) $title=basename($file_path);
    if (!$url) $url=$file_path;
    $str_age = file_age_string($file_age_sec);
    echo "<a href='$url'>$title</a> ";

    $style='font-size: 80%; font-family: monospace; padding: 0px 3px 0px 3px; ';

    if ($file_age_days<2) $style.="background-color: #FFFF99; color: #333; border: 1px solid #333";
    else if ($file_age_days<14) $style.="background-color: #EEEECC; color: #999933; border: 1px solid #999933";
    else if ($file_age_days<28) $style.="background-color: #DDEEDD; color: #666; border: 1px solid #666";
    else $style.="background-color: #EEE; color: #999; border: 1px solid #999";
    
    echo"<span style='$style'>$str_age</span>";
}

$folder='X:\Lab Documents';
foreach (dirscan_files(path_xdrive_to_local($folder)) as $fname){
    html_link_file_age($folder."/".$fname);
    echo "<br>";
}

?>