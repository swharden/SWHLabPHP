<?php

function showFunctions($fname){
    file_exists($fname) or die("Does not exist: [$fname]");
    $f = fopen($fname, "r") or die("Unable to open: [$fname]");
    $raw=fread($f,filesize($fname));
    fclose($f);
    $functions=[];
    foreach (explode("\n",$raw) as $line){
        if (substr($line,0,8)=="function"){
            if (strstr($line,"{")) $line=explode("{",$line)[0];
            $functions[]=substr($line,9);
        }
    }
    sort($functions);
    echo("<code style='line-height: 150%'>");
    echo("<span style='font-size: 200%; font-weight: bold;'>$fname</span><br>");
    echo("<blockquote>");
    foreach ($functions as $func){
        $funcName=explode("(",$func)[0];
        $funcArgs=explode(")",explode("(",$func)[1])[0];
        echo("<span style='color: blue;'>$funcName</span>");
        echo("<span style='color: red;'> ( </span>");
        echo("<span style='color: gray;'>$funcArgs</span>");
        echo("<span style='color: red;'> ) </span>");
        echo "<br>";
    }
    echo("</blockquote>");
    echo("</code>");
    
}

showFunctions("swhlab.php");

?>