<?php

// init the ABFfolder class (scans directory)
$fldr = isset($_GET['fldr']) ? $_GET['fldr'] : '';
$AF = new ABFfolder($fldr);

// menu
if (isset($_GET['menu'])){
    $AF->display_menu();
} else if (isset($_GET['frames'])) {
    $AF->display_frames();
} else if (isset($_GET['data'])) {
    $match = isset($_GET['match']) ? $_GET['match'] : '';
    $AF->display_cells($match);
} else if (isset($_GET['splash'])) {
    $AF->display_splash();
} else if (isset($_GET['browse'])) {
    $AF->display_browser();
} else {
    $AF->display_help();
}

?>
