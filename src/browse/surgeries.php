<?php include('general.php'); ?>


<html>
<head>
<style>
body {font-family: sans-serif;}
a {text-decoration: None; color: blue;}
a:hover {text-decoration: underline;}
td {font-family: 'Arial Narrow'; font-size: 80%; padding: 10px; background-color: #EFEFEF;}
th {font-family: 'Arial Narrow'; font-size: 80%; padding: 10px; background-color: #CCCCCC;}
</style>
</head>
<body>

<?php

function parse_csv ($csv_string, $delimiter = ",", $skip_empty_lines = true, $trim_fields = true)
{
    $enc = preg_replace('/(?<!")""/', '!!Q!!', $csv_string);
    $enc = preg_replace_callback(
        '/"(.*?)"/s',
        function ($field) {
            return urlencode(utf8_encode($field[1]));
        },
        $enc
    );
    $lines = preg_split($skip_empty_lines ? ($trim_fields ? '/( *\R)+/s' : '/\R+/s') : '/\R/s', $enc);
    return array_map(
        function ($line) use ($delimiter, $trim_fields) {
            $fields = $trim_fields ? array_map('trim', explode($delimiter, $line)) : explode($delimiter, $line);
            return array_map(
                function ($field) {
                    return str_replace('!!Q!!', '"', utf8_decode(urldecode($field)));
                },
                $fields
            );
        },
        $lines
    );
}


function display_surgery_log($path_csv){
    echo "<div style='font-size: 300%; font-weight: bold;'>Surgery Log</div>";
    echo "<div style='padding-bottom: 20px; color: #CCC;'>$path_csv</div>";

    // read the CSV file
    $f = fopen($path_csv, "r");
    $raw=fread($f,filesize($path_csv));
    fclose($f);
    unset($lines[0]);
    $lines = parse_csv ($raw);
    
    // prepare a list of folder names
    $folders = [];
    foreach (scandir(dirname($path_csv)) as $folderName){
        if ($folderName=='.' || $folderName=='..') continue;
        $path=dirname($path_csv).DIRECTORY_SEPARATOR.$folderName;
        if (is_dir($path)) $folders[]=$folderName;
    }

    // read the CSV and create the primary data table
    echo "<table><tr>";
    foreach ($lines[0] as $cell) echo "<th>$cell</td>";
    echo "<th>FILES</th></tr>";
    for ($row=1; $row<count($lines); $row++){
        $condensedRow=trim(str_replace(" ","",str_replace(",","",implode($lines[$row]))));
        if (strlen($condensedRow)<5) continue;
        if ($lines[$row][0][0]=="#" && !isset($_GET["showall"])) continue;
        echo "<tr>";
        for ($col = 0; $col<count($lines[$row]); $col++) {
            $cell=$lines[$row][$col];
            if ($col==0){
                echo "<td><b>$cell<b></td>";
            } else {
                echo "<td>$cell</td>";
            }            
        }
        if ($row==0) echo "<td>FILES</td>";
        else{
            echo "<td>";
            foreach ($folders as $folder){
                if (strtolower($folder)==strtolower($lines[$row][0])){
                    $animal_folder=dirname($path_csv).DIRECTORY_SEPARATOR.$folder;
                    tiff_convert_folder($animal_folder,false);
                    foreach (scandir($animal_folder) as $fname){
                        $extension = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
                        if ($extension=='tif' || $extension =='tiff') continue;
                        $animal_file_path=$animal_folder.DIRECTORY_SEPARATOR.$fname;
                        if (!is_file($animal_file_path)) continue;
                        $url=str_replace("X:","/X/",$animal_file_path);
                        if (strpos(strtolower($fname),".jpg") || strpos(strtolower($fname),".png")){
                            echo "<a href='$url'><img src='$url' height=100></a> ";
                        } else {
                            echo "<a href='$url'>$fname</a> ";
                        }
                        
                    }
                }
            }
            echo "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";

    // display instructions
    echo "<div style='padding-top: 20px; color: #CCC;'>";
    $path=$_GET["path"];
    echo "<li><a href='http://192.168.1.9/SWHLabPHP/src/browse/surgeries.php?showall=true&path=$path'>display hidden animals too</a>";
    echo "<li>This file was generated from <code>$path_csv</code>";
    echo "<li>Animal numbers in the CSV file starting with # will not be displayed";
    echo "<li>Backups of this surgery log are automatically created daily.";
    echo "</div>";
}

function file_backup($file_path){
    // given a file path, save a daily backup in backups/filename.backup
    $file_path_backup = dirname($file_path)."/backups/".str_replace(".csv","",basename($file_path)).date('-o-m-d').".csv";
    if (!is_dir(dirname(file_path_backup))) return;
    if (is_file($file_path_backup)) return;
    copy($file_path, $file_path_backup);
}









if(isset($_GET["path"])){
    $path=$_GET["path"];
    $path_csv=$path.DIRECTORY_SEPARATOR."surgery_log.csv";
    if (file_exists($path_csv)){
        file_backup($path_csv);
        tiff_convert_folder($path);
        display_surgery_log($path_csv);
    } else {
        echo "FILE DOESN'T EXIST: <code>$path_csv</code>";
    }
    
} else{
    echo "ERROR: use <code>?path=x:\\something\\</code>";
}






?>

</body>
</html>