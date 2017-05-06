<?php include('swhlab_functions.php');?>
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
		$allIDs[]=explode("_",$file)[0];
        if (strpos($file, '_tif_')){
			$cellIDs[]=explode("_",$file)[0];
		}
    }
	$allIDs=array_unique($allIDs);
	$cellIDs=array_unique($cellIDs);
	
	foreach ($allIDs as $ID){		
		// figure out all pics associated with this ID
		$pics_tif=[];
		$pics_jpg=[];
		foreach ($files as $file){
			if (!endsWith($file, '.jpg')) continue;
			if (startsWith($file,$ID)){
				if (strpos($file, '_tif_')){
					$pics_tif[]="/data/swhlab/".$file;
				} else {
					$pics_jpg[]="/data/swhlab/".$file;
				}
			}
		}
		
		// If TIF files exist, assume it's a new cell
		if (sizeof($pics_tif)){
			echo("<h2>$ID</h2>");
			html_pics($pics_tif);
			echo("<br>");
		}
		html_pics($pics_jpg);
	}
	
?>

<hr>
<span style="color: gray; font-family: monospace;">page generated in <?php timer(1); ?></style>
</body>
</html>