<?php include('top.php');?>


<?php

    foreach (dirscan_cellIDs($project) as $cellID){
               
        $picsData=dirscan_cellPics($project,$cellID,$tif=False);
        $picsTif=dirscan_cellPics($project,$cellID,$tif=True);
        
        $color=project_getCellColor($project,$cellID);
        
        echo "<h1 style='background: $color; padding: 5px 10px 5px 10px;'>Data for cellID: $cellID</h1>";
        
        if(sizeof($picsData)) html_pics($picsData, $prepend="$project/swhlab/");
        echo("<br>");
        if(sizeof($picsTif)) html_pics($picsTif, $prepend="$project/swhlab/");
         
    }
	
?>

<?php include('bot.php');?>