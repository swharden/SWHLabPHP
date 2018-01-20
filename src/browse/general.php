<?php

/*

DESIGN CONSIDERATIONS:
    Keep all php files to be include()'d in this same folder.

*/






// #######################################
// ### USEFUL FUNCTIONS FOR DEVELOPERS ###
// #######################################

// display a message in a <div> block
function error_box($msg){
    $style ='margin: 5px; padding: 5px; ';
    $style.='border: 3px solid #FF0000; ';
    $style.='background-color: #FFAAAA; ';
    echo "\n\n<div style='$style'>$msg</div>\n\n";
}

// display a message in a <span> block
function error_message($msg){
    $style='background-color: #FFAAAA; ';
    echo "\n\n<span style='$style'>$msg</span>\n\n";
}
















// ######################################
// ### PATH SCANNING AND MODIFICATION ###
// ######################################


function dirscan_files($rootFolder){
    // return full paths of just the files in a folder
    $fileNames=[]; // will get filled
    foreach (scandir($rootFolder) as $name){
        if ($name=='.' || $name=='..') continue;
        if (!is_dir($rootFolder."/".$name)) $fileNames[]=$name;
    }
    sort($fileNames);
    return $fileNames;
}

function dirscan_folders($rootFolder){
    // return full paths of just the folders in a folder
    $folderNames=[]; // will get filled
    foreach (scandir($rootFolder) as $name){
        if ($name=='.' || $name=='..') continue;
        if (is_dir($rootFolder."/".$name)) 
            $folderNames[]=$name;
    }
    sort($folderNames);
    return $folderNames;
}

// tidy-up a file or folder path and make everything backslashes
function path_clean($path){
    $path = trim($path);
    $path = str_replace("/","\\",$path);
    while (strstr($path,"\\\\")) $path = str_replace("\\\\","\\",$path);
    return $path;
}

// given an X:\ path, return a D:\X_Drive\ path
function path_xdrive_to_local($path){
    $path=path_clean($path);
    $path=str_replace("x:","X:",$path);
    $path=str_replace("X:","D:\X_Drive",$path);
    return path_clean($path);
}

function file_age_string($ageSec){
    
    // determine file age
    //$ageSec=time()-filemtime($fname);
    $ageMin=$ageSec/60;
    $ageHr=$ageMin/60;
    $ageDy=$ageHr/24;
    $ageYr=$ageDy/365.25;
    $ageString=date("F d Y H:i:s.", filemtime($fname));
    
    // determine string formatting
    if ($ageHr<1) $ageString=sprintf("%.01f min", $ageMin);
    else if ($ageDy<1) $ageString=sprintf("%.01f hr", $ageHr); 
    else if ($ageDy<90) $ageString=sprintf("%.01f d", $ageDy);
    else $ageString=sprintf("%.01f yr", $ageYr);

    return $ageString;
}

function html_link_file_age($file_path, $title=null, $url=null){
    // display a link to a file with a color-coded tag to indicate how recently it was modified
    $file_age_sec = time()-filemtime($file_path);
    $file_age_days = $file_age_sec/60/60/24;
    $file_age_string = file_age_string($file_age_sec);
    if (!$title) $title=basename($file_path);
    if (!$url) $url=$file_path;
    $str_age = file_age_string($file_age_sec);
    echo "<a href='$url'>$title</a> ";

    $style='font-size: 80%; font-family: monospace; padding: 0px 3px 0px 3px; ';

    if ($file_age_days<2) $style.="background-color: #FFFF99; color: #333; border: 1px solid #333";
    else if ($file_age_days<14) $style.="background-color: #EEEECC; color: #999933; border: 1px solid #999933";
    else if ($file_age_days<28) $style.="background-color: #DDEEDD; color: #666; border: 1px solid #666";
    else $style.="background-color: #EEE; color: #999; border: 1px solid #999";
    
    echo"<span style='$style'>$str_age</span>";
}





















// ######################################
// ### CLIPBOARD HTML CODE GENERATION ###
// ######################################

// echo the text needed to enable clipboard javascript (place this in <head>)
function clipboard_headScript(){
    echo '
    <script>
    function copyToClipboard(elementId) {
      // Create a "hidden" input
      var aux = document.createElement("input");
      // Assign it the value of the specified element
      aux.setAttribute("value", document.getElementById(elementId).innerHTML);
      // Append it to the body
      document.body.appendChild(aux);
      // Highlight its content
      aux.select();
      // Copy the highlighted text
      document.execCommand("copy");
      // Remove it from the body
      document.body.removeChild(aux);
    }
    </script>
    ';
    
}

function random_string($length, $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',$str='') {
    // return a random string of a certain length
    $count = strlen($charset);
    while ($length--) {
        $str .= $charset[mt_rand(0, $count-1)];
    }
    return $str;
}

function clipboard_html_button($text, $invisible=True, $label="copy"){
    // display a string and add a button to copy to clipboard
    // a javascript script has to go in the <head> for it to work
    /*
    <script>
    function copyToClipboard(elementId) {
      var input = document.createElement("input");
      document.body.appendChild(input);
      input.value=document.getElementById(elementId).innerText;
      input.select();
      document.execCommand("copy");
      document.body.removeChild(input);
    }
    </script>
    */
    $uniqueID=random_string(10);
    $style="";
    if ($invisible) {$style="display: none;";}
    $html = "<span style=\"$style\" id=\"$uniqueID\">$text</span> ";
    $html .= "<button class='button_copy' ";
    $html .= "onclick=\"copyToClipboard('$uniqueID')\">$label</button>";
    return $html;
}


















// #################################
// ### HTML GENERATION FOR LINKS ###
// #################################

function link_origin_file($fname, $title=""){
    if (!$title=="") $title.="<br>";
    $title.=basename($fname);
    $ageString=file_age_string($fname);
    $ageStyle=file_age_style($fname);

    echo "<div style='font-family: monospace; line-height: 100%;'>";
    echo html_button_copy($fname, True, "copy");
    echo "<span style='$ageStyle; padding-left: 4px; padding-right: 4px;'>";
    echo basename($fname);
    echo " <span style='font-size: 70%'>$ageString</span></span>";
    echo "</div>";
}

function link_origin_folder($folder, $title=""){
    if ($title=="") $titleMsg = basename($folder);
    echo "<div style='font-weight: bold; line-height: 100%; padding-top: 20px;'>$title</div>";
    echo "<div style='font-family: monospace; position: relative;'>$folder\\</div>";

    if (!is_dir($folder)){
        echo "<div style='font-family: monospace; background-color: #FFAAAA;'>DIRECTORY NOT FOUND</div>";
        return;
    }

    $files = scandir($folder);
    $flies = sort($files);
    foreach ($files as $filename){
        $filepath=$folder."\\".$filename;
        if (!is_file($filepath)) continue;
        if (strstr($filename,".opj")){
            link_origin_file($filepath);
        }        
    }
}














// ############################
// ### HTML GENERATION MISC ###
// ############################


// given the path to a markdown file, render and echo it as HTML
function markdown_file_render($markdown_filename){

    // ensure file exists
    if (is_file($markdown_filename)){
        // using it as-is, no big deal
    } else if (path_xdrive_to_local($markdown_filename)){
        $markdown_filename = path_xdrive_to_local($markdown_filename);
    } else {
        error_box("<b>FILE DOES NOT EXIST:</b><br><code>$markdown_filename</code>");
        return;
    }

    // ensure file is not empty
    if (filesize($markdown_filename)==0){
        error_box("<b>FILE IS EMPTY:</b><br><code>$markdown_filename</code>");
        return;
    }

    // render and echo the file
    include_once('Parsedown.php');
    $Parsedown = new Parsedown();
    $f = fopen($markdown_filename, "r");
    $raw = fread($f,filesize($markdown_filename));
    fclose($f);
    echo $Parsedown->text($raw);

    // add a button to edit the file
    $markdown_filename = path_clean($markdown_filename);
    echo "<div align='right' style='font-size: 80%; color: #CCC;'>";
    echo "$markdown_filename</div>";
    //echo "<a href='$url' style='color: #CCC'>edit $url</a></div>";
    //echo "<i>Edit this text block: $markdown_filename</i></div>";
}


















?>