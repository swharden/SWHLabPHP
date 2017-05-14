<?php include("top.php"); ?>

<style>
body {padding: 10px;}
</style>

<span style="font-size: 200%; font-weight: bold;">SWHLab</span><br>
<i>realtime project data viewer</i>

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

echo("<h3>FEATURED PROJECTS</h3>");
foreach ($ini_array["featured"] as $path){
	$path=str_replace("X:\\","\\\\Spike\\X_Drive\\",$path);	
	echo("<a href='/SWHLabPHP/src/?page=frames&project=$path'>$path</a><br>");
}

echo("<h3>ADDITIONAL PROJECTS</h3>");
foreach ($projectFolders as $path){
	echo("<a href='/SWHLabPHP/src/?page=frames&project=$path'>$path</a><br>");
}
?>

<h3>Miscellaneous</h3>
<i>project path information is stored in <a href="projects.ini">projects.ini</a>.</i><br>
<i>source code for this project lives in the <a href="https://github.com/swharden/SWHLabPHP">GitHub project</a>.</i>


<?php include("bot.php"); ?>