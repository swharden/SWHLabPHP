<?php include($_SERVER['DOCUMENT_ROOT'] . "/SWHLabPHP/src/swhlab.php"); ?>
<?php timer(); ?>
<?php 
    //$path="\\\\spike\\X_Drive\\Data\\SCOTT\\";
    $path="\\\\spike\\X_Drive\\Data\\2P01\\2015\\";
    //$path="D:/Data/SCOTT/";
?>
<html>
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
        if (!is_dir($path.$file)) continue;
        $files[] = $file;
        echo("<b><u>".$file."</u></b><br>");
        echo(sprintf("<code>swhlab folder contains %d files</code>",sizeof(scandir($path.$file."/SWHLab3"))));
        echo("<blockquote>");
        foreach (dirscan_cellIDs($path.$file,True) as $cellID){
            echo("<b>".bn($cellID[0])."</b><br>");
            echo(str_replace(".abf","",implode(", ", $cellID)));
            echo("<br><br>");
        }
        echo("</blockquote><br><br>");
    }
?>

<hr>
<span style="color: gray; font-family: monospace;">page generated in <?php timer(1); ?></style>
</body>
</html>