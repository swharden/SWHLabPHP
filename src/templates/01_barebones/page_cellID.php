<?php include('top.php');?>

<?php

$needAnalysisTIF=analyze_tifConvert($project,True);
$needAnalysisABF=analyze_abf_commands($project);

if (count($needAnalysisTIF)){
    echo "<h1><a href='?page=action_tif&project=$project'>TIFs NEED CONVERTING</a></h1>";
}

if (count($needAnalysisABF)){
    echo "<h1><a href='?page=action_analyze&project=$project'>ABFs NEED ANALYSIS</a></h1>";
}

?>

<?php
// show parent cell ID along with the appropraite color banner
$cellName=bn(dirscan_parent($project,$cellID));
$cellColor=project_getCellColor($project,$cellID);
$cellComment=project_getCellComment($project,$cellID);
$cellIDprev=dirscan_parent_previous($project,$cellID);
$cellIDnext=dirscan_parent_next($project,$cellID);

echo "<div style='background-color: $cellColor; padding: 5px;'>";
//echo "<span style='font-size: 300%; font-weight: bold;'>Cell ID $cellName</span><br>";
if ($cellIDprev) echo "&laquo; <a href='?page=cellID&project=$project&cellID=$cellIDprev'>$cellIDprev</a> | ";
echo "<span style='font-size: 200%; font-weight: bold;'>$cellName</span>";
if ($cellIDnext) echo " | <a href='?page=cellID&project=$project&cellID=$cellIDnext'>$cellIDnext</a> &raquo;";
echo "<br><i>$cellComment</i>";
echo "</div>";
?>


<div style="background-color: #EEE;">
    <form action="" method="get">
    <input type="hidden" name="page" value="<?php echo $page;?>" />
    <input type="hidden" name="project" value="<?php echo $project;?>" />
    <input type="hidden" name="cellID" value="<?php echo $cellID;?>" />
    <input type="hidden" name="action" value="cellSet" />
    <table border=0 style="border-collapse: collapse;">
        <tr style="font-weight: bold">
            <td>color:</td>
            <td>comment:</td>
            <td><a href="?page=menu&project=<?php echo($project);?>" target="menu">refresh menu</a></td>
        </tr>
            <td>
            <?php
                //$colors=array('','g1','g2','g3','r','b');
                foreach ($COLORCODES as $colorcode){
                    $colors[]=$colorcode[0];
                }
                foreach ($colors as $code){
                    $color=colorcode_lookup($code);
                    $checked = (colorcode_lookup($code)==$cellColor) ? 'checked' : '';
                    echo "<span style='margin: 2px; padding: 2px;  border: solid 1px black; background-color: $color;'>";
                    echo "<input type='radio' name='col' value='$code' $checked></span>";
                }
            ?>&nbsp;&nbsp;&nbsp;&nbsp;
            </td>
            <td><input type="text" size="50" name="str" value="<?php echo($cellComment);?>"></td>
            <td><input type="submit" value="Submit"></td>
        </tr>
    </table>
    </form>

    <div style="background-color: #F6F6F6; padding: 5px;">
    <code><?php dirscan_cell_ABFsAndProtocols($project, $cellID);?></code>
    </div>

</div>

<?php

	$picsData=dirscan_cellPics($project,$cellID,$tif=False);
	$picsTif=dirscan_cellPics($project,$cellID,$tif=True);
    
	if(sizeof($picsData)){	
	echo "<h3>Figures</h3>";
		html_pics($picsData, $prepend="$project/swhlab/");
		echo("<br>");
	}
    
	if(sizeof($picsTif)){
		echo "<h3>Micrographs</h3>";
		html_pics($picsTif, $prepend="$project/swhlab/");
		echo("<br>");
	}
	
	if (!sizeof($picsData) and !sizeof($picsData)){
		echo("<h1>WARNING: no data found for '$cellID.'</h1>");
	}
	
?>

<?php include('bot.php');?>