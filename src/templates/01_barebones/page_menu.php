<?php include('top.php');

function project_displayItems($items){
    // after getting a list of items from project_getItems(), this function
    // turns the list into beautifully formatted HTML.
    global $project;
    foreach ($items as $line){
        list($cellID,$color,$desc)=$line;
        $color=colorcode_lookup($color);
        if ($cellID=='---'){
            // this is a group separator
            echo "<div class='menu_category'>$desc</div>";
        } else {
            // this is a single cell
            echo "<div class='menu_cell_ID' style='background-color: $color'>";
            echo "<a href='?page=cellID&project=$project&cellID=$cellID' target='content'>$cellID</a>";
            echo "<span class='menu_cell_description'>$desc</span></div>";
        }
    }
}

$items=project_getItems($project);
project_displayItems($items);

include('bot.php');?>