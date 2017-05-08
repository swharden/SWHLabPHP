<?php include($_SERVER['DOCUMENT_ROOT'] . "/SWHLabPHP/src/swhlab.php"); ?>
<?php $abfProject="C:/Users/scott/Documents/Data"; ?>
<html>
<body>
<code>

<h3>Skinny</h3>
<?php
	//This displays only cell IDs 
	$IDs=dirscan_cellIDs($abfProject);
	foreach ($IDs as $ID){
		echo "<a href='interface_test_content.php?cellID=$ID' target='content'>$ID</a><br>";
	}
?>

</code>
</body>
</html>