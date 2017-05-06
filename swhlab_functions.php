<?php 
# SWHLab PHP functions
# This code should be available server-wide




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
// SMALL HTML GENERATION
//======================================================================

function html_timestamp(){
    // display the timestamp in microseconds
    echo("<i>automatically generated at ".microtime(True)."</i>");  
} 




//======================================================================
// DIRECTORY SCANNING / CELL ID GROUPING
//======================================================================

function dirscan_cellIDs($path, $abfGroups=False) {
   /* Given a path, return an array of just the cell IDs found
    * This works by scanning the folder for all ABF files, and if any
    * file starts with the same sequence of numbers/letters, it's a new cell.
    * i.e., 234567.abf and 123456789.abc will be flagged as a new cell.
    */
    $files=scandir($path);
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
?>