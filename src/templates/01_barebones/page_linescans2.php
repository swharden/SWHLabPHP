<?php include('top.php');?><div style="padding: 10px;">

<span style='font-size: 200%'><b>Linescan Project Index</b></span><br>

<code><?php copy_button_write($project); ?></code>









<hr><h1>Experiment Notes</h1>
<table style="padding-left: 20px;"><tr><td style="background-color: #EEE; padding: 10px; border: 1px solid #CCC; border-left: 5px solid #CCC;"><code>
<?php echo file_to_html($project.'/experiment.txt'); ?>
</code></td></tr></table>


<?php

function folder_list_ABFs($folder){
    $files = scandir($folder);
    foreach ($files as $fname){
        if (!endsWith($fname,'.abf')) continue;
        $fname2=realpath($folder.'/'.$fname);
        echo "<code>$fname2</code>";
        copy_button_write($fname2,True);
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

function tiff_to_png_folder($folder){
    $files = scandir($folder);
    foreach ($files as $fname){
        if (!endsWith($fname,".tif")) continue;
        $fname2=$folder."/".$fname;
        if (is_file($fname2.".png")) return;
        //echo "converting $fname ... ";
        $flags="-contrast-stretch .05%";
        $cmd="convert $flags \"$fname2\" \"$fname2.png\"";
        //echo "<br><code>$cmd</code><br>";
        exec($cmd);
        //echo "DONE<br>";
    }
}

function display_all_pics($folder){
    tiff_to_png_folder($folder);
    $files=scandir($folder);
    sort($files);
    foreach ($files as $fname){
        $fname=$folder.'/'.$fname;
        if (endsWith($fname,".png") || endsWith($fname,".jpg")){
            $url=str_replace("X:\\Data\\","/dataX/",$fname);
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
$files=scandir($project."/analysis/");
sort($files);
foreach ($files as $fname){
    if (endsWith($fname,".csv")){
        $url=$project."/analysis/".$fname;
        $url=str_replace("X:\\Data\\","/dataX/",$url);
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
                $url=str_replace("X:\\Data\\","/dataX/",$url);
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

</div><?php include('bot.php');?>