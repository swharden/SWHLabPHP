<?php include('top.php');

function colorcode_lookup($s){
    // for each of the color codes (in colorcodes.php) do a find/replace
    // and return the actual color code to be used. If no match is found,
    // return the original colorcode.
    global $COLORCODES;
    foreach ($COLORCODES as $colorcode){
        if ($s==$colorcode[0]){
            return $colorcode[1];
        }
    }
    return $s;
}

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

/*
<code>

<h3>Cell ID</h3>
<?php
	//This displays only cell IDs 
	$IDs=dirscan_cellIDs($project);
	foreach ($IDs as $cellID){
		echo "<a href='?page=cellID&project=$project&cellID=$cellID' target='content'>$cellID</a><br>";
	}
?>

<h3>All ABFs</h3>
<?php
	//This displays every ABF grouped by cell ID
	$groups=dirscan_cellIDs($project,True);
	foreach ($groups as $group){
		$cellID=bn($group[0]);
		echo "<br><b><a href='?page=cellID&project=$project&cellID=$cellID' target='content'>$cellID</a></b><br>";
		foreach ($group as $abf){
            $abfID=bn($abf);
            echo "<a href='?page=abfID&project=$project&abfID=$abfID' target='content'>$abfID</a><br>";
		}
	}
	
?>
</code>
*/

include('bot.php');?>