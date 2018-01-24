<?php 


if (!isset($_REQUEST["indexFolder"])){
    echo "Error: no index folder defined. Use <code>?indexFolder=X:/some/folder/</code>";
    exit();
}
//$project='D:/X_Drive/Data\projects\2017-06-16 OT-Cre mice\data\2017-08-28 Mannitol 2P';
$project=str_replace("/","\\",$_REQUEST["indexFolder"]);
if (!is_dir($project)){
    echo "Error: folder does not exist: <code>$project</code>";
    exit();
}

?>

<html>

<head>
<style type="text/css">
body {
	font-family: sans-serif;
}

a {
    color: blue;
    text-decoration: none;
}

a:hover {  
    color: blue;
    text-decoration: underline;  
}

</style>
</head>

<body>
<div style="font-size: 300%; font-weight: bold;">Linescan Experiment Viewer</div>
<div style="font-family: monospace; font-size: 150%;"><?php echo $project;?>\</div>


<?php

$linescanFolders=[];
foreach (scandir($project) as $basename){
    if (!($basename == "." || $basename=="..")) $linescanFolders[]=$basename;
}
sort($linescanFolders);
echo '<div style="font-weight: bold; font-size: 150%;"><br>Scan Folders</div>';
echo "<ul>";
foreach ($linescanFolders as $folder){
    if (!is_dir("$project/$folder")) continue;
    $url="old_linescan.php?cellFolder=$project/$folder";
    if (file_exists("$project/$folder/2p") || file_exists("$project/$folder/linescans")){
        echo "<li><a href='$url'>$folder</a>";
    } else {
        echo "<li>$folder";
    }
    
}
echo "</ul>";
?>


<div style="font-weight: bold; font-size: 150%;">Organizing 2P Experiments</div>
<ul style="font-style: italic; font-size: 80%">
    <li>A 2P "experiment" is a single folder where every sub-folder contains data pertaining to a cell.
    <li>Use <code>?indexFolder=X:/some/path/</code> to point this page to a folder containing multiple "scan folders".
    <li>A "scan folder" contains sub-folders <code>linescans/</code> (where each subfolder is a linescan) and optionally <code>2P/</code> (for T-series, Z-series, etc.)
    <li>Every sub-folder is displayed in the list above, but only valid two photon scan folders (with a linescans or 2p sub folder) have links.
</ul>

</body>
</html>