<?php include("swhlab.php"); ?>
<?php

$project = isset($_GET['project']) ? $_GET['project'] : '';
$abfID = isset($_GET['abfID']) ? $_GET['abfID'] : '';
$cellID = isset($_GET['cellID']) ? $_GET['cellID'] : '';
$page = isset($_GET['page']) ? $_GET['page'] : '';
if ($page=='') $page="splash";

msg("request started ".timestamp());
msg("page: [$page]");
msg("project: [$project]");
msg("abfID: [$abfID]");
msg("cellID: [$cellID]");

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