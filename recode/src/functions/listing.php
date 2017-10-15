<?php

function link_ABF_folder($folder){
    // echo HTML link to view a given ABF project folder
    $folder = realpath($folder);
    $subFolderBN = basename($folder);
    $nABFs = glob_count($folder);
    $url="?view=abf&fldr=$folder&frames";
    if ($nABFs) {
        echo "<code><a style='background-color: #FFFFAA;' href='$url'>";
        echo "$subFolderBN</a></span> ($nABFs ABFs)</code><br>";
    } else {
        echo "<code>$subFolderBN</code><br>";
    }
}

function list_ABF_project_folders($folder){
    // displays HTML to provide links to each subfolder
    echo "<br><b><code>ABF Project Listing for: $folder</code></b><br>";
    $folders = scandir($folder);
    rsort($folders);
    foreach ($folders as $subFolder){
        if ($subFolder=='.' || $subFolder=='..') continue;
        $subFolder=$folder.'/'.$subFolder;
        if (!is_dir($subFolder)) continue; // skip files
        link_ABF_folder($subFolder);
    }
}

function list_LS_project_folders($folder){
    echo "<code>";
    echo "<br><b>$folder</b><br>";
    $folders = scandir($folder);
    rsort($folders);
    foreach ($folders as $subFolder){
        if ($subFolder=='.' || $subFolder=='..') continue;
        $subFolder=$folder.'/'.$subFolder;
        if (!is_dir($subFolder.'/linescans/')) continue; // skip files
        $subFolder = realpath($subFolder);
        $nLinescans=glob_count($subFolder,'/linescans/*');
        $subFolderBN=basename($subFolder);
        echo "<a href=''?view=LS2&fldr=$subFolder'>$subFolderBN</a> ($nLinescans)<br>";
    }
    echo "</code>";
}

?>