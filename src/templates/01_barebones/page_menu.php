<?php include('top.php');

$items=project_getItems($project);
project_displayItems($items);

include('bot.php');?>