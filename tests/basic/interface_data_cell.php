<?php include($_SERVER['DOCUMENT_ROOT'] . "/SWHLabPHP/src/swhlab.php"); ?>
<?php 
	$abfProject="C:/Users/scott/Documents/Data";
	//$cellID="17421010";
	$cellID=$_GET["cellID"]; //interface_data_cell.php?cellID=17421010
?>
<html>
<body>
<code>

<?php
	
	$picsTif=dirscan_cellPics($abfProject,$cellID,$tif=True);
	$picsData=dirscan_cellPics($abfProject,$cellID,$tif=False);
	
	if(sizeof($picsTif)){
		echo "<h1>Micrographs for $cellID</h1>";
		foreach ($picsTif as $pic){echo("IMG: $pic<br>");}
	}
	
	if(sizeof($picsData)){
		echo "<h1>Data Figures for $cellID</h1>";
		foreach ($picsData as $pic){echo("DTA: $pic<br>");}
	}
	
	if (!sizeof($picsData) and !sizeof($picsData)){
		echo("WARNING: no data found for $cellID.");
	}

?>


</code>
</body>
</html>