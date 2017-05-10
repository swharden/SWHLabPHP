<?php include('top.php');?>

<b style="font-size: 300%">Cell ID <?php echo bn(dirscan_parent($project,$cellID));?></b><br>
<i>Showing all data and figures from a group of ABF files</i>
<code>
<?php
    foreach (dirscan_abfCluster($project, $cellID) as $abfID){
        $path=realpath($project."/".$abfID);
        $proto=abf_protocol($path);
        echo "<br>$path [$proto]";
    }
?>
</code><hr>

<?php

	$picsData=dirscan_cellPics($project,$cellID,$tif=False);
	$picsTif=dirscan_cellPics($project,$cellID,$tif=True);
    
	if(sizeof($picsData)){	
	echo "<h3>Figures</h3>";
		html_pics($picsData, $prepend="$project/swhlab/");
		echo("<br>");
	}
    
	if(sizeof($picsTif)){
		echo "<h3>Micrographs</h3>";
		html_pics($picsTif, $prepend="$project/swhlab/");
		echo("<br>");
	}
	
	if (!sizeof($picsData) and !sizeof($picsData)){
		echo("<h1>WARNING: no data found for '$cellID.'</h1>");
	}
	
?>

<?php include('bot.php');?>