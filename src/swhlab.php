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
    if ($display){
        $took=(microtime(True)-$timer_start)*1000;
        return sprintf("%.02f ms", $took);
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

function html_msg(){
    // render log to HTML comment text
    global $log_messages;
    echo "\n\n".implode("\n",$log_messages)."\npage loaded in ".timer(1)."\n";
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
    if (strpos($abfData, '.pro') !== false){
        $protoComment=explode(".pro",$abfData)[1];
    } else {
        $protoComment="NOPROTO";
    }
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
    //echo("DONT USE TOP OR BOT");
    global $template;
    include("templates/$template/top.php");
}

function html_bot(){
    //echo("DONT USE TOP OR BOT");
    global $template;
    include("templates/$template/bot.php");
}

function html_from_2d($data2d){
    // given a 2d aray, display it as a HTML table
    $rows=sizeof($data2d);
    $cols=0;
    foreach ($data2d as $line){
        if (sizeof($line)>$cols) $cols=sizeof($line);
    }
    
    echo "<table border='1' borderwidth='1'>";
    foreach ($data2d as $line){
        echo "<tr>";
        foreach ($line as $cell){
            echo "<td>$cell</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
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
		if ($tif and substr_count($fname,".tif.jpg")) $dataFiles[]=$fname;
		if (!$tif and !substr_count($fname,".tif.jpg")) $dataFiles[]=$fname;
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
			if ($tif and substr_count($fname,".tif.jpg")) $dataFiles[]=$fname;
			if (!$tif and !substr_count($fname,".tif.jpg")) $dataFiles[]=$fname;
		}
	}
	return $dataFiles;
}

function dirscan_cell_ABFsAndProtocols($project, $cellID){
    // given a project folder and a cell ID, figure all its children ABF IDs
    // and display them as a list of ABFs (html-formatted with links) and also
    // display what protocol they use
    foreach (dirscan_abfCluster($project, $cellID) as $abfID){
        $path=realpath($project."/".$abfID);
        $proto=abf_protocol($path);
        $abfID=bn($abfID);
        echo "<a href='?page=abfID&project=$project&abfID=$abfID'>$path</a> [$proto]<br>";
        
    }  
}


//======================================================================
// EXPERIMENT / CELL TEXT FILE INFORMATION
//======================================================================


function project_getItems($projectPath){
    // given a project path (containing a bunch of ABFs and TIFs) return a 2d array
    // where each row is a cell ID with values [cellID, colorcode, description].
    // This assums a valid "cells.txt" file exists (otherwise False is returned).
    // In addition, "group separators" have cellID and colorcode as '---'.
    // items are returned in the order that they exist inside cells.txt (a file which
    // could be edited with software or manually)
    
    $experimentPath=$projectPath."/cells.txt";
    if (!file_exists($experimentPath)) file_put_contents($experimentPath,"# automatically created cells.txt\n");
    $f = fopen($experimentPath, "r");
    $raw=fread($f,filesize($experimentPath));
    fclose($f);
    $lines=[];
    foreach (explode("\n",$raw) as $line){
        $line=trim($line);
        if (strlen($line)<3) continue;
        if ($line[0]=='#') continue;
        $lines[]=$line;
    }
                
    $cellIDs=dirscan_cellIDs($projectPath);
    $cellIDsDisplayed=[];
    $items=[];
    
    foreach ($lines as $line){
        $maybeCellID=explode(" ",$line)[0];
        
        if ($maybeCellID=='---'){ 
            // this line is a new section
            $items[]=['---','---',trim(substr($line,4))];
            continue;
        }
        
        foreach ($cellIDs as $cellID){
            if ($maybeCellID==$cellID){
                // this line is a cell ID
                $line=$line."   ";
                $maybeColor=trim(explode(" ",$line,3)[1]);
                $maybeDesc=trim(explode(" ",$line,3)[2]);
                $cellIDsDisplayed[]=$maybeCellID;
                $items[]=[$maybeCellID,$maybeColor,$maybeDesc];
                break;
            }
        }
    }
        
    $items[]=['---','---','UNCATEGORIZED'];
    foreach ($cellIDs as $cellID){
        if (!in_array($cellID,$cellIDsDisplayed)){
            // found a cell which hasn't been accounted for
            $items[]=[$cellID,'',''];
        }
    }
    
    //html_from_2d($items);
    return $items;
    
}

function project_getCellColor($project,$cellID){
    // given a project path and a cell ID, read cells.txt and return its color
    $items=project_getItems($project);
    foreach ($items as $item){
        if ($item[0]==$cellID){
            return colorcode_lookup($item[1]);
        }
    }
    return colorcode_lookup("");
}

function project_getCellComment($project,$cellID){
    // given a project path and a cell ID, read cells.txt and return its comment
    $items=project_getItems($project);
    foreach ($items as $item){
        if ($item[0]==$cellID){
            return $item[2];
        }
    }
    return "";
}

function cell_edit($project, $cellID, $newColor, $newComment){
    $cellFile=realpath($project."/cells.txt");
    //echo "ACTION: COLOR [$newColor] TO [$project/$cellID] in [$cellFile].";
    
    // load the existing cells.txt content
    if (file_exists($cellFile)){
        $f = fopen($cellFile, "r");
        $raw=fread($f,filesize($cellFile));
        fclose($f);
    } else {
        $raw="";
    }

    // modify the line(s) which involve this cell ID
    $lineFound=False;
    $raw=explode("\n",$raw);
    for ($lineNum=0;$lineNum<sizeof($raw);$lineNum++){
        $line=$raw[$lineNum]."      ";
        if (startsWith($line,$cellID)){
            if (sizeof(trim($newComment))){
                // a message is given
                $message=trim($newComment);
            } else {
                // no message given, use the old one
                $message=trim(explode(" ",$line,3)[2]);
            }
            msg("that cell ID is already in the log, so I'm modifying that line...");
            $raw[$lineNum]="$cellID $newColor $message";
            // actually, if both are blank, let's delete the line like it never was there
            if ($newColor=="" and $message==""){
                msg("actually, since color and message is blank, let's delete that line...");
                $raw[$lineNum]="";
            }
            $lineFound=True;
        }
    }
    if (!$lineFound){
        // this line doesn't exist in the log, so add it.
        msg("that cell ID isn't found in the log, so I'm adding a line for it...");
        $message=trim($newComment);
        $raw[]="$cellID $newColor $message";
    }
    $raw=implode("\n",$raw);
    
    // save the updated file to disk
    $f = fopen($cellFile, "w");
    fwrite($f, $raw);
    fclose($f);    
}

function colorcode_lookup($s){
    // for each of the color codes (in colorcodes.php) do a find/replace
    // and return the actual color code to be used. If no match is found,
    // return the original colorcode.
    global $COLORCODES;
    foreach ($COLORCODES as $colorcode){
        if ($s==$colorcode[0]){
            return $colorcode[1];
        }
    }
    return $s;
}

/////////////////////////
// needs sorting
//////////////////////


function rename_lowercase_extensions($folder){
    // ensure every file in a folder with a 3 character extension has a lowercase extension
    foreach (scandir($folder) as $fname) {
        $fname=$folder."/".$fname;
        $rev=explode(".",strrev($fname),2);
        if (strlen($rev[0])!=3) continue;
        $rev[0]=strtolower($rev[0]);
        $rev=implode(".",$rev);
        $fname2=strrev($rev);
        if (!($fname==$fname2)){
            echo "RENAMING (capitalization):<br>";
            rename($fname,$fname2);
        }
    }
}



function analyze_tifConvert($project, $justGetABFsThatNeedConversion=False){
    // given a project folder, make all TIFs JPGs.
    if (!is_dir($project."/swhlab/")) mkdir($project."/swhlab/");
    $fnames1=scandir($project);
    $fnames2=scandir($project."/swhlab/");
    $needsConversion=[];
    $flags="-contrast-stretch .05%"; // flags for ImageMagick conversion
    
    // make sure every TIF has a JPG
    foreach ($fnames1 as $fname1){
        if (endsWith($fname1,".tif")){
            if (!in_array($fname1.".jpg",$fnames2)){
                $needsConversion[]=$fname1;
            }
        }
    }
    
    if ($justGetABFsThatNeedConversion) return $needsConversion;
    
    // for each TIF that needs conversion, convert it!
    foreach ($needsConversion as $fname1){
        $cmd="convert $flags \"$project/$fname1\" \"$project/swhlab/$fname1.jpg\"";
        echo "CONVERTING TIF->JPG ($flags) [$fname1] ... ";
        flush();ob_flush(); // update the browser
        exec($cmd);
        flush();ob_flush(); // update the browser
        echo("DONE<br>");
    }
    
    // correct an issue where stacks are saved, renaming the first slice as the abf.
    $fnames2=scandir($project."/swhlab/");
    foreach ($fnames2 as $fname1){
        $fname2=str_replace(".tif-0.jpg",".tif.jpg",$fname1);
        if (!in_array($fname2,$fnames2)){
            echo "RENAMING STACK: [$fname1] -> [$fname2]<br>";
            $fname1=$project."/swhlab/".$fname1;
            $fname2=$project."/swhlab/".$fname2;
            rename($fname1,$fname2);
        }
    }
    
}

function analyze_delete_everything($project){
    // erase EVERYTHING in the project SWHLab folder.
    $folder=$project."\\swhlab\\";
    if (!is_dir($folder)) mkdir($folder);
    
    echo("DELETING FILES: ");
    foreach (glob($folder."/*.*") as $fname) {
        if (is_file($fname)) {
            echo(basename($fname)." ");
            flush();ob_flush(); // update the browser
            unlink($fname); // do the deletion    
            flush();ob_flush(); // update the browser        
        }
    }
}

/////////////////////////////////////
// CODE RELATED TO ABF FILE ANALYSIS
/////////////////////////////////////

function analyze_abf_commands($project){
    // given a project folder, return a list of commands to analyze EVERY abf in that folder.
    $fnames1=scandir($project);
    $fnames2=scandir($project."/swhlab/");
    $commands=[];
    foreach ($fnames1 as $fname1){
        if (!endsWith($fname1,".abf")) continue;
        $abfID=substr($fname1,0,-4);
        $nFigures=0;
        foreach ($fnames2 as $fname2){
            if (startsWith($fname2,$abfID)){
                if (endsWith($fname2,".tif.jpg")) continue; // ignore micrographs
                $nFigures+=1;
            }
        }
        if ($nFigures==0){
            $cmd="python \"C:\Users\swharden\Documents\GitHub\SWHLab\swhlab\analysis\protocols.py\" \"$project\\$fname1\"";
            $commands[]=$cmd;
        }
    }
    return $commands;
}

function execute_cmd($cmd,$message=""){
    // given a system command and an optional message, run it while also displaying it
    // to the browser using buffering so it shows up in real time. Also add a "DONE" message.
    global $project;
    $clean=str_replace($project,".",$cmd);
    echo "EXECUTING [$clean] ... ";
    flush();ob_flush(); // update the browser    
    $output=exec($cmd);
    flush();ob_flush(); // update the browser    
    echo "$output<br>";
    
    if ($output=="ERROR"){
        $abfID=explode(".abf",explode($project,$cmd)[1])[0];
        $theTouch=$project."/swhlab/".$abfID.".ERROR";
        touch($theTouch);
        echo(" --> CREATING ERROR FILE: $theTouch<br>");
    }
    
}

function analyze_abf_next($project){
    // given a project folder, analyze data from every non-analyzed ABF (returns array of remaining commands)
    $cmd=analyze_abf_commands($project);
    execute_cmd($cmd[0]);
    return count($cmd)-1;
}

function analyze_abf_all($project){
    // given a project folder, analyze data from every non-analyzed ABF
    foreach (analyze_abf_commands($project) as $cmd){execute_cmd($cmd);}
}

function dirscan_parent_previous($project,$cellID){
    // return the cell ID of the PREVIOUS parent in a project. Returns "" if none.
    $cellIDs=dirscan_cellIDs($project);
    if ($cellIDs[0]==$cellID) return "";
    for ($i=0;$i<count($cellIDs);$i++){
        if ($cellIDs[$i]==$cellID) return $cellIDs[$i-1];
    }
    return "???";
}

function dirscan_parent_next($project,$cellID){
    // return the cell ID of the NEXT parent in a project. Returns "" if none.
    $cellIDs=dirscan_cellIDs($project);
    if ($cellIDs[count($cellIDs)-1]==$cellID) return "";
    for ($i=0;$i<count($cellIDs);$i++){
        if ($cellIDs[$i]==$cellID) return $cellIDs[$i+1];
    }
    return "???";
}


?>

<!-- swhlab_functions.php has been included -->