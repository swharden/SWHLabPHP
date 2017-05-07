<?php include('swhlab_functions.php');?>
<?php $abfProject="C:/Users/scott/Documents/Data"; ?>
<html>
<body>
<code>

<h3>Skinny</h3>
<?php
	//This displays only cell IDs 
	$IDs=dirscan_cellIDs($abfProject);
	foreach ($IDs as $ID){
		echo "<a href='?cellID=$ID'>$ID</a><br>";
	}
?>

<h3>Full</h3>
<?php
	//This displays every ABF grouped by cell ID
	$groups=dirscan_cellIDs($abfProject,True);
	foreach ($groups as $group){
		$ID=bn($group[0]);
		echo "<br><a href='?cellID=$ID'>$ID</a><br>";
		foreach ($group as $abf){
			echo bn($abf)."<br>";
		}
	}
	
?>
</code>
</body>
</html>