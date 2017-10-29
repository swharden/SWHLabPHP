<?php include("top.php"); ?>

<style>
body {padding: 10px;}
</style>

<!--
<p style="background-color: #EEEEEE; padding: 5px;">
<span style="font-size: 200%; font-weight: bold;">Frazier Laboratory: Live Data</span><br>
<i>master project index</i>
</p>
-->

<?php

////////////////////////////////////////////////////////////////
// read INI file and scan directories for ABF-containing folders
////////////////////////////////////////////////////////////////

function containsABFs($path){
	// returns TRUE if the folder contains ABFs inside.
	foreach (scandir($path) as $fname){
		if (substr($fname,-4)=='.abf') return True;
	}
	return False;
}

$ini_array = parse_ini_file("projects.ini"); // relative to the calling file folder
$projectFolders=[];

// add each project manually defined
foreach ($ini_array["projects"] as $path){
	$path=str_replace("X:\\",$PATH_XDRIVE_ROOT,$path);
	#if (!containsABFs($path)) continue;
	$projectFolders[]=$path;
}

// add each project inside the project collection folders
foreach ($ini_array["collections"] as $fldrParent){
	$fldrParent=str_replace("X:\\",$PATH_XDRIVE_ROOT,$fldrParent);
    if (!file_exists($fldrParent)) continue;
	#echo("<br><b>$fldrParent</b><br>");
	foreach (scandir($fldrParent) as $fldrChild){
		if ($fldrChild[0]=='.') continue;
		$path=$fldrParent."\\".$fldrChild;
		if (!is_dir($path)) continue;
		#if (!containsABFs($path)) continue;
		$projectFolders[]=$path;
	}
	
}

// clean up the paths and sort them as desired
// maybe sort them by date?
$projectFolders=array_unique($projectFolders);
//sort($projectFolders);
rsort($projectFolders);

////////////////////////////////////////////////////////////////
// now display what we have
////////////////////////////////////////////////////////////////

?>

<!--
<div style="background-color: #EEEEFF; padding: 5px;">
<span style="font-weight: bold; font-size: 150%;">ABF FILE ANALYSIS</span>
<br>
<i>use the new ABF browser seen on the home page</i>
-->


<!--
<?php 
echo("<br><b>Additional Projects</b><br>");
foreach ($projectFolders as $path){
    $path2=network_path($path);
	echo("<a href='/SWHLabPHP/src/?page=frames&project=$path'>$path2</a><br>");
}
?>
-->
</div>

<br>
<div style="background-color: #b5e0cd; padding: 5px;">
<span style="font-weight: bold; font-size: 150%;">TWO-PHOTON PROJECTS</span>
<br>
<?php 
function page_list_twoPhotonProject($folderParent){
    echo "<br>".network_path($folderParent)."<br>";
    $projectFolders=scandir($folderParent);
    sort($projectFolders);
    foreach ($projectFolders as $projectFolder){
        if (!is_dir($folderParent.'/'.$projectFolder.'/linescans/')) continue;
        echo "&nbsp;&nbsp;&nbsp;";
        $url="?page=linescans2&project=$folderParent\\$projectFolder";
        echo "<a href='$url'>$projectFolder</a><br>";
    }
}
echo page_list_twoPhotonProject($PATH_XDRIVE_ROOT.'Data\projects\2017-06-16 OT-Cre mice\data\2017-08-28 Mannitol 2P');
?>
</div>

<br>
<div style="background-color: #d0efe1; padding: 5px;">
<span style="font-weight: bold; font-size: 150%;">TWO-PHOTON LINESCANS</span>
<br>
<?php 

$projectLS = $PATH_XDRIVE_ROOT.'Data\projects\2017-06-16 OT-Cre mice\data\fishing\2017-06-16 mannitol linescans\data\linescans\2p';

// PUT THIS IN A LOOP OVER THE FOLDERS
$projectLSpretty = str_replace("D:\\X_Drive","X:",$projectLS);

echo "<br>$projectLSpretty ";
//echo "[<a href='?page=action_analyzeLS&project=$projectLS' target='_blank'>analyze new</a>]";
echo "<br>";
//echo "View: <a href='/SWHLabPHP/src/?page=linescans&project=$projectLS'>all scans</a>, ";
//echo "<a href='/SWHLabPHP/src/?page=linescans&project=$projectLS&notes'>scans with notes</a>, ";
//echo "[re-analyze all<a href='?page=action_analyzeLS&project=$projectLS&all=True' target='_blank'>*</a>]";
echo "<br>";

$lineScanFolders=scandir($projectLS);
sort($lineScanFolders);
$datecodes=[];
foreach ($lineScanFolders as $folder){
    if (!startsWith($folder,"LineScan-")) {continue;}
    $datecodes[]=explode("-",$folder)[1];
}
$datecodes=array_unique($datecodes);
sort($datecodes);
foreach (array_reverse($datecodes) as $datecode){
    echo "&nbsp;&nbsp;<a href='/SWHLabPHP/src/?page=linescans&project=$projectLS&datecode=$datecode'>$datecode</a> ";
}
echo "<br>&nbsp;";
?>
</div>


<!--
<br>
<div style="background-color: #EEEEEE; padding: 5px;">
<span style="font-weight: bold; font-size: 150%;">GCAMP IMAGING VIDEO ANALYSIS</span>
<br>
<i>from newest to oldest</i>
<br>
<?php 
foreach (array_reverse($ini_array["collectionsCa"]) as $fldrParent){
    echo "<a href='/SWHLabPHP/src/?page=framesRoi&project=$fldrParent'>$fldrParent</a><br>";
}

?>
</div>

<h2>Miscellaneous</h2>

<b>Internal Software</b>
<li>commands: 
    [<a href="/SWHLabPHP/src/?page=log">log</a>]
    [<a href="/SWHLabPHP/src/?page=log&clear">clear</a>]
<li>errors: 
    [<a href="/SWHLabPHP/src/?page=log&error&test">log</a>]
    [<a href="/SWHLabPHP/src/?page=log&error&clear">clear</a>]
    
<br><br>

<b>Reporter validation:</b><br>
<li><a href="/dataX/SCOTT/2017-06-16%20OXT-Tom/2p/ZSeries-06302017-1049-699/MIP/out_merge">merged</a><br>
<li><a href="/dataX/SCOTT/2017-06-16%20OXT-Tom/2p/ZSeries-06302017-1049-699/MIP/animated.gif">animated (grayscale)</a><br>
<li><a href="/dataX/SCOTT/2017-06-16%20OXT-Tom/2p/ZSeries-06302017-1049-699/MIP/animated2.gif">animated (color)</a><br>

<br>
<i>project path information is stored in <a href="projects.ini">projects.ini</a>.</i><br>
<i>source code for this project lives in the <a href="https://github.com/swharden/SWHLabPHP">GitHub project</a>.</i>
-->
<?php include("bot.php"); ?>