<html>
<head>
<style>
body {font-family: sans-serif;}
a {text-decoration: None; color: blue;}
a:hover {text-decoration: underline;}
td {padding: 10px; background-color: #EFEFEF;}
th {padding: 10px; background-color: #CCCCCC;}
</style>
</head>
<body>

<?php

function log_load($logFile, $showInstructions=true){
    
    $logFileFolder=dirname(realpath($logFile));
    $logFileFolder=str_replace("D:\\X_Drive","X:",$logFileFolder);
    echo "<div style='font-size: 200%; font-weight: bold;'>Surgical Project Browser</div>";
    echo "<div><code>$logFileFolder\\</code></div><br>";
    
    $filenames = scandir($logFileFolder);
    $f = fopen($logFile, "r");
    $raw=fread($f,filesize($logFile));
    fclose($f);
    echo "<table>";
    $lines = explode("\n",$raw);
    unset($lines[0]);
    
    foreach (["Cage Card","Species","AP/ML/DV","Surgery Date","Sac Date","notes","files"] as $part){
        echo "<th>$part</td>";        
    }
    
    foreach ($lines as $line){
        $line=trim($line);
        if ($line[0]=="#") continue;
        echo "<tr>";
        $parts=explode(", ",str_replace("\t","",$line));
        foreach ($parts as $part){
            $part=trim($part);
            echo "<td>$part</td>";
        }
        echo "<td>";
        foreach ($filenames as $filename){
            if (strstr(strtoupper($filename),strtoupper(trim($parts[0])))){
                $url="$logFileFolder\\$filename";
                $url=str_replace("X:\Data\\","/dataX/",$url);
                echo "<a href='$url'>";
                echo $filename."</a><br>";
            }
        }
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if ($showInstructions){
        echo "<br><br>";
        echo "<div style='color: #CCC;'>";
        echo "<b>Instructions:</b>";
        echo "<li>modify surgery_log.txt with a text editor (notepad, not word)<br>";
        echo "<li>add tabs and spaces as desired, they are ignored when the file is read<br>";
        echo "<li>associate files (like images) with a surgery by making their filenames start with the cagecard identifier";
        echo "<li>to create an entirely new surgery project, create a new folder one folder up and seed it with a surgery_log.txt file";
        echo "</div>";
    }
}

if(isset($_GET["path"])){
    $path=$_GET["path"];
    $path=$path."/"."surgery_log.txt";
    if (file_exists($path)){
        log_load($path);
    } else {
        echo "FILE DOESN'T EXIST: <code>$path</code>";
    }
    
} else{
    echo "ERROR: use <code>?path=x:\\something\\</code>";
}


?>

</body>
</html>