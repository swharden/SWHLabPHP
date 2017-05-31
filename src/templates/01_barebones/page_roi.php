<?php include('top.php');?>


<span style="font-size: 200%; font-weight: bold;">ROI Image Analysis</span><br>
<code>
<?php echo($project);?>
<br>
[<a href="?page=action_roi&project=<?php echo $project;?>">re-analyze</a>] Files: 

<?php
$figures=[];
foreach (scandir($project) as $fname){
    if (endsWith($fname,".tif")) continue;
    if ($fname[0]==".") continue;
    echo "$fname ";
    if (startsWith($fname,"fig_")){
        $figures[]=$fname;
    }
}
echo("<hr>");

if (file_exists($project."/experiment.txt")) {    
    $myfile = fopen($project."/experiment.txt", "r");
    $raw=fread($myfile,filesize($project."/experiment.txt"));
    fclose($myfile);
    $raw=str_replace("\n","<br>",$raw);
    
    echo "<blockquote style='border: 1px solid #CCC; background-color: #EEE; padding: 5px;'>";
    echo "<b><u>EXPERIMENT.TXT</u></b><br>$raw</blockquote>";
}

html_pics($figures, $prepend="$project/", $height="400");

foreach (["RoiSet.zip","experiment.txt","Results.xls"] as $fname){
    if (!file_exists($project."/".$fname)) {
        echo "<br><span style='background-color: yellow;'>WARNING: $fname does not exist!</span><br>";
    }
}

if (file_exists($project."/messages.Rout")) {    
    $myfile = fopen($project."/messages.Rout", "r");
    $raw=fread($myfile,filesize($project."/messages.Rout"));
    fclose($myfile);
    $raw=str_replace("\n","<br>",$raw);
    
    echo "<br><br><br><blockquote style='font-size: 70%; color: #CCC;'>";
    echo "<b><u>messages.Rout</u></b><br>$raw</blockquote>";
}

?>

<br><br>
<?php include('bot.php');?>