<?php

// this script dynamically creates HTML views of ABF data.
// when a TIF and ABF have the identical filename, it designates the start of a new cell.
// all subsequent (alphabetical) files belong to that cell, unti lanother matching TIF/ABF is found.

class ABFfolder
{
    // operations related to project folders containing many ABFs and TIFs

    var $fldr; // the input path
    var $analysisFldr='swhlab'; // SWHLab output folder name
    var $fldr2; // output folder
    var $fldr_local; // D:
    var $fldr_network; // X:
    var $fldr_web; // http://192.168.1.9/dataX/SCOTT/2017-07-1...
    var $files; // list of files in this folder
    var $files2; // list of files in SWHLab folder
    var $cells; // list of parents
    var $IDs; // keyed array where keys are parents and children are ABFs (all IDs)
    //var $file_cells; // "$this->fldr/cells.txt"
    // TODO: make "cells.txt" a $this variable
    // TODO: make cells.txt a CSV
    
    // these are populated from cells.txt
    var $cellColors; // keyed by cell ID, contains colorcode of each cell
    var $cellComments; // keyed by cell ID, contains comment of each cell
    var $cellGroups; // key is group name, contents are cell IDs

    
    function __construct($fldr)
    {
        // determine the ABF path (locally and network) and make sure it's valid
        $this->fldr = $fldr;
        $this->fldr2 = "$fldr/$this->analysisFldr";
        $this->fldr_local = path_local($fldr);
        $this->fldr_network = path_network($fldr);
        $this->fldr_web = path_web($this->fldr_local);
        if (!isset($_GET['fldr'])){
            // if no folder is given, default to this path
            redirect("?view=abf&fldr=X:/Data&frames");
        }
        if (!file_exists($this->fldr_local)) {
            //display_error("FOLDER DOES NOT EXIST:<br>$this->fldr_local");
            echo "<h2>I can't seem to find:<br><code>$this->fldr_local</code></h2>";
            echo "<h2>Try navigating for it using the <a href='/SWHLabPHP/recode/src/?view=abf&fldr=X:/Data&frames'>ABF browser</a></h2>";
            return;
        }
        $this->scanFiles(); // scan this folder and ./swhlab/
        $this->process_actions(); // deleting files, adding commands, etc
        $this->process_cell_updates(); // modify cells.txt if asked to
        $this->scanCellsFile(); // load cells.txt for cell colors and comments
    }
    


    function convertAllTiffs(){
        // automatically turn all x.tif files into ./swhlab/x.tif.JPG files
        $tifFiles=$this->tifsNeedingAnalysis();
        if (!count($tifFiles)) return;
        if (!is_dir("$this->fldr_local/swhlab/")) {
            echo "<code><b>CREATING:</b> $this->fldr_local\\swhlab\\</code><br>";
            mkdir("$this->fldr_local/swhlab/");
        }
        
        $tifCount=sizeof($tifFiles);
        //$scriptPath='D:\X_Drive\Lab Documents\network\htdocs\SWHLabPHP\recode\src\scripts\convertImages.py';
        $scriptPath='D:\X_Drive\Lab Documents\network\htdocs\SWHLabPHP\src\browse\scripts\convertImages.py';
        $__PATH_PYTHON__=$GLOBALS['__PATH_PYTHON__'];
		$pathIN = $this->fldr_local;
		$pathOUT = $pathIN.'\swhlab';
        $cmd="\"$__PATH_PYTHON__\" \"$scriptPath\" \"$pathIN\" \"$pathOUT\" ";
        echo "<code>";
        echo "<hr><b>$tifCount TIFS REQUIRE TIF->JPG CONVERSION: ...</b> ";
        //foreach ($tifFiles as $fname) echo "$fname ";
        //echo "<hr><b>RUNNING COMMAND:</b><br>$cmd</b> ... ";
        
        
        flush();ob_flush();
        exec($cmd);       
        flush();ob_flush();
        
        echo "<b>DONE!</b><hr></code>";
        
        
        /*
        echo "<b>CONVERTING TIF->JPG:</b> ";
        foreach ($tifFiles as $fname){
            $fname1="$this->fldr_local\\$fname";
            $fname2="$this->fldr_local\\swhlab\\$fname.jpg";
            
            flush();ob_flush();
            echo "$fname ";
            $cmd="\"$__PATH_PYTHON__\" \"$scriptPath\" \"$this->fldr_local\\$fname\"";
            exec($cmd);       
            flush();ob_flush();
        }            
        echo "<b>DONE!</b>";
        echo "</div>";
        */
        
        /*
        echo "<code><b>CONVERTING TIFS:</b> ";
        foreach ($tifFiles as $fname){
            $fname1="$this->fldr_local\\$fname";
            $fname2="$this->fldr_local\\swhlab\\$fname.jpg";
            
            $size = filesize($fname1);
            $sizeMB = $size/1024/1024;
            echo "$fname ($sizeMB Mb) ";
            
            if ($sizeMB<2){
                // less than 2MB, probably an or 16-bit single-channel TIFF                
                // so use the ImageMagick method
                $cmd="convert -contrast-stretch .05% \"$fname1\" \"$fname2\" ";
                echo "IM ... ";
            } else {
                // NConvert method
                $path_nconvert = 'D:\X_Drive\Lab Documents\network\htdocs\SWHLabPHP\recode\src\bin\XnView\nconvert.exe';            
                $cmd="\"$path_nconvert\" -overwrite -out jpeg -o \"$fname2\" \"$fname1\"";
                echo "NC ... ";
            }
            //echo "<hr><div><code>$cmd</code></div>";
            flush();ob_flush(); // update the browser
            exec($cmd);          
        }
        echo "<b>DONE!</b></code><hr>";
        */
        
        $this->scanFiles(); // update after making tifs
        $this->scanCellsFile();  // update after making tifs
    }

    function abfNeedsAnalysis($cellID){
        // check if an ABF needs analysis (based on lack of files in ./swhlab/)
        if (!count($this->files2)) return True;
        foreach ($this->files2 as $fname2) {
            if (endsWith(strtolower($fname2),".tif.jpg")) continue;
            if (startsWith($fname2,$cellID)) return False;
        }
        return True;
    }

    function abfsNeedingAnalysis(){
        // return a list of ABF IDs needing analysis
        $needAnalysis=[];
        foreach (array_keys($this->IDs) as $parentID){
            foreach ($this->IDs[$parentID] as $cellID){
                if($this->abfNeedsAnalysis($cellID)) {
                    if (!is_file("$this->fldr/$cellID.rsv"))
                        $needAnalysis[]=$cellID;
                }
            }
        }
        return $needAnalysis;
    }
    
    function tifsNeedingAnalysis(){
        // return a list of TIF files needing conversion
        $needAnalysis=[];
        foreach ($this->files as $fname1){
            if (endsWith($fname1,".tif")||endsWith($fname1,".TIF")) {
                $fname1=str_replace(".TIF",".tif",$fname1);
                if ((!in_array("$fname1.jpg",$this->files2))&&(!in_array("$fname1-0.jpg",$this->files2))) {
                    $needAnalysis[]=$fname1;
                }
            }
            
        }
        return $needAnalysis;
    }
    

    function process_actions(){
        
        if (isset($_GET['delete'])){
            // delete graphs associated with this cell ID
            display_error("deleted analysis files for this cell");
            $deleteCell=$_GET['delete'];
            foreach ($this->IDs[$deleteCell] as $cellID){
                $match=$this->fldr_local."/swhlab/$cellID*.*";
                foreach (glob($match) as $fname){
                    unlink($fname);
                }
            }
            echo "<hr>";
            $this->scanFiles(); // re-scan required since stuff is missing now
        }
        
        if (isset($_GET['analyzeFolder'])){
            
            // clear the log file if it contains nothing new
            $logfile = realpath(dirname(dirname(__FILE__))."/scripts/log.txt");
            if (time()-filemtime($logfile) > 60*30) {
                echo "<hr>CLEARED OLD LOG FILE<hr>";
                file_put_contents("$logfile", "");
            }
            
            // analyze all unanalyzed ABFs in this entire folder
            display_error("analyzing entire folder...");
            $commands="";
            
            foreach ($this->abfsNeedingAnalysis() as $cellID){
                //$path=realpath("$this->fldr_network/$cellID.abf"); // best for network PC
                if (is_file($path=realpath("$this->fldr_local/$cellID.rsv"))) continue;
                $path=realpath("$this->fldr_local/$cellID.abf"); // best for server PC
                $commands.="analyze $path\n";
            }
            // write commands to commands.txt
            $CMDFILE = realpath(dirname(dirname(__FILE__))."/scripts/commands.txt");           
            $f = fopen($CMDFILE, "a");
            fwrite($f, $commands."\n");
            fclose($f);
            
            redirect("?view=commands&refresh=1");            
        }
    }
    
    function scanFiles()
    {
        // populate our list of files
        $this->files = scandir2($this->fldr_local);

        //if (file_exists($this->fldr2)) $this->files2 = scandir2($this->fldr2);
        $this->files2 = (file_exists($this->fldr2) ? scandir2($this->fldr2) : []);

        // determine cell parent IDs
        $files_ABF = [];
        $this->cells=[];
        foreach ($this->files as $fname) {
            if (!endsWith($fname, ".abf")) continue;
            $files_ABF[] = $fname;
            $ABF_basename = str_replace(".abf", "", $fname);
            if (in_array($ABF_basename . ".tif", $this->files) || in_array($ABF_basename . ".TIF", $this->files)) $this->cells[] = $ABF_basename;
        }

        // determine IDs of parents
        $parent = "ORPHAN";
        $this->IDs = array();
        foreach ($files_ABF as $fname){
            $ABF_basename = str_replace(".abf", "", $fname);
            if (in_array($ABF_basename,$this->cells)) $parent = $ABF_basename;
            $this->IDs[$parent][]=$ABF_basename;
        }
    }
    
    var $colorcodes = array(   
        "" => "#FFFFFF",             
        "?" => "#EEEEEE",
        "g" => "#00FF00",
        "g1" => "#00CC00",
        "g2" => "#009900",
        "b" => "#FF9999",
        "i" => "#CCCCCC",
        "s" => "#CCCCFF",
        "s1" => "#9999DD",
        "s2" => "#6666BB",
        "s3" => "#333399",
        "w" => "#FFFF00",
    );
    
    function cells_file_backup($path){
        // given the path of a data folder, create a backup copy of cells.txt (maximum once per day)

        $path_local=str_replace('X:','D:\X_Drive',$path);
        $cells_file=$path_local."/cells.txt";
        $datecode=date('o-m-d');
        $cells_file_backup=$path_local."\\swhlab\\cells-backup-$datecode.txt";

        if (!is_file($cells_file)) return;    
        if (!is_dir($path_local."/swhlab/")) return;
        if (is_file($cells_file_backup)) return;
        copy($cells_file, $cells_file_backup);
    }
           
    function scanCellsFile(){
        // scan cells.txt and populate cell colors, comments, and groups.
        
        $this->cells_file_backup($this->fldr_local);
       
        $fnameCellsFile="$this->fldr_local"."/cells.txt"; 
        if (!is_file($fnameCellsFile)) return; // only proceed if cells.txt exists  
        
        $this->cellColors=[]; // keyed by cell ID, contains colorcode of each cell
        $this->cellComments=[]; // keyed by cell ID, contains comment of each cell
        $this->cellGroups=[]; // key is group name, contents are cell IDs

        $lastCategory = "ungrouped";
        $cellsAccountedFor=[];

        $f = fopen($fnameCellsFile, "r");
        $raw=fread($f,filesize($fnameCellsFile));
        fclose($f);
        foreach (explode("\n",$raw) as $line){
            $line=trim($line);
            if (strlen($line)<3) continue; // ignore short lines
            if ($line[0]=='#') continue; // ignore comments
            if (substr($line,0,3)=='---') { // new category
                $lastCategory=trim(substr($line,3));
                continue;
            }
            $line = explode(" ",$line,3);            
            #if (count($line)<3) continue; // ignore lines which don't have 3 parts
            while (count($line)<3) $line[]=' ';
            
            $cellID = $line[0];   
            $cellsAccountedFor[]=trim($cellID);
            $this->cellColors[$cellID]=trim($line[1]);
            if ($this->cellColors[$cellID]=='?') $this->cellColors[$cellID]='';
            $this->cellComments[$cellID]=trim($line[2]);
            if ($this->cellComments[$cellID]=='?') $this->cellComments[$cellID]='';
            
            // update groups keyed array
            if (gettype($this->cellGroups[$lastCategory])=="NULL"){
                $this->cellGroups[$lastCategory]=[$cellID];
            } else {
                $this->cellGroups[$lastCategory]=array_merge($this->cellGroups[$lastCategory],[$cellID]);
            }
        }
        
        // create a list of unlisted cells (cells we see in the folder which aren't in cells.txt)
        $this->cellGroups['unknown']=[];
        foreach ($this->cells as $cellID){
            if (in_array($cellID,$cellsAccountedFor)) continue;
            $this->cellGroups['unknown']=array_merge($this->cellGroups['unknown'],[$cellID]);
        }        
    }
    
    function _display_cell_data($filesByCell){
        // given an array of arrays, display all relevant cell data.
        // the first item of each array is the ABF id, everuthing else is an associated file
        foreach ($filesByCell as $cellFiles){

            // sort each file by its type based on some simple rules
            $files_abf=[];
            $files_data=[];
            $files_pics=[];
            $files_unknown=[];
            foreach ($cellFiles as $fname){
                if (endsWith(strtolower($fname),".abf")) {
                    $files_abf[]="$this->fldr_web/$fname";
                } else if (endsWith(strtolower($fname),".tif.jpg")) {
                    $files_pics[]="$this->fldr_web/$fname";
                } else if (endsWith(strtolower($fname),".jpg")) {
                    $files_data[]="$this->fldr_web/$fname";
                } else if (endsWith(strtolower($fname),".png")) {
                    $files_data[] = "$this->fldr_web/$fname";
                } else {
                    $files_unknown[] = "$this->fldr_web/$fname";
                }
            }
            if (array_keys($this->IDs)==['ORPHAN']){
                foreach ($this->files2 as $fname){
                    if (endsWith(strtolower($fname),".tif.jpg")) {
                        $files_pics[]="$this->fldr_web/swhlab/$fname";
                    }
                }
            }

            // display all the files associated with this cell ID
            $ID = str_replace(".abf",'',$cellFiles[0]);
            $url='http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
            $url.="?view=abf&fldr=$this->fldr&match=$ID&data";
            $urlMenu="?view=abf&fldr=$this->fldr&menu";
            $btn=html_button_copy($url, True, "copy URL");
            $btnFldr=html_button_copy($this->fldr_network, True, "copy folder");
            

            // HEADER: CELL ID AND COMMENT
            $color = $this->colorcodes[$this->cellColors[$ID]];
            $comment = strip_tags($this->cellComments[$ID]);            
            echo "<div style='background-color: $color; padding: 10px;'>";
            echo "<b>CELL ID: <a href='$url'>$ID</a></b> $btn<br>";
            echo "<code>$this->fldr_network</code> $btnFldr<br>";
            echo "Cell comment: <i>$comment</i>";
            echo "</div>";
            
            // CELL COMMENT AND COLOR ASSIGNMENT
            echo "<div style='background-color: #DDD; padding: 10px;'>";
            echo "<div style='line-height: 150%;'><b>Cell Notes:</b></div>";
            echo "<form action='$url' method='get'>";
            echo "<input type='hidden' name='view' value='abf' />";
            echo "<input type='hidden' name='fldr' value='$this->fldr' />";
            echo "<input type='hidden' name='match' value='$ID' />";
            echo "<input type='hidden' name='data' value='' />";
            foreach (array_keys($this->colorcodes) as $colorcode){
                $color=$this->colorcodes[$colorcode];
                $checked=''; //unchecked by default
                if ($colorcode==$this->cellColors[$ID]) $checked='checked';
                echo "<span style='margin: 2px; padding: 5px;  border: solid 1px black; background-color: $color;'>";
                echo "<input type='radio' name='color' value='$colorcode' $checked></span>";
            }
            echo "<br><input style='margin-top: 8px;' type='text' size='35' name='comment' value='$comment' />";
            echo " <input type='submit' value='Submit'>";
            echo " <a href='$urlMenu' target='menu'>refresh menu</a>";
            echo "</form>";       
            echo "</div>";

            // LIST OF ASSOCIATED ABF FILES
            echo "<div style='background-color: #EEE; padding: 10px;'>";
            echo "<b>ABFs associated with this cell:<br></b>";
            foreach ($files_abf as $fname){
                $fname=path_network($fname);
                $bn=basename($fname);
                $protocol = abf_protocol(path_local($fname));
                $filesize = filesize_formatted(path_local($fname));
                $btn = html_button_copy($fname, True, "copy path");
                $setpath='setpath "'.$fname.'"';
                $btn2 = html_button_copy($setpath, True, "setpath");
                echo "<code>$bn $btn $btn2 $protocol ($filesize)</code><br>";
            }
            foreach ($files_unknown as $fname){
                $fname=path_network($fname);
                echo "<code style='color: red;'>unknown file: $fname</code><br>";
            }
            echo "</div>";
            
            // DISPLAY ANALYSIS BUTTONS
            echo "<div style='background-color: #F6F6F6; padding: 10px;'>";
            echo "Analysis: ";
            $urlDelCell = "?".$_SERVER['QUERY_STRING']."&delete=$ID";
            $urlAnlFldr = "?".$_SERVER['QUERY_STRING']."&analyzeFolder";
            echo "<a href='$urlDelCell'>delete graphs for this cell</a>";
            $neededABF=count($this->abfsNeedingAnalysis());
            if ($neededABF){
                echo " | <span style='background-color: yellow;'>";
                echo "<a href='$urlAnlFldr'>process unanalyzed ABFs ($neededABF)</a>";
                echo "</span>";
            }            
            echo "</div>";
            
            // SHOW PICTURES
            display_thumbnail($files_data);
            display_thumbnail($files_pics);
        }
    }

    function process_cell_updates(){
        // do the thing if a cell ID is channging its comment
        if (!isset($_GET['match'])) return;
        if (!isset($_GET['color'])) return;
        if (!isset($_GET['comment'])) return;
        $cellID=$_GET['match'];
        $color=$_GET['color'];
        if ($color=='') $color='?';
        $comment=strip_tags($_GET['comment']);
        if ($comment=='') $comment='?';
        
        $changedLine="$cellID $color $comment";
               
        // load content of text file and turn it into an array of lines
        if (!is_file("$this->fldr_local/cells.txt")) {
            $content="# automatically created cells.txt\n";
            file_put_contents("$this->fldr_local/cells.txt",$content);
        }
        $f = fopen("$this->fldr_local/cells.txt", "r");
        $raw=fread($f,filesize("$this->fldr_local/cells.txt"));
        fclose($f);
        $raw=explode("\n",$raw);
        $changeMade=False;
        for ($i=0;$i<count($raw);$i++){
            $line=explode(" ",$raw[$i]);
            if ($line[0]==$cellID){
                // we found the line to be replaced
                $raw[$i]=$changedLine;
                $changeMade=True;
            }
        }
        if (!$changeMade){
            // add the line to the bottom of the file
            $raw[]=$changedLine;
        }
        $raw=implode("\n",$raw);
        $f = fopen("$this->fldr_local/cells.txt", "w");
        fwrite($f, $raw);
        fclose($f);
    }
    
    function display_cells($parentMatching=""){
        // display all ABFs grouped by parent and relevant analysis files
        // optionally give it a cell ID and it will display only that and its children
        // optionally give it a date code and it will display all cells from that date
        // group all cell IDs matching our criteria with the files they go with
        $filesByCell=[];
        $lastParent="";
        $files=[];
        
        // silently convert TIFs
        $this->convertAllTiffs();
        
        foreach (array_keys($this->IDs) as $parent){
            if ((!$parentMatching=="") && (!strstr($parent,$parentMatching))) continue;
            if ($parent!=$lastParent && count($files)){
                $filesByCell[]=$files;
                $files=[];
            }
            foreach ($this->IDs[$parent] as $ABFID){
                $files[]="$ABFID.abf";
                if (!count($this->files2)) continue;
                foreach ($this->files2 as $fname){
                    if (startsWith($fname,$ABFID)) {
                        $files[]="$this->analysisFldr/$fname";
                    }
                }
            }
        }
        $filesByCell[]=$files;
        $this->_display_cell_data($filesByCell);
    }
    
    function _display_menu_abfs(){        
        if (is_null($this->cellGroups)){
            // DISPLAY MENU WITHOUT USING CELLS.TXT
            echo "<br>";
            echo "<i style='color: red;'>cells.txt does not exit</i><br>";
            echo "<b>Data Grouped by Cell</b><br>";
            foreach (array_keys($this->IDs) as $cellID) {
                $nABFs = count($this->IDs[$cellID]);
                $url="?view=abf&fldr=$this->fldr&match=$cellID&data";
                echo "<a target='content' href='$url'>$cellID</a> ($nABFs)<br>";
            }
        } else {
            // DISPLAY MENU USING CELLS.TXT
            $nSelected=0;
            foreach (array_keys($this->cellGroups) as $group){
                $c = count($this->cellGroups[$group]);
                if (!$c) continue; // no cells in this group
                $nGroupCells=0; // number of non-ignored cells in this group
                foreach ($this->cellGroups[$group] as $cellID){
                    if (count($this->IDs[$cellID])) $nGroupCells+=1;
                }
                echo "<br><div class='menu_title' style='padding: 10 5 5 0px;'>$group (n=$nGroupCells)</div>";
                foreach ($this->cellGroups[$group] as $cellID){
                    $comment = $this->cellComments[$cellID];
                    $colorcode = $this->cellColors[$cellID];
                    $color = $this->colorcodes[$colorcode];
                    $color = ($color ? $color : 'black'); // color to use if colorcode isn't found
                    $url="?view=abf&fldr=$this->fldr&match=$cellID&data";
                    $nABFs = count($this->IDs[$cellID]);
                    if ($nABFs==0) continue; // skip ABFs seen in cells.txt without their data in this folder
                    $nSelected+=1;
                    echo "<div style='white-space: nowrap;'>";
                    echo "<span class='abftick' id='$nSelected' style='visibility: hidden;'>&raquo;</span>";
                    $nABFsText=sprintf('%02d', $nABFs);
                    echo "<a target='content' href='$url' onclick='setClicked($nSelected)' style='background-color: $color;'>$cellID</a> ($nABFsText) ";
                    echo "<i style='color: #CCC;'>$comment</i></div>";
                }
            }
        }
    }
    
    function display_menu(){
        // show a menu linking to cell views for every parent in this folder
        $bn = basename($this->fldr);
        $fldrParent = dirname($this->fldr);
        $urlSplash="?view=abf&fldr=$this->fldr&splash";
        $urlBrowse="?view=abf&fldr=$fldrParent&browse";
        $urlFrame='http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
        $urlFrame.="?view=abf&fldr=$this->fldr&frames";
        $btnFldr = html_button_copy($this->fldr_network,True,"copy path");
        $btnPage = html_button_copy($urlFrame,True,"copy link");
        $neededABF=count($this->abfsNeedingAnalysis());
        
        echo "<div style='font-family: monospace;'>";
        
        // DISPLAY THE NESTED PATH
        //echo "<div>";
        //echo "<div class='menu_box_browse'>";
        echo"<form class='menu_box_browse' action='$urlFrame' method='post' target='_top'>";
        echo "<div class='menu_title'>Project Browser</div>";
        $pathTestFull = path_network($this->fldr);
        $parts = explode("\\",$pathTestFull);
        for ($indents=0; $indents<count($parts); $indents++){
            $thisPath = implode("/",array_slice($parts, 0, $indents+1));
            echo str_repeat("&nbsp;",$indents);
            $url="?view=abf&fldr=$thisPath&menu";
            echo "<a target='menu' href='$url'>$parts[$indents]</a><br>";
        }   
        
        
        
        echo "<hr>$btnFldr $btnPage ";        
        echo "<input class='button_copy' type='submit' value='refresh here' />";
        echo "</form>";
        
        // DISPLAY ABF PROJECT INFORMATION IF THIS IS AN ABF FOLDER
        $nCells=count($this->IDs);
        $nABFs=0;
        foreach ($this->IDs as $IDs) $nABFs+=count($IDs);
        echo "<div>";
        if ($nCells){
            echo "<div class='menu_box_abf'>";
            echo "<div class='menu_title'>Electrophysiology Project</div>";
            echo "$nCells cells ($nABFs ABFs)<br>";
            if ($neededABF) echo "($neededABF ABFs need analysis)<br>";
            echo "<a target='content' href='$urlSplash'>experiment summary</a><br>";
            echo "</div>";
            $this->_display_menu_abfs();
            echo "<br><br>";
        } else {
            //echo "<div class='menu_box_abf'>";
            //echo "this folder does not contain ABFs";
            //echo "</div>";
        }
        echo "</div><br>";
        
        // DISPLAY ALL FILES IN THIS PATH
        //echo "<div>";
        echo "<div class='menu_box_browse'>";
        echo "<div class='menu_title'>Folder Contents</div>";
        $folder = realpath($this->fldr);
        foreach (scandir($folder) as $path){
            if ($path == "." || $path == "..") continue;
            $path2 = realpath($folder.'/'.$path);
            $webPath=path_web($path2);
            if (count($this->IDs) and endsWith(strtolower($path2),".abf")) continue;
            if (count($this->IDs) and endsWith(strtolower($path2),".tif")) continue;
            $urlMenu="?view=abf&fldr=$path2&menu";               

            if (is_dir($path2)) {
                // IT'S A DIRECTORY - always link to it, optionally highlight it
                if (strstr($path,"progress")||strstr($path,"notes")) {       
                    echo "<a href='$urlMenu' style='background-color: #FFCCCC;'>$path</a><br>";  
                } else if (strstr($path,"data") || glob_count($path2)) {
                    echo "<a href='$urlMenu' style='background-color: #FFFFAA;'>$path</a><br>";  
                } else {
                    echo "<a href='$urlMenu'>$path</a><br>";
                }
            } else {
                // IT'S A FILE - only link to it if it can be displayed in the content window
                if (endsWith($path2,".txt")) {       
                    echo "<a href='$webPath' target='content' style='background-color: #CCCCFF;'>$path</a><br>";   
                } else if (endsWith($path2,".jpg")||endsWith($path2,".png")) {       
                    echo "<a href='$webPath' target='content' style='background-color: #FFCCFF;'>$path</a><br>";   
                } else if (endsWith($path2,".pdf")) {       
                    echo "<a href='$webPath' target='content' style='background-color: #FFCCCC;'>$path</a><br>";    
                } else if (endsWith($path2,".url")||endsWith($path2,".html")||endsWith($path2,".php")) {       
                    echo "<a href='$webPath' target='content' style='background-color: #CCCCFF;'>$path</a><br>";    
                } else {                    
                    echo "$path<br>";
                }
            }
        }
        echo "</div><br>";
        
        
        echo "</div>";

    }

    function display_frames(){
        // create a frame layout to display the menu and data
        $url = $_SERVER['QUERY_STRING'];
        $url = str_replace("&frames",'',$url);
        echo "<frameset cols='300px,100%'>";
        echo "<frame name='menu' src='?$url&menu' />";
        echo "<frame name='content' src='?$url&splash' />";
        echo "</frameset>";
    }
    
    function abf_parent($abfIDseek){
        // return the ABF ID of the parent
        foreach (array_keys($this->IDs) as $parent){
            $children=$this->IDs[$parent];
            foreach ($children as $abfID){
                if ($abfID==$abfIDseek){
                    return $parent;
                }
            }
        }
        return "ORPHAN";
    }
    
    function display_origin_commands(){
        $abfProtos=[];
        $abfsByProtocol=[];
        foreach (array_keys($this->IDs) as $parent){
            $children=$this->IDs[$parent];
            foreach ($children as $abfID){
                $fname=path_local("$this->fldr/$abfID.abf");
                $protocol = abf_protocol($fname);
                if (!in_array($protocol,array_keys($abfsByProtocol))) $abfsByProtocol[$protocol]=[];
                $abfsByProtocol[$protocol]=array_merge($abfsByProtocol[$protocol],[$abfID]);
                $abfProtos[$abfID]=$protocol;
            }
        }
        
        
        /////////////////////////////////////////////////////////////////////////////////
        // PROTOCOL ORIGIN COMMANDS /////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////
        
        echo "<h1>Origin Analysis Commands(v1)</h1>";
        $protocol="0111 continuous ramp";
        if (in_array($protocol,array_keys($abfsByProtocol))){            
            echo "<div style='background-color: #CCCCFF; font-family: monospace; padding: 10px; border: 1px solid #6666AA;'>";
            echo "<b># First AP threshold and rheobase ($protocol)</b><br><br>";
            foreach (array_keys($this->IDs) as $parent){
                echo "setpath \"$this->fldr\\$parent.abf\"; ";
                //$comment = $this->cellComments[$parent];                
                //echo "modifyTags \"$comment\"; ";
                echo "nextABFprotocol 0111; ";
                echo "cjfmini; ";
                echo "<br>";
            }
            echo "</div><br><br>";
        }
        
        echo "<h1>Origin Analysis Commands(v2)</h1>";
        
        $protocol="0112 steps dual -50 to 150 step 10";
        if (in_array($protocol,array_keys($abfsByProtocol))){            
            echo "<div style='background-color: #AAFFAA; font-family: monospace; padding: 10px; border: 1px solid #66AA66;'>";
            echo "<b># AP Gain (low power) ($protocol)</b>";
            echo "<br><br># gain after regular step:<br>m1 120; m2 670;";
            echo "<br><br># gain after hyperpolarizing step:<br>m1 1620; m2 2170;";
            echo "<br><br>";
            foreach ($abfsByProtocol[$protocol] as $abfID){
                $parent=$this->abf_parent($abfID);
                echo "parent=$parent; setpath \"$this->fldr\\$abfID.abf\"; ";
                $comment = $this->cellComments[$parent];                
                //echo "modifyTags \"$comment\"; ";
                echo "cjfmini;<br>";
            }
            echo "<br>getgroups 3 _EVN freqInBin.S;";
            echo "<br>RunOnBooks UpdateSummarySheets;";
            echo "</div><br><br>";
        }
        
        $protocol="0113 steps dual -100 to 300 step 25";
        if (in_array($protocol,array_keys($abfsByProtocol))){            
            echo "<div style='background-color: #AAFFAA; font-family: monospace; padding: 10px; border: 1px solid #66AA66;'>";
            echo "<b># AP Gain (medium power) ($protocol)</b><br>";
            echo "<br><br># gain after regular step:<br>m1 120; m2 670;";
            echo "<br><br># gain after hyperpolarizing step:<br>m1 1620; m2 2170;";
            echo "<br><br>";
            foreach ($abfsByProtocol[$protocol] as $abfID){
                $parent=$this->abf_parent($abfID);
                echo "parent=$parent; setpath \"$this->fldr\\$abfID.abf\"; ";
                $comment = $this->cellComments[$parent];                
                echo "modifyTags \"$comment\"; ";
                echo "cjfmini;<br>";
            }
            echo "<br>getgroups 3 _EVN freqInBin.S;";
            echo "<br>RunOnBooks UpdateSummarySheets;";
            echo "</div><br><br>";
        }
        
        $protocol="0201 memtest";
        if (in_array($protocol,array_keys($abfsByProtocol))){            
            echo "<div style='background-color: #AAFFAA; font-family: monospace; padding: 10px; border: 1px solid #66AA66;'>";
            echo "<b># Voltage-Clamp Membrane Test ($protocol)</b><br>";
            foreach ($abfsByProtocol[$protocol] as $abfID){
                $parent=$this->abf_parent($abfID);
                echo "parent=$parent; setpath \"$this->fldr\\$abfID.abf\"; ";
                $comment = $this->cellComments[$parent];                
                echo "modifyTags \"$comment\"; ";
                echo "memtest;<br>";
            }
            //echo "<br>getgroups 1 _mStats mean.S;";
            //echo "<br>RunOnBooks UpdateSummarySheets;";
            echo '<br>getgroups 1 _MT Ih.SS;<br>getgroups 3 _MT Rm.SS;<br>getgroups 4 _MT Cm.SS;<br>getgroups 7 _MT Id.SS;<br>';
            echo '<br>runonsheets .SS. "addx; rave;";';
            echo "</div><br><br>";
        }
        
        $protocol="0203 IV fast";
        if (in_array($protocol,array_keys($abfsByProtocol))){            
            echo "<div style='background-color: #AAFFAA; font-family: monospace; padding: 10px; border: 1px solid #66AA66;'>";
            echo "<b># Voltage-Clamp IV Curve ($protocol)</b><br>";
            echo "# enable lowpass filter<br>";
            echo "<br>modifyTags GROUPNAME;<br>m1 500; m2 1000;<br><br>";
            foreach ($abfsByProtocol[$protocol] as $abfID){
                $parent=$this->abf_parent($abfID);
                echo "parent=$parent; setpath \"$this->fldr\\$abfID.abf\"; ";
                $comment = $this->cellComments[$parent];                
                echo "modifyTags \"$comment\"; ";
                echo "getstats;<br>";
            }
            echo "<br>getgroups 1 _mStats mean.S;";
            echo "<br>RunOnBooks UpdateSummarySheets;";
            echo "</div><br><br>";
        } 
        
        $protocol="0303 IC 20s IC ramp drug";
        if (in_array($protocol,array_keys($abfsByProtocol))){            
            echo "<div style='background-color: #AAFFAA; font-family: monospace; padding: 10px; border: 1px solid #66AA66;'>";
            echo "<b># IC repeated ramps ($protocol)</b><br>";
            foreach ($abfsByProtocol[$protocol] as $abfID){
                $parent=$this->abf_parent($abfID);
                echo "parent=$parent; setpath \"$this->fldr\\$abfID.abf\"; ";
                $comment = $this->cellComments[$parent];                
                echo "modifyTags \"$comment\"; ";
                echo "cjfmini;<br>";
            }
            echo "<br>getgroups 3 _EVN freqInBin.S;";
            echo "<br>RunOnBooks UpdateSummarySheets;";
            echo "</div><br><br>";
        }
        
        $protocol="0406 VC 10s MT-50";
        if (in_array($protocol,array_keys($abfsByProtocol))){            
            echo "<div style='background-color: #AAFFAA; font-family: monospace; padding: 10px; border: 1px solid #66AA66;'>";
            echo "<b># VC repeated memtest ($protocol)</b><br>";
            foreach ($abfsByProtocol[$protocol] as $abfID){
                $parent=$this->abf_parent($abfID);
                echo "parent=$parent; setpath \"$this->fldr\\$abfID.abf\"; ";
                $comment = $this->cellComments[$parent];                
                echo "modifyTags \"$comment\"; ";
                echo "memtest;<br>";
            }
            echo "<br>getgroups 1 _MT Ih.S;<br>getgroups 2 _MT Ra.S;<br>getgroups 3 _MT Rm.S;";
            echo "<br>runonsheets .S. \"letters; addx 1/6; ccave;\"";
            echo "<br>RunOnBooks UpdateSummarySheets;";
            echo "</div><br><br>";
        }
        
        $protocol="0911 VC 15s stim PPR varied";
        if (in_array($protocol,array_keys($abfsByProtocol))){            
            echo "<div style='background-color: #AAFFAA; font-family: monospace; padding: 10px; border: 1px solid #66AA66;'>";
            echo "<b># ISI assessment ($protocol)</b><br>";
            echo "# use same settings as protocol 0912<br>";
            echo "# set evoked markers for sweep 2 (always discard sweep 1)<br>";
            echo "<br>modifyTags GROUPNAME;<br>m1 500; m2 1000;<br><br>";
            foreach ($abfsByProtocol[$protocol] as $abfID){
                $parent=$this->abf_parent($abfID);
                echo "parent=$parent; setpath \"$this->fldr\\$abfID.abf\"; ";
                echo "getevoked;<br>";
            }
            echo "<br>RunOnBooks UpdateSummarySheets;";
            echo "</div><br><br>";
        }
        
        $protocol="0912 VC 20s stim PPR 40ms";
        if (in_array($protocol,array_keys($abfsByProtocol))){            
            echo "<div style='background-color: #AAFFAA; font-family: monospace; padding: 10px; border: 1px solid #66AA66;'>";
            echo "<b># Paired Pulse Analysis ($protocol)</b><br>";
            echo "# enable lowpass filter<br>";
            echo "# enable Cm in membrane test<br>";
            echo "# enable evoked stats (channel 0, min and mean) and manually place evoked markers<br>";
            echo "# enable phasic analysis<br>";
            echo "# configure event detection<br>";
            echo "<br>modifyTags GROUPNAME;<br>m1 5000; m2 20000;<br><br>";
            
            foreach ($abfsByProtocol[$protocol] as $abfID){
                $parent=$this->abf_parent($abfID);
                echo "parent=$parent; setpath \"$this->fldr\\$abfID.abf\"; ";
                echo "memtest; getevoked; getstats; cjfmini;<br>";
            }
            echo "<br>RunOnBooks UpdateSummarySheets;";
            echo "</div><br><br>";
        }
        
        
        
        
        
        
        
        
        
        
        
        /////////////////////////////////////////////////////////////////////////////////
        // ABFS LISTED BY PROTOCOL //////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////

        
        echo "<h2>PROTOCOLS (by ABF)</h2>";
        echo "<code>";
        foreach (array_keys($abfProtos) as $abfID){
            $protocol=$abfProtos[$abfID];
            if (in_array($abfID,array_keys($this->IDs))){
                echo "<br><b>$abfID</b><br>";
            }
            echo "$abfID.abf ($protocol)<br>";
        }
        echo "</code>";
        
        
        echo "<h2>ABFs (by protocol)</h2>";
        echo "<code>";
        foreach (array_keys($abfsByProtocol) as $protocol){
            echo "<br><b>$protocol</b><br>";
            foreach ($abfsByProtocol[$protocol] as $abfID){
                $parent=$this->abf_parent($abfID);
                echo "[$parent] $abfID.abf<br>";
            }
        }
        echo "</code>";
        
        
    }

    function display_splash(){
        // shows experiment info
        if (count($this->IDs)){
            echo "<br><b>ABF Project Summary</b><br>";
            $btn = html_button_copy(path_network($this->fldr));
            echo "<code>$this->fldr $btn </code><hr>";      

            // show information about processing/analyzing ABFs
            $neededABF=count($this->abfsNeedingAnalysis());
            if ($neededABF){
                $urlAnlFldr = "?".$_SERVER['QUERY_STRING']."&analyzeFolder";
                echo "<div class='menu_box_abf' style='font-size: 150%; font-weight: bold; padding: 20px;'>";
                echo "ABFs files require analysis!<br>";
                echo "<a href='$urlAnlFldr'>Click here to analyze $neededABF ABFs</a>";
                echo "</div>";
                
            } else {
                display_message("All ABFs have been analyzed.");
            }
            
            // show cells.txt
            display_file($this->fldr."\\experiment.txt");
            display_file($this->fldr."\\cells.txt");
            $this->display_origin_commands();
            
        } else {
            echo "<br>";
            $msg="Use the left menu to navigate to a folder containing ABFs<br>";
            display_message($msg);
        }        
    }

    function display_help(){
        // if a view argument isn't given, show what's available
        $views = ["menu","frames","data","splash","browse"];
        echo "<h3>An extra argument is required:</h3>";
        foreach ($views as $view){
            $url = $_SERVER['QUERY_STRING'];
            echo "<li> <a href='?$url&$view'>$view</a><br>";
        }
    }
}

function abf_protocol($abfFile, $comment=False){
	// opens the ABF file in binary mode to pull the protocol information
    // it can return the protocol filename or the protocol comment
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

?>
