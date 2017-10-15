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
    var $file_cells; // path to cell.txt (or FALSE if it doesn't exist)
    
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
        if (!file_exists($this->fldr_local)) {
            display_error("FOLDER DOES NOT EXIST:<br>$this->fldr_local");
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
        if (!is_dir("$this->fldr_local/swhlab/")) mkdir("$this->fldr_local/swhlab/");
        echo "<code><b>CONVERTING TIFS:</b> ";
        foreach ($tifFiles as $fname){
            $fname1="$this->fldr_local\\$fname";
            $fname2="$this->fldr_local\\swhlab\\$fname.jpg";
            echo "$fname ";
            $cmd="convert -contrast-stretch .05% \"$fname1\" \"$fname2\" ";
            flush();ob_flush(); // update the browser
            exec($cmd);
        }
        echo "<b>DONE!</b></code><hr>";
        $this->scanFiles(); // update folder scans
    }
    
    function abfNeedsAnalysis($cellID){
        // check if an ABF needs analysis (based on lack of files in ./swhlab/)
        if (!count($this->files2)) return True;
        foreach ($this->files2 as $fname2) if (startsWith($fname2,$cellID)) return False;
        return True;
    }

    function abfsNeedingAnalysis(){
        // return a list of ABF IDs needing analysis
        $needAnalysis=[];
        foreach (array_keys($this->IDs) as $parentID){
            foreach ($this->IDs[$parentID] as $cellID){
                if($this->abfNeedsAnalysis($cellID)) $needAnalysis[]=$cellID;
            }
        }
        return $needAnalysis;
    }
    
    function tifsNeedingAnalysis(){
        // return a list of TIF files needing conversion
        $needAnalysis=[];
        foreach ($this->files as $fname1){
            if (!endsWith(strtolower($fname1),".tif")) continue;
                if (!in_array("$fname1.jpg",$this->files2)) $needAnalysis[]=$fname1;
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
        $this->file_cells = (in_array("cells.txt",$this->files) ? $this->fldr_local."/cells.txt" : False);
        //if (file_exists($this->fldr2)) $this->files2 = scandir2($this->fldr2);
        $this->files2 = (file_exists($this->fldr2) ? scandir2($this->fldr2) : []);

        // determine cell parent IDs
        $files_ABF = [];
        $this->cells=[];
        foreach ($this->files as $fname) {
            if (!endsWith($fname, ".abf")) continue;
            $files_ABF[] = $fname;
            $ABF_basename = str_replace(".abf", "", $fname);
            if (in_array($ABF_basename . ".tif", $this->files)) $this->cells[] = $ABF_basename;
        }

        // determine IDs of parents
        $parent = "ORPHAN";
        $this->IDs = array();
        foreach ($files_ABF as $fname){
            $ABF_basename = str_replace(".abf", "", $fname);
            if (in_array($ABF_basename,$this->cells)) $parent = $ABF_basename;
            //echo "$parent $fname<br>";
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
           
    function scanCellsFile(){
        // scan cells.txt and populate cell colors, comments, and groups.
        if (!$this->file_cells) return; // only proceed if cells.txt exists        
        $this->cellColors=[]; // keyed by cell ID, contains colorcode of each cell
        $this->cellComments=[]; // keyed by cell ID, contains comment of each cell
        $this->cellGroups=[]; // key is group name, contents are cell IDs
        
        $lastCategory = "ungrouped";
        $cellsAccountedFor=[];
        
        // load content of text file and turn it into an array of lines
        $f = fopen($this->file_cells, "r");
        $raw=fread($f,filesize($this->file_cells));
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
            if (count($line)<3) continue; // ignore lines which don't have 3 parts
            $cellID = $line[0];   
            $cellsAccountedFor[]=trim($cellID);
            $this->cellColors[$cellID]=trim($line[1]);
            $this->cellComments[$cellID]=trim($line[2]);
            
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
            
            // silently convert TIFs
            $this->convertAllTiffs();

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
            //$comment = (strlen($comment) ? $comment : "[none]");
            
            echo "<div style='background-color: $color; padding: 10px;'>";
            echo "<b>CELL ID: <a href='$url'>$ID</a></b> $btn<br>";
            echo "<code>$this->fldr_network</code> $btnFldr<br>";
            echo "Cell comment: <i>$comment</i>";
            echo "</div>";
            
            // CELL COMMENT AND COLOR ASSIGNMENT
            echo "<div style='background-color: #DDD; padding: 10px;'>";
            echo "<div style='line-height: 150%;'><b>Cell Notes:</b></div>";
            echo "<form action='' method='get'>";
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
                echo "<code>$bn $btn $protocol ($filesize)</code><br>";
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
        $comment=strip_tags($_GET['comment']);
        $changedLine="$cellID $color $comment<br>";
               
        // load content of text file and turn it into an array of lines
        $f = fopen($this->file_cells, "r");
        $raw=fread($f,filesize($this->file_cells));
        fclose($f);
        $raw=explode("\n",$raw);
        for ($i=0;$i<count($raw);$i++){
            $line=explode(" ",$raw[$i]);
            if ($line[0]==$cellID){
                $raw[$i]=$changedLine;
            }
        }
        $raw=implode("\n",$raw);
        $f = fopen($this->file_cells, "w");
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
            foreach (array_keys($this->cellGroups) as $group){
                $c = count($this->cellGroups[$group]);
                echo "<br><div style='font-weight: bold; text-decoration: underline;'>$group</div>";
                foreach ($this->cellGroups[$group] as $cellID){
                    $comment = $this->cellComments[$cellID];
                    $colorcode = $this->cellColors[$cellID];
                    $color = $this->colorcodes[$colorcode];
                    $color = ($color ? $color : 'black'); // color to use if colorcode isn't found
                    $url="?view=abf&fldr=$this->fldr&match=$cellID&data";
                    echo "<div style='white-space: nowrap;'>";
                    echo "<a target='content' href='$url' style='background-color: $color;'>$cellID</a> ";
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
        $urlFrame=str_replace(" ","%20",$urlFrame);
        
        echo "<div style='font-family: monospace;'>";
        
        // DISPLAY THE NESTED PATH
        echo "<div>";
        echo "<b>Project Browser</b><br>";
        $pathTestFull = path_network($this->fldr);
        $parts = explode("\\",$pathTestFull);
        for ($indents=0; $indents<count($parts); $indents++){
            $thisPath = implode("/",array_slice($parts, 0, $indents+1));
            echo str_repeat("&nbsp;",$indents);
            $url="?view=abf&fldr=$thisPath&menu";
            echo "<a target='menu' href='$url'>$parts[$indents]</a><br>";
        }   
        echo "</div><br>";
        
        // DISPLAY ABF PROJECT INFORMATION IF THIS IS AN ABF FOLDER
        echo "<div>";
        if (count($this->IDs)){
            echo "<b>Electrophysiology Project</b><br>";
            echo "<a target='content' href='$urlSplash'>experiment summary</a><br>";
            echo "<a target='content' href='$urlSplash'>analyze new data</a><br>";
            $this->_display_menu_abfs();
        } else {
            echo "this folder does not contain ABFs";
        }
        echo "</div><br>";
        
        // DISPLAY ALL FILES IN THIS PATH
        echo "<div>";
        echo "<b>Folder Contents</b><br>";
        $folder = realpath($this->fldr);
        foreach (scandir($folder) as $path){
            if ($path == "." || $path == "..") continue;
            $path2 = realpath($folder.'/'.$path);
            if (count($this->IDs) and endsWith(strtolower($path2),".abf")) continue;
            if (count($this->IDs) and endsWith(strtolower($path2),".tif")) continue;
            $urlMenu="?view=abf&fldr=$path2&menu";               
            if (glob_count($path2)){
                // folder contains ABFs
                echo "<a href='$urlMenu' target = 'menu'>";
                echo "<span style='background-color: #FFFFAA;'>$path</span>";
                echo "</a><br>";
            } else {
                // folder not containing ABFs
                if (is_dir($path2)) echo "<a href='$urlMenu'>$path</a><br>";
                else echo "$path<br>";
            }
        }
        echo "</div><br>";
        
        $btn = html_button_copy($urlFrame);
        echo "<br><br><hr>link to this page: $urlFrame $btn";        
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

    function display_splash(){
        // shows experiment info
        if (count($this->IDs)){
            echo "<br><b>ABF Project Summary</b><br>";
            $btn = html_button_copy(path_network($this->fldr));
            echo "<code>$this->fldr $btn </code><hr>";      

            $neededABF=count($this->abfsNeedingAnalysis());
            if ($neededABF){
                $urlAnlFldr = "?".$_SERVER['QUERY_STRING']."&analyzeFolder";
                
                echo "ABFs files require analysis:<br>";
                echo "<span style='background-color: yellow;'>";
                echo "<a href='$urlAnlFldr'>process unanalyzed ABFs ($neededABF)</a>";
                echo "</span>";
            } else {
                echo "All ABFs have been analyzed.";
            }
            
        } else {
            echo "<br><br><br>use the left menu to select a folder containing ABFs...";
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
