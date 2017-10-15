<?php

function html_safe_text($msg){
    // strip or replcae HTML characters with HTML codes
    $msg = str_replace("\n","<br>",$msg);
    return $msg;
}

// display a message in a div block
function display_error($msg){
    // display a message in a div block
    // accepts string or array
    if (gettype($msg)=='array') $msg = implode("<br>",$msg);
    $msg=html_safe_text($msg);
    echo "<div class=\"error\">$msg</div>";
}

function glob_count($folder,$glob="/*.abf"){
    // return the number of files in a folder matching a glob function
    return sizeof(glob($folder.$glob));
}

function startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    return $length === 0 || (substr($haystack, -$length) === $needle);
}

function path_local($path){
    // given an X-drive path return a D-drive path
    $path=str_replace('/','\\', $path);
    $path=str_replace($GLOBALS['__PATH_DATA_NETWORK__'],$GLOBALS['__PATH_DATA_LOCAL__'], $path);
    return $path;
}

function path_network($path){
    // given a D-drive path return an X-drive path
    $path=str_replace($GLOBALS['__PATH_DATA_WEB__'],$GLOBALS['__PATH_DATA_NETWORK__'], $path);
    $path=str_replace('/','\\', $path);
    $path=str_replace($GLOBALS['__PATH_DATA_LOCAL__'],$GLOBALS['__PATH_DATA_NETWORK__'], $path);
    return $path;
}

function path_web($path){
    // given any local or network path return the http:// path...
    $path=path_local($path);
    $path=str_replace($GLOBALS['__PATH_DATA_LOCAL__'],$GLOBALS['__PATH_DATA_WEB__'], $path);
    return $path;
}

function scandir2($path){
    // return a sorted directory scan without the dots
    $files = array_diff(scandir($path), array('..', '.'));
    sort($files);
    return $files;
}

function random_string($length, $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',$str='') {
    // return a random string of a certain length
    $count = strlen($charset);
    while ($length--) {
        $str .= $charset[mt_rand(0, $count-1)];
    }
    return $str;
}

function html_button_copy($text, $invisible=True, $label="copy"){
    // display a string and add a button to copy to clipboard
    $text=str_replace("D:\\X_Drive\\","X:\\",$text);
    $uniqueID=random_string(10);
    $style="";
    if ($invisible) {$style="display: none;";}
    $html = "<span style=\"$style\" id=\"$uniqueID\">$text</span> ";
    $html .= "<button style='font-size: 65%;' onclick=\"copyToClipboard('$uniqueID')\">$label</button>";
    return $html;
}

function display_thumbnail($urls,$height=200){
    // given a URL to a picture (or an array of URLs) display a thumbnail
    if (gettype($urls)=="string") $urls = [$urls];
    foreach ($urls as $url){
        echo "<a href='$url'><img class='picframe_shadow' src='$url' height='$height'></a> ";
    }
}

function filesize_formatted($path)
{
    $size = filesize($path);
    $units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $power = $size > 0 ? floor(log($size, 1024)) : 0;
    return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
}

function redirect($url){
    echo "<script language='javascript'>window.location.href = '$url'</script>";
}

?>