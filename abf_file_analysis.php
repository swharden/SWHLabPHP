<?php include('swhlab_functions.php');?>
<?php timer(); ?>
<html>
<body>
<span style="font-size: 200%"><b>PROJECT INDEX</b></span><br>
<?php html_timestamp();?>
<br><br>
<code>

<?php

	$path="C:/Users/scott/Documents/Data/17501046.abf";
	echo("FILE: ".$path."<br>");
	echo("CREATED: ".filemtime($path)."<br>");
	echo(sprintf("CREATED: %.02f days ago<br>",(time()-filemtime($path))/24/24));
	echo("SIZE: ".filesize($path)." bytes <br>");
	echo(sprintf("SIZE: %.03f MB <br>",filesize($path)/1024/1024));
	echo("PROTOCOL FILENAME: ".abf_protocol($path)."<br>");
	echo("PROTOCOL COMMENT: ".abf_protocol($path,1)."<br>");

?>

</code>
<br><br><br>
<span style="color: gray; font-family: monospace;">page generated in <?php timer(1); ?></style>
</body>
</html>