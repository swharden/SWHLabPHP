<?php 
// This page should never be rendered, just called.
// It contains no classes, just functions.
// It should never be used to render any pages.

include("config.php");



//======================================================================
// VERSION AND SYSTEM INFO
//======================================================================

function version(){
	echo("SWHLab V1<br>");
	html_timestamp();
}

function webpath($fname){
    // convert a project folder and filename (swhlab/some_picture.jpg) to a web-accessable file name
    // this requires server aliasing.
    //global $fileReplaceWhat,$fileReplaceWith;
    //$fname=str_replace($fileReplaceWhat,$fileReplaceWith,$fname);
    
    global $fileReplacements;
    foreach ($fileReplacements as $replacements){
        $fname=str_replace($replacements[0],$replacements[1],$fname);
    }
    return($fname);
}


//======================================================================
// STRING MANIPULATION
//======================================================================

function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}

function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
}

function bn($str){
    // return basename by stripping last 4 characters off
    return substr($str, 0, -4);
}

function timer($display=0){
    global $timer_start;
    if ($display>0){
        $took=(microtime(True)-$timer_start)*1000;
        echo(sprintf("%.02f ms", $took));
    } else {
        $timer_start=microtime(True);
    }
}



//======================================================================
// LOGGING
//======================================================================

$log_messages=[];
function msg($message){
    // add a line to the log
    global $log_messages;
    $log_messages[]=$message;
}

function msg_html(){
    // render log to HTML text (echo)
    global $log_messages;
    echo("<code>");
    echo("<b><u>DEBUG LOG:</u></b><br>");
    foreach($log_messages as $line){
        $style="";
        if (startsWith($line,"ERROR:")){
            $style.="background: #FFCCCC;";
        }
        echo("<span style='$style'>$line</span><br>");
    }
    echo("</code>");
}



//======================================================================
// ABF FILE READING
//======================================================================
// if you want to get fancy, learn how to read the protocol out of the header.
// maybe you can figure out basic information like how many sweeps and file duration?

function abf_protocol($abfFile, $comment=False){
	// opens the ABF file in binary mode to pull the protocol information
	$handle = fopen($abfFile, "r");
	$abfData=fread($handle,10000);
	if (startsWith($abfData,"ABF2")){
	} else {echo("<br>!!!ABF VERSION UNSUPPORTED!!!<br>");return;}
	
	$abfData=explode("Clampex",$abfData)[1];
	$abfData=explode("IN ",$abfData)[0];
	$protoFile=explode(".pro",basename($abfData))[0];
	$protoComment=explode(".pro",$abfData)[1];
	if ($comment) return $protoComment;
	return $protoFile;
}




//======================================================================
// SMALL HTML GENERATION
//======================================================================

function html_timestamp(){
    echo timestamp(); 
} 

function timestamp(){
    $t = microtime(true);
    $micro = sprintf("%06d", ($t - floor($t)) * 1000000);
    $d = new DateTime(date('Y-m-d H:i:s.' . $micro, $t));
    return $d->format("Y-m-d H:i:s.u"); 
} 

function html_pic($fname, $height="200"){
    $fname=webpath($fname);
	echo("\n<a href='$fname'><img src='$fname' height='$height' class='picframe_shadow'></a>");
}

function html_pics($fnames, $prepend="", $height="200"){
	// given an array of picture URLs, run html_pic() on each of them.
	foreach ($fnames as $fname){
		html_pic($prepend.$fname, $height);
	}
}

function html_top(){
    echo("DONT USE TOP OR BOT");
    global $template;
    include("templates/$template/top.php");
}

function html_bot(){
    echo("DONT USE TOP OR BOT");
    global $template;
    include("templates/$template/bot.php");
}


//======================================================================
// DIRECTORY SCANNING / CELL ID GROUPING
//======================================================================

$cache_project=[];
$cache_swhlab=[];

function cachedir($path){
    // crude caching of directory scans
    // assumes it's a project folder unless it ends in /swhlab/
    global $cache_project,$cache_swhlab;
    
    if (basename($path)=="swhlab") {
        if (sizeof($cache_swhlab)){
            //msg("swhlab cache");
        } else {
            msg("scanning $path into cache");
            $cache_swhlab=scandir($path);
        }
        return $cache_swhlab;
    } else {
        if (sizeof($cache_project)){
            //msg("project cache");
        } else {
            msg("scanning $path into cache");
            $cache_project=scandir($path);
        }
        return $cache_project;
    }
    
}

function dirscan_parent($abfProjectPath, $abfID){
    // return the abfID of the parent in a cell group
    $cluster=dirscan_abfCluster($abfProjectPath, $abfID);
    if (sizeof($cluster)) return $cluster[0];
    else return $abfID;
}

function dirscan_abfs($abfProjectPath) {
	// return a list of all ABF files in a path
    $abfs=[];
	foreach (cachedir($abfProjectPath) as $fname){
		if (!endsWith($fname,".abf")) continue;
		$abfs[]=$fname;
	}
	return $abfs;
	
}

function dirscan_cellIDs($abfProjectPath, $abfGroups=False) {
   /* Given a path, return an array of just the cell IDs found
    * This works by scanning the folder for all ABF files, and if any
    * file starts with the same sequence of numbers/letters, it's a new cell.
    * i.e., 234567.abf and 123456789.abc will be flagged as a new cell.
	*
	* By default, returns just the cell IDs of each new cell:
	* [cell1, cell2, cell3, cell4]
	*
	* If $abfGroups=True, returns groups of IDs like this:
	* [[cell1_abf1,cell1_abf2,cell1_abf3],
	*  [cell2_abf1,cell2_abf2,cell2_abf3],
	*  [cell3_abf1,cell3_abf2,cell3_abf3]]
	*
    */
    $files=cachedir($abfProjectPath);
    $filesMashed=",".implode(",", $files);
    $ids=[];
    $thisCell=[];
    $groups=[];
    foreach ($files as $file){
        if (substr(strtolower($file), -4)==".abf"){
            $cellID=substr(strtolower($file), 0, -4);
            if (substr_count($filesMashed, ','.$cellID)>1){
                $ids[] = $cellID;
                $groups[]=$thisCell;
                $thisCell=[];
            }
            $thisCell[]=$file;
        }
    }
    $groups[]=$thisCell;
    $groups=array_filter($groups);
    if ($abfGroups) return $groups;
    return $ids;
}

function dirscan_abfCluster($abfProjectPath, $abfID){
	/* given an ABF ID, scan the path, group all ABFs by cell,
	 * determine which cell that ABF belongs to, and return an array
	 * of all the ABFs associated with that cell
	 */
	
	$groups=dirscan_cellIDs($abfProjectPath,True);
	foreach ($groups as $group){
		foreach ($group as $abf){
			if($abfID == bn($abf)){
				return($group);
			}
		}
	}
	return([]);
}

function dirscan_abfPics($abfProjectPath, $abfID, $tif=False){
	// given an ABF ID, return all data figure filenames associated with it
	$abfDataPath=$abfProjectPath."/swhlab/";
	$dataFiles=[];
	if (endsWith($abfID,".abf")) $abfID=bn($abfID);
    foreach (cachedir($abfDataPath) as $fname){
		if (!startsWith($fname,$abfID)) continue;
		if (!endsWith($fname,".jpg")) continue;
		if ($tif and substr_count($fname,"_tif_")) $dataFiles[]=$fname;
		if (!$tif and !substr_count($fname,"_tif_")) $dataFiles[]=$fname;
	}
	return $dataFiles;
}


function dirscan_cellPics($abfProjectPath, $abfID, $tif=False){
	// given an ABF ID, return all data figure filenames associated with the entire CELL
	$abfDataPath=$abfProjectPath."/swhlab/";
	$dataFiles=[];
	if (endsWith($abfID,".abf")) $abfID=bn($abfID);
	$validABFs=dirscan_abfCluster($abfProjectPath, $abfID);
	
    foreach (cachedir($abfDataPath) as $fname){
		foreach ($validABFs as $abf){
			if (!startsWith($fname,bn($abf))) continue;
			if (!endsWith($fname,".jpg")) continue;
			if ($tif and substr_count($fname,"_tif_")) $dataFiles[]=$fname;
			if (!$tif and !substr_count($fname,"_tif_")) $dataFiles[]=$fname;
		}
	}
	return $dataFiles;
}

?>

<!-- swhlab_functions.php has been included -->