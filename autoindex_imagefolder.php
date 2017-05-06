<?php include('swhlab_functions.php');?>
<?php timer(); ?>
<?php $path="C:/Users/scott/Documents/Data/swhlab"; ?>
<html>

<head>
<style>

.picframe_shadow{
	padding: 0px; 
	margin: 20px;
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

    foreach (scandir($path) as $file) {
        if ('.' === $file) continue;
        if ('..' === $file) continue;
        if (is_dir($path.$file)) continue;
		if (substr($file,8,5)=="_tif_"){
			echo("<h3>$file</h3>");
		}
		if (endsWith(strtolower($file),".png") or endsWith(strtolower($file),".jpg")){
			html_pic("/data/swhlab/".$file);
		}
    }
?>

<hr>
<span style="color: gray; font-family: monospace;">page generated in <?php timer(1); ?></style>
</body>
</html>