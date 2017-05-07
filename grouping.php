<?php include('swhlab_functions.php');?>
<?php $abfProject="C:/Users/scott/Documents/Data"; ?>
<html>
<body>

<b>working directory:</b><br>
<code><?php echo $abfProject; ?></code>

<h3>cell IDs</h3>
<?php
	$IDs=dirscan_cellIDs($abfProject);
	echo(implode(", ",$IDs));
?>


<h3>all ABFs</h3>
<?php
	$groups=dirscan_cellIDs($abfProject,True);
	foreach ($groups as $group){
		echo("<b><u>".bn($group[0])."</u></b><br>");
		foreach ($group as $abfFile){
			echo("$abfFile<br>");
		}
		echo("<br><br>");
	}
?>

</body>
</html>