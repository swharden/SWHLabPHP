<?php 
// this script is the ONLY file which should be called by a browser.
// it handles the request and determines the page to display or action to take.


//======================================================================
// ENVIRONMENT SETUP
//======================================================================

include("swhlab.php");

$project = isset($_GET['project']) ? $_GET['project'] : '';
$abfID = isset($_GET['abfID']) ? $_GET['abfID'] : '';
$cellID = isset($_GET['cellID']) ? $_GET['cellID'] : '';
$page = isset($_GET['page']) ? $_GET['page'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';
$str = isset($_GET['str']) ? $_GET['str'] : '';
$col = isset($_GET['col']) ? $_GET['col'] : '';
if ($page=='') $page="splash";

msg("request started ".timestamp());
msg("page: [$page]");
msg("project: [$project]");
msg("abfID: [$abfID]");
msg("cellID: [$cellID]");
msg("action: [$action]");
msg("col: [$col]");
msg("str: [$str]");


//======================================================================
// ACTION HANDLING
//======================================================================

if (sizeof(explode("_",$action))>1){
    $actionValue=explode("_",$action,2)[1];
    $action=explode("_",$action,2)[0];
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
            $raw[$lineNum]="$cellID $newColor $message";
        }
    }
    $raw=implode("\n",$raw);
    
    // save the updated file to disk
    $f = fopen($cellFile, "w");
    fwrite($f, $raw);
    fclose($f);    
}

switch ($action){
    
    case "cellSet":
        cell_edit($project,$cellID,$col,$str);
        break;
    
    case "cell":
        echo "ACTION: DO [$actionValue] TO THIS CELL.";
        break;
        
    case "":
        //echo "NO ACTION REQUIRED.";
        break;
        
    default:
        //echo "UNKNOWN ACTION.";
        break;
}

//======================================================================
// PAGE GENERATION
//======================================================================

switch ($page){
    
    case "menu":
        msg("generating menu page...");
        include("templates/$template/page_menu.php");
        break;
        
    case "abfID":
        msg("generating single ABF page...");
        include("templates/$template/page_abfID.php");
        break;
        
    case "cellID":
        msg("generating page for all ABFs assocaited with a cell...");
        include("templates/$template/page_cellID.php");
        break;
    
    case "project":
        include("templates/$template/page_project.php");
        break;
        
    case "frames":
        include("templates/$template/page_frames.php");
        break;
        
    case "splash":
        include("templates/$template/page_splash.php");
        break;
        
    default:
        msg("ERROR: page not recognized");
        break;
        
}

?>