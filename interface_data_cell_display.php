<?php include('swhlab_functions.php');?>
<?php 
	$abfProject="C:/Users/scott/Documents/Data";
	
	if (isset($_GET["cellID"])){
		$cellID=$_GET["cellID"];
	} else {
		$cellID="no_cell_ID_given";
		echo("<h1>you must give a cell ID like <a href='?cellID=17421010'>?cellID=17421010</a></h1>");
	}	
?>
<html>
<body>
<code>

<?php

	$picsTif=dirscan_cellPics($abfProject,$cellID,$tif=True);
	$picsData=dirscan_cellPics($abfProject,$cellID,$tif=False);
	
	if(sizeof($picsTif)){
		echo "<h3>Micrographs for $cellID</h3>";
		html_pics($picsTif, $prepend="/data/swhlab/");
		echo("<br>");
	}
	
	if(sizeof($picsData)){	
	echo "<h3>Data Figures for $cellID</h3>";
		html_pics($picsData, $prepend="/data/swhlab/");
		echo("<br>");
	}
	
	if (!sizeof($picsData) and !sizeof($picsData)){
		echo("<h1>WARNING: no data found for '$cellID.'</h1>");
	}
	

?>


</code>
</body>
</html>