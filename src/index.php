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
$col = isset($_GET['col']) ? $_GET['col'] : '';
$str = isset($_GET['str']) ? $_GET['str'] : ''; // can be used for generic string passing
$num = isset($_GET['num']) ? $_GET['num'] : ''; // can be used for generic number passing
if ($page=='') $page="splash";

msg("request started ".timestamp());
msg("page: [$page]");
msg("project: [$project]");
msg("abfID: [$abfID]");
msg("cellID: [$cellID]");
msg("action: [$action]");
msg("col: [$col]");
msg("str: [$str]");
msg("num: [$num]");


//======================================================================
// ACTION HANDLING
//======================================================================

if (sizeof(explode("_",$action))>1){
    $actionValue=explode("_",$action,2)[1];
    $action=explode("_",$action,2)[0];
}

switch ($action){
    
    case "cellSet":
        msg("editing $project,$cellID,$col,$str");
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

if ($page){
    $fname="templates/$template/page_$page.php";
    if (file_exists($fname)){
        msg("including content from [$fname]");
        include($fname);
    } else {
        echo "ERROR: page [$page] not found ($fname)";
    }
}

?>