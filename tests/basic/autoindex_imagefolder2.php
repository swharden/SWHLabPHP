<?php include($_SERVER['DOCUMENT_ROOT'] . "/SWHLabPHP/src/swhlab.php"); ?>
<?php timer(); ?>
<?php $path="C:/Users/scott/Documents/Data/swhlab"; ?>
<html>

<head>
<style>

.picframe_shadow{
	padding: 0px; 
	margin: 10px;
	border: 1px solid black;
    box-shadow: 5px 5px 20px rgba(0, 0, 0, .5);
	}

</style>
</head>

<body>
<span style="font-size: 200%"><b>PROJECT INDEX</b></span><br>
<code><?php echo($path);?></code><br>
<?php html_timestamp();?>
<br>
<hr><br>

<?php

	// scan a SWHLab output folder for files
	$files=scandir($path);
	
	// figure out our cell IDs (based on TIF file prefixes)
    foreach ($files as $file) {
        if (strpos($file, '_tif_')){
			$cellIDs[]=explode("_",$file)[0];
		}
    }
	$cellIDs=array_unique($cellIDs);
	
	foreach ($files as $file){
		if (!endsWith($file, '.jpg')) continue;
		if (startsWith($file,$cellIDs[0])){
			echo("<h2>".$cellIDs[0]."</h2>");
			array_shift($cellIDs);
		}
		html_pic("/data/swhlab/".$file);
	}
	
?>

<hr>
<span style="color: gray; font-family: monospace;">page generated in <?php timer(1); ?></style>
</body>
</html>