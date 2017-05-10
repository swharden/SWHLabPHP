<?php include('top.php');?>

<code>

<h3>Cell ID</h3>
<?php
	//This displays only cell IDs 
	$IDs=dirscan_cellIDs($project);
	foreach ($IDs as $cellID){
		echo "<a href='?page=cellID&project=$project&cellID=$cellID' target='content'>$cellID</a><br>";
	}
?>

<h3>All ABFs</h3>
<?php
	//This displays every ABF grouped by cell ID
	$groups=dirscan_cellIDs($project,True);
	foreach ($groups as $group){
		$cellID=bn($group[0]);
		echo "<br><b><a href='?page=cellID&project=$project&cellID=$cellID' target='content'>$cellID</a></b><br>";
		foreach ($group as $abf){
            $abfID=bn($abf);
            echo "<a href='?page=abfID&project=$project&abfID=$abfID' target='content'>$abfID</a><br>";
		}
	}
	
?>
</code>
<?php include('bot.php');?>