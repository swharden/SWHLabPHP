<?php include($_SERVER['DOCUMENT_ROOT'] . "/SWHLabPHP/src/swhlab.php"); ?>
<?php 
	$abfProject="C:/Users/scott/Documents/Data"; 
?>
<html>
<body>
<code>

<div style="background-color: #EEEEFF">
<h1>Data by Group</h1>
<?php

foreach (dirscan_cellIDs($abfProject) as $abfFile){
	echo('<br><br><b><u>'.$abfFile.'</u></b><br>');
	
	$picsData=dirscan_cellPics($abfProject,$abfFile,$tif=False);
	$picsTif=dirscan_cellPics($abfProject,$abfFile,$tif=True);
	
	foreach ($picsTif as $pic){echo("IMG: $pic<br>");}
	foreach ($picsData as $pic){echo("DTA: $pic<br>");}
}

?>
</div>


<div style="background-color: #FFEEEE">
<h1>Data by ABF</h1>
<?php

foreach (dirscan_abfs($abfProject) as $abfFile){
	echo('<br><br><b><u>'.$abfFile.'</u></b><br>');
	
	$picsData=dirscan_abfPics($abfProject,$abfFile,$tif=False);
	$picsTif=dirscan_abfPics($abfProject,$abfFile,$tif=True);
	
	foreach ($picsTif as $pic){echo("IMG: $pic<br>");}
	foreach ($picsData as $pic){echo("DTA: $pic<br>");}
}

?>
</div>


</code>
</body>
</html>