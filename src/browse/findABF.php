<?php
/* 
This script is specifically designed to find ABF files.
This file is intended to be launched from within OriginLab / CJFLab.
http://192.168.0.0/path/to/origin_findabf.php?filename=123456.abf
*/

function search_filesystem_for_abf($abf_file_name){
    $abf_file_name=str_replace("*","%",$abf_file_name);
    $sql="SELECT System.ItemPathDisplay FROM SYSTEMINDEX WHERE System.FileExtension = '.abf' AND System.FileName LIKE '$abf_file_name'";
    echo "<!-- \n\n\n SQL QUERY: $sql \n\n\n -->";
    // requires COM extension to be installed (edit php.ini)
    $files=[];
    $conn = new COM("ADODB.Connection") or die("Cannot start ADO");
    $recordset = new COM("ADODB.Recordset");
    $recordset -> MaxRecords = 100;
    $conn -> Open("Provider=Search.CollatorDSO;Extended Properties='Application=Windows';");
    $recordset -> Open($sql, $conn);
    while (!$recordset -> EOF) {
        $found_file_path=$recordset -> Fields -> Item("System.ItemPathDisplay") -> Value;
        $files[]=$found_file_path;
        $recordset -> MoveNext();
    }
    return $files;
}
?>



<html>
<head>
<title>ABF Locator</title>
<link rel="stylesheet" href="styles.css">
<style>
body {
    font-family: sans-serif; 
    background-color: white;
    color: black;
    }
a {color: blue; text-decoration: none;}
a:hover {color: blue; text-decoration: underline;}
</style>
</head>
<html>
<body>
<!--<div style='font-size: 200%; font-weight: bold;'>CJFLab ABF Locator</div>-->



<?

$searchbox_html='
    <form action="/swhLabPHP/src/browse/findABF.php">
    <input type="hidden" name="view" value="iframe">
    <input type="text" size="25" name="filename" value="SEARCHSTRING" style="font-family: monospace;">
    <input type="submit" value="search">
    </form>
    ';
$searchDefault='171212ss_0015.abf';
if (isset($_REQUEST["filename"])) $searchbox_html=str_replace("SEARCHSTRING",$_REQUEST["filename"],$searchbox_html);
else $searchbox_html=str_replace("SEARCHSTRING",$searchDefault,$searchbox_html);

if (isset($_REQUEST["view"]) && $_REQUEST["view"]=='iframe'){
    // we are viewing this in an iframe and need a search box
    echo $searchbox_html;
}


if (!isset($_REQUEST["view"]) && !isset($_REQUEST["filename"])){   
    echo "ERROR: no filename provided.<br><br>";
    $url="/swhLabPHP/src/browse/findABF.php?filename=13919011.abf";
    echo "<li>Example 1 (one match): <a href='$url'>$url</a><br>";
    $url="/swhLabPHP/src/browse/findABF.php?filename=13919009.abf";
    echo "<li>Example 2 (multiple matches): <a href='$url'>$url</a><br>";
}

function showPathLink($folder){
    $folderOriginal = $folder;
    $folder = str_replace('D:\X_Drive','X:',$folder);
    $filename = $_REQUEST["filename"];
    $filePath = $folder."\\".$filename;
    $filePathOriginal = $folderOriginal."\\".$filename;
    $fileSize = filesize($filePathOriginal)/1000000;
    $fileSizeString = number_format($fileSize,2);
    $url = "/SWHLabPHP/recode/src/?view=abf&frames&fldr=$folder";
    echo "<code style='color: gray;'>File path:</code> <code>$filePath</code> <code style='color: gray;'>($fileSizeString MB)</code><br>";
    echo "<code style='color: gray;'>ABF browser:</code> <code><a target='_blank' href='$url'>$folder\\</a></code><br>";
    if (isset($_REQUEST["filename"])){
        echo "<code style='color: gray;'>Load Directly Into Origin:</code> <code>setpath \"$folder\\$filename\"; </code><br>";
        echo "<code style='color: gray;'>Set Origin's Alternate Path:</code> <code>altpath$=\"$folder\"</code><br>";
        echo "<br>";
    }
}

if (isset($_REQUEST["filename"])){
    $abf_file_name = $_REQUEST["filename"];
    echo "<div style='line-height: 150%; color: #CCC; font-style: italic; font-size: 80%;'>";
    echo "searching for $abf_file_name ... ";
    flush();ob_flush(); // update the browser
    $files = search_filesystem_for_abf($abf_file_name);
    if (count($files)==0){
        echo "NO MATCH FOUND";
    } else if (count($files)==1){
        echo "found!</div>";
        showPathLink(dirname($files[0]));
    } else {
        echo "found multiple matches:</div>";
        foreach ($files as $fname){
            showPathLink(dirname($fname));
        }
    }
}


?>



</body>
</html>