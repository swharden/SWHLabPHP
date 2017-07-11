<?php include("top.php"); ?>

<style>
body {padding: 10px;}
</style>

<p style="background-color: #EEEEEE; padding: 5px;">
<span style="font-size: 200%; font-weight: bold;">SWHLabPHP</span><br>
<i>master project index</i>
</p>

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
	$path=str_replace("X:\\","\\\\Spike\\X_Drive\\",$path);
	#if (!containsABFs($path)) continue;
	$projectFolders[]=$path;
}

// add each project inside the project collection folders
foreach ($ini_array["collections"] as $fldrParent){
	$fldrParent=str_replace("X:\\","\\\\Spike\\X_Drive\\",$fldrParent);
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

<div style="background-color: #EEEEFF; padding: 5px;">
<span style="font-weight: bold; font-size: 150%;">ABF FILE ANALYSIS</span>
<br>

<?php 

echo("<br><b>Pinned Projects</b><br>");
foreach ($ini_array["featured"] as $path){
	$path=str_replace("X:\\","\\\\Spike\\X_Drive\\",$path);	
	echo("<a href='/SWHLabPHP/src/?page=frames&project=$path'>$path</a><br>");
}

echo("<br><b>Additional Projects</b><br>");
foreach ($projectFolders as $path){
	echo("<a href='/SWHLabPHP/src/?page=frames&project=$path'>$path</a><br>");
}
?>
</div>

<br>
<div style="background-color: #d0efe1; padding: 5px;">
<span style="font-weight: bold; font-size: 150%;">TWO-PHOTON LINESCAN ANALYSIS</span>
<br>

<?php 

$projectLS = '\\\\Spike\X_Drive\Data\SCOTT\2017-06-16 OXT-Tom\2p';

// PUT THIS IN A LOOP OVER THE FOLDERS
$projectLSpretty = basename(dirname($projectLS));
echo "<a href='/SWHLabPHP/src/?page=linescans&project=$projectLS'>$projectLSpretty</a>";
echo "&nbsp;&nbsp;&nbsp;";
echo "[<a href='/SWHLabPHP/src/?page=linescans&project=$projectLS&notes'>notes only</a>]";
echo "[<a href='?page=action_analyzeLS&project=$projectLS' target='_blank'>analyze new</a>]";
echo "[re-analyze all<a href='?page=action_analyzeLS&project=$projectLS&all=True' target='_blank'>*</a>]";
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
foreach ($datecodes as $datecode){
    echo "&nbsp;&nbsp; <a href='/SWHLabPHP/src/?page=linescans&project=$projectLS&datecode=$datecode'>$datecode</a><br>";
}

?>
</div>


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


<?php include("bot.php"); ?>