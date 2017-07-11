<?php include('top.php');?>

<h1>Project Index</h1>
<code><?php echo $project; ?></code>

<?php

/*
    foreach (dirscan_cellIDs($project) as $cellID){
               
        $picsData=dirscan_cellPics($project,$cellID,$tif=False);
        $picsTif=dirscan_cellPics($project,$cellID,$tif=True);
        
        $color=project_getCellColor($project,$cellID);
        
        echo "<h1 style='background: $color; padding: 5px 10px 5px 10px;'>Data for cellID: $cellID</h1>";
        
        if(sizeof($picsData)) html_pics($picsData, $prepend="$project/swhlab/");
        echo("<br>");
        if(sizeof($picsTif)) html_pics($picsTif, $prepend="$project/swhlab/");
         
    }
	*/
    
$needAnalysisTIF=analyze_tifConvert($project,True);
$needAnalysisABF=analyze_abf_commands($project);

if (count($needAnalysisTIF)){
    $howMany=count($needAnalysisTIF);
    echo "<h3><a href='?page=action_tif&project=$project'>$howMany TIFs NEED CONVERTING</a></h3>";
}

if (count($needAnalysisABF)){
    $howMany=count($needAnalysisABF);
    echo "<h3><a href='?page=action_analyze&project=$project'>$howMany ABFs NEED ANALYSIS</a></h3>";
}
?>

<?php include('bot.php');?>