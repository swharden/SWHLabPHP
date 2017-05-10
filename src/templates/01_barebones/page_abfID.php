<?php include('top.php');?>

<b style="font-size: 300%">ABF ID <?php echo $abfID;?></b><br>
<i>Showing data associated with a single ABF file</i><br>
<code>
<?php 
    $path=realpath($project."/".$abfID.".abf");
    $proto=abf_protocol($path);
    echo "$path [$proto]";
?>
</code>
<hr>

<?php

	$picsData=dirscan_abfPics($project,$abfID,$tif=False);
	$picsTif=dirscan_abfPics($project,$abfID,$tif=True);
    $parentID=bn(dirscan_parent($project,$abfID));
	
    echo "<h1>Data for abfID: $abfID (parent: $parentID)</h1>";
    
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
		echo("<h1>WARNING: no data found for '$abfID.'</h1>");
	}
	
?>

<?php include('bot.php');?>