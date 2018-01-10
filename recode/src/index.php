<?php

// time how long this takes
$page_time = array_sum(explode(' ', microtime()));

// handle our function imports
include('config/config.php');
foreach (glob("functions/*.php") as $filename) include $filename;

// determine what type of page to render
$view = isset($_GET['view']) ? $_GET['view'] : 'demo';
$view = str_replace("=","",$view);
$debug = isset($_GET['debug']) ? TRUE : FALSE;
$viewFile = 'views/'.$view.'.php';

// render the page based on our view
include('template/top.php');
if (file_exists($viewFile)) include($viewFile);
else display_error("VIEW DOES NOT EXIST: $view");
$page_time = round((array_sum(explode(' ', microtime())) - $page_time)*1000, 2);
include('template/bot.php');

?>