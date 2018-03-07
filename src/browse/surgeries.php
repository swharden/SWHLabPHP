<?php include('general.php'); ?>


<html>
<head>
<style>
body {font-family: sans-serif;}
a {text-decoration: None; color: blue;}
a:hover {text-decoration: underline;}
.tableAnimals{
    border: 1px solid #666;
    box-shadow: 5px 5px 10px  rgba(0, 0, 0, 0.25);
}
td {
    font-family: 'Arial Narrow'; 
    font-size: 80%; padding: 5px; 
    white-space: nowrap;
    border: .5px solid rgba(0, 0, 0, 0.05);
}

th {
    font-family: 'Arial Narrow'; 
    font-size: 80%; padding: 5px; 
    background-color: #666;
	color: white;
}

.row0{
    background-color: #E9E9E9;
}
.row1{
    background-color: #E0E0E0;
}

.row0alt{
    background-color: #c0e0ce;
}
.row1alt{
    background-color: #afd1be;
}

.micrograph{
    margin: 5px;
    border: 1px solid black;
    background-color: black;
    box-shadow: 2px 2px 7px  rgba(0, 0, 0, 0.5); 
}

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
    if (isset($_GET["showall"])){
        echo "<div style='font-size: 300%; font-weight: bold;'>Surgery Log (all animals)</div>";
    } else {
        echo "<div style='font-size: 300%; font-weight: bold;'>Surgery Log</div>";
    }
    echo "<div><a href='surgeries2.php'>test-out version 2</a></div>";
    //echo "<div style='padding-bottom: 20px; color: #CCC;'>$path_csv</div>";
	
	// display the markdown notes
	echo "<table cellspacing=0 class='tableAnimals' style='background-color: #EEE; margin-top: 20px; margin-bottom: 20px;'><tr><th align='left' style='font-size: 200%;'>";
	$age_since = time()-filemtime('D:X_Drive\Data\surgeries\colony.md');
	echo "Colony Notes (updated ".file_age_string($age_since)." ago)";
	echo "</th></tr><tr><td style='border: none;'>";
	echo "<div style='padding-right: 40px;'>";
	markdown_file_render($markdown_file_folder.'X:\Data\surgeries\colony.md');
	echo "</div>";
	echo "</td></tr></table>";

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
    echo "<table class='tableAnimals' cellspacing='0' cellpadding='0' style='margin-top: 50px;'><tr>";

    echo "<tr><th colspan='15' align='left' style='font-size: 200%;'>";
	$age_since = time()-filemtime('D:X_Drive\Data\surgeries\surgery_log.csv');
    echo "Surgery Log (updated ".file_age_string($age_since)." ago)";
    echo "</th></tr>";

    foreach ($lines[0] as $cell){
        //if (strlen($cell)==0) continue;
        $cell=strtoupper($cell);
        $cell=str_replace("STRAIN","STN",$cell);
        $cell=str_replace("TARGET","TGT",$cell);
        $cell=str_replace("DAYS","&Delta;",$cell);
        if (strpos($cell,"OLUME")==1) $cell="&mu;L";
        echo "<th>$cell</td>";
    }
    echo "<th align='left'>FILES</th></tr>";
    $rowsShown=0;
	$lastRowWasSpacer=true;
    for ($row=1; $row<count($lines); $row++){
        if ($lines[$row][0][0]=="#" && !isset($_GET["showall"])) continue;
        $condensedRow=trim(str_replace(" ","",str_replace(",","",implode($lines[$row]))));
        if (strlen($condensedRow)<10) {
			if ($lastRowWasSpacer==true) continue;
			echo "<tr style='background-color: #666;'><td colspan='99'></td></tr>";
			$lastRowWasSpacer=true;
			continue;
		} else {
			$lastRowWasSpacer=false;
		}
        $rowType=$rowsShown%2;
        $rowsShown+=1;
		$useAlt='';
		if (trim($lines[$row][11])=='') $useAlt='alt';
        echo "<tr class='row$rowType$useAlt'>";
        for ($col = 0; $col<count($lines[$row]); $col++) {
            $cell=$lines[$row][$col];
            if ($col==0){
                echo "<td style='font-weight: bold; font-size: 150%;'>$cell</td>";
            } else {
                echo "<td>$cell</td>";
            }            
        }
        if ($row==0) echo "<td>FILES</td>";
        else{
            //echo "<td style='white-space: normal;'>";
            echo "<td>";
            foreach ($folders as $folder){
                if (strtolower($folder)==strtolower($lines[$row][0])){
                    $animal_folder=dirname($path_csv).DIRECTORY_SEPARATOR.$folder;
                    tiff_convert_folder($animal_folder,false);
                    foreach (scandir($animal_folder) as $fname){
                        $extension = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
                        if ($extension=='tif' || $extension =='tiff' || $extension=='db') continue;
                        $animal_file_path=$animal_folder.DIRECTORY_SEPARATOR.$fname;
                        if (!is_file($animal_file_path)) continue;
                        $url=str_replace("X:","/X/",$animal_file_path);
                        if (strpos(strtolower($fname),".jpg") || strpos(strtolower($fname),".png")){
                            echo "<a href='$url'><img class='micrograph' src='$url' height=100></a> ";
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
    $urlShowing="http://192.168.1.9/SWHLabPHP/src/browse/surgeries.php?showall=true&path=$path";
    $urlHiding="http://192.168.1.9/SWHLabPHP/src/browse/surgeries.php?&path=$path";
    echo "<li>You can display this page <a href='$urlShowing'>showing</a> or <a href='$urlHiding'>hiding</a> ignored animals</a>";
    echo "<li>This file was generated from <code>$path_csv</code>";
	echo '<li>Notes at the top are generated from <code>X:\Data\surgeries\colony.md</code>';
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