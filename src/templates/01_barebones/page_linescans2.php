<?php include('top.php');?>

<span style='font-size: 200%'><b>Linescan Project Index</b></span><br>
<code><?php echo $project;?></code><br><br><br>

<h1>Imaging</h1>
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
        if (is_file($fname.".png")) return;
        //echo "converting $fname ... ";
        $flags="-contrast-stretch .05%";
        $cmd="convert $flags \"$fname\" \"$fname.png\"";
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

<h1>Maximum Intensity Projections</h1>
<?php
$files=scandir($project.'/2P/');
sort($files);
foreach ($files as $fname){
    if (startsWith($fname,"ZSeries")){
        $mipFolder=$project.'/2P/'.$fname.'/MIP';
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
        echo "<hr><a href='$url'><h2>$fname</h2></a>";
        
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

<?php include('bot.php');?>