
<?php

/*
function tiff_convert_folder($folder, $putInSubFolder="/swhlab/"){
    // given a folder with a bunch of TIF files, use python to make them JPGs.

    if (!file_exists($folder)) return;

    $folder_output=$folder.$putInSubFolder;
    if (!file_exists($folder_output)) mkdir($folder_output);

    $files = scandir($folder);
    $files2 = scandir($folder_output);
    $tifs_to_convert=[];
    foreach ($files as $fname){
        $extension=strtolower(pathinfo($fname, PATHINFO_EXTENSION));
        if ($extension == "tif" || $extension == "tiff") {
            if (!in_array($fname.".jpg",$files2)){
                $tifs_to_convert[]=$fname;
            }
        }
        
    }

    foreach ($tifs_to_convert as $tifFile){
        $fileIn=$folder."/".$tifFile;
        $fileOut=$folder_output.$tifFile.".png";
        if (file_exists($fileOut)) continue;
        $cmd="convert \"$fileIn\" -contrast-stretch 0.15x0.05% \"$fileOut\"";
        echo "<div style='font-family: monospace;'>$cmd</div>";
        exec($cmd);       
        flush();ob_flush();
    }
    
}
*/


function display_files($folder, $figHeight=150){
    if (!is_dir($folder)) {
		mkdir($folder);
        echo "<i style='color: #AAA;'>automatically created this folder</i>";
        return;
    }
    $files = scandir($folder);
    foreach ($files as $fname){
        $fpath = $folder."/".$fname;
        $url = str_replace("D:/X_Drive","/X",$fpath);
        if ($fname=="Thumbs.db") continue;
        if (is_dir($fpath)) continue;
        if (strtolower(pathinfo($fpath)['extension'])=="tif") continue;
        if (strtolower(pathinfo($fpath)['extension'])=="jpg" || 
            strtolower(pathinfo($fpath)['extension'])=="png")
            {
                echo "<a href='$url'><img src='$url' height='130' class='micrograph'></a> ";
            } else {
                echo "<br><a href='$url'>$fname</a>";
            }      
    }
}



function update_sx_log(){

    // connect to database
    class MyDB2 extends SQLite3 {
        function __construct() {
            $this->open('D:\X_Drive\Data\surgeries\surgeries.db'); // TODO: path specific
        }
    }
    $db = new MyDB2();
    if(!$db) {
        echo $db->lastErrorMsg();
        return;
    }

    // prepare the query
    $values = ["animal","originalCage","gender","genotype","target","substance","volume","coords","dateSx","dateSac","notes","hidden"];
    $sqlKeys="";
    $sqlValues="";
    foreach ($values as $value){
        $sqlKeys.="`$value`,";
        if ($_POST[$value]==""){
            $sqlValues.="NULL,";
        } else {
            $sqlValues.="'".$_POST[$value]."',";
        }
    }
    $sql = "INSERT INTO `animals`($sqlKeys) VALUES ($sqlValues)";
    $sql = str_replace(",)",")",$sql);

    // run the query
    $ret = $db->exec($sql);
    if(!$ret) {
       echo $db->lastErrorMsg();
    } else {
       echo "<div style='font-family: monospace; font-weight: bold; font-size: 200%;'>SAVING...</div>";
    }
    $db->close();

    
    echo "\n\n<!-- \n $sql \n -->\n\n";
    //echo "<div style='font-family: monospace;'>$sql</div>";

    // forward to this page so clicking refresh doesn't break things
    echo '<meta http-equiv="refresh" content="1; url=surgeries2.php" />';

}

function deltaDays($dateOld, $dateNew){
    if ($dateNew=="") $dateNew = "now";
    $d1 = strtotime($dateOld);
    $d2 = strtotime($dateNew);   
    $delta = ($d2-$d1)/(60*60*24);
    return (int)$delta;
}

class MyDB extends SQLite3 {
    function __construct() {
        $this->open('D:\X_Drive\Data\surgeries\surgeries.db'); // TODO: path specific
    }
}

function editButton($line){
    $html.="\n\n";
    $html.='<button onclick="setData({';
    foreach ($line as $key => $value) {
        $html.="$key: '$value', ";
    }
    $html.="test: 'test'})";
    $html.='">edit</button>';
    $html.="\n\n";
    return $html;
}

function display_sx_log($showAlive=false){

    $db = new MyDB();
    if(!$db) echo $db->lastErrorMsg();
    
    // match pattern
    $results = $db->query('SELECT * FROM animals ORDER BY dateSac DESC, id DESC');
    
    if (isset($_GET["csv"])){
        echo "<a href='?'>regular view</a><br><br>";
        echo "<i>copy/paste this text into notepad and save as a CSV file, then open in Excel</i>";
        echo "<pre style='padding: 20px; background-color: #EEE;'><code>";
        $first = true;
        while ($line = $results->fetchArray(SQLITE3_ASSOC)) {
            
            if ($first){
                foreach ($line as $key => $value) {
                    echo "\"$key\", ";
                }
                echo "<br>";
            }
            $first = false;

            foreach ($line as $key => $value) {
                echo "\"$value\", ";
            }
            echo "<br>";
        }

        echo "</code></pre>";
        $db->close();
        return;
    }


    // html generation
    if ($showAlive) $msg="animals still alive";
    else $msg="sac'd animals";
    echo "<table cellpadding='10' cellspacing='0' style='border: 2px solid #666;' width='100%'>";
    echo "<tr><td colspan='2' style='background-color: #666; color: white;'><span style='font-size: 200%; font-weight: bold;'>Surgery Log ($msg)</span><br>";
    echo "display <a href='?showAll'>full</a> or <a href='?'>standard</a> records or <a href='?csv'>export as CSV</a>";
    echo "</br></td></tr>";
    

    $displayedRows=0;
    $shownAnimals=[];
    while ($line = $results->fetchArray(SQLITE3_ASSOC)) {
        
        if ($line["hidden"]!="") {
            if (!isset($_GET["showAll"])) {
                $shownAnimals[]=$line["animal"];
                continue;
            }
        }
    
        if (!isset($_GET["showAll"])){
            if (in_array($line["animal"],$shownAnimals)){
                continue;
            } else {
                $shownAnimals[]=$line["animal"];
            }
        } 

        //$unsure = "<span style='background-color: red;'>???</span>";
        $unsure = "?????????????????????";
        $displayedRows+=1;

        if ($line["coords"] == "") {
            $line["coords"].=$unsure;
        } else {
            $coords = explode(",", $line["coords"]);
            if (sizeof($coords)==3){
                $line["coords"]="";
                $line["coords"].=trim("$coords[0],");
                $line["coords"].=trim("$coords[1],");
                $line["coords"].=trim("$coords[2]");
            } else {
                $line["coords"].=$unsure;
            }
        }

        if ($line["dateSac"]=="" and $showAlive==false) continue;
        if ($line["dateSac"]!="" and $showAlive==true) continue;
        
        if ($line["originalCage"]=="") $line["originalCage"]=$unsure;

        $daysPostSx=deltaDays($line["dateSx"], $line["dateSac"]);

        $bgcolor = ($displayedRows%2==1 ? "#E9E9E9" : "#E0E0E0");
		if (strpos(strtolower($line["notes"]),"king")!==False) $bgcolor="#a1c3e0";
        echo "<tr style='background-color: $bgcolor; '>";
        echo "<td valign='top' style='min-width: 450px;'>";

        $style='';

        if ($line["dateSac"]=="") $style.='background-color: lightgreen; ';
        if ($line["hidden"]!="") $style.='text-decoration: line-through; ';
        echo "<div style='font-size: 200%; font-weight: bold;'><span style='$style'>".$line["animal"]."</style></div>";

        if ($line["dateSac"]=="") {
            echo "<div><span style='$style'>animal is alive ($daysPostSx days post Sx)</span></div>";
        }

        echo "<div><b>".$line["genotype"]."</b> ".$line["gender"]." from cage ".$line["originalCage"]."</div>";
        echo "<div><b>".$line["target"]."</b> injected with ".$line["substance"]." (".$line["volume"].")</div>";
        echo "<div>Sx: ".$line["dateSx"].", AP=$coords[0], ML=$coords[1], DV=$coords[2]</div>";
        if ($line["dateSac"]!="") {
            echo "<div>Sac: ".$line["dateSac"]." ($daysPostSx days post Sx)</div>";
        }
        if (strlen($line["notes"])){
            echo "<div>Notes: <span style='font-style: italic; font-weight: bold; color: darkgreen;'>";
            echo $line["notes"];
            echo "</span></div>";
        }
        
        echo "<div>".editButton($line);
        echo "</td>";
        echo "<td valign='top' style='white-space: nowrap;'>";
        echo "<code>X:\\Data\\surgeries\\".$line["animal"]."\\</code><br>";
        display_files("D:/X_Drive/Data/surgeries/".$line["animal"]);
        echo "</td>";
        echo "</tr>";
    
    }
    echo "</table>";
    echo "<br><br><br>";

    $db->close();
    
}

?>

<html>
<head>

<!-- PREPROCESS: update log if POST animal is given -->
<?php if ($_POST["animal"]!="") update_sx_log(); ?>

<style>
body {
    font-family: sans-serif;
}
.micrograph{
    margin: 5px;
    border: 1px solid black;
    background-color: black;
    box-shadow: 2px 2px 7px  rgba(0, 0, 0, 0.5); 
}
a {
    text-decoration: none;
    color: blue;
}
a:hover {
    color: orange;
}
</style>


<script>

Number.prototype.pad = function(size) {
    var s = String(this);
    while (s.length < (size || 2)) {s = "0" + s;}
    return s;
}

function checkDateFormat(id){
    var passing = true;
    var txtNow = document.getElementById(id).value;
    
    if (txtNow.length!=8) passing = false;
    else if (txtNow[2]!="-") passing = false;
    else if (txtNow[5]!="-") passing = false;

    if (txtNow=="" || passing==true){
        document.getElementById(id+"Msg").innerHTML='';
        document.getElementById("submit").disabled = false;
    } else {
        document.getElementById(id+"Msg").innerHTML='YY-MM-DD';
        document.getElementById("submit").disabled = true;
    }
}

 function fixCoords(){
     var coordsText = "";
     coordsText += document.getElementById("coordsAP").value + ",";
     coordsText += document.getElementById("coordsML").value + ",";
     coordsText += document.getElementById("coordsDV").value;
    document.getElementById("coords").value = coordsText;
 }

function setData(dict){
    if (dict["animal"] == ""){
        document.getElementById("entryTitle").innerHTML="Add New Animal";
		document.getElementById("animal").readOnly = false;
		document.getElementById("animal").style.backgroundColor = "";
    } else {
        document.getElementById("entryTitle").innerHTML="Edit Animal Record";
		document.getElementById("animal").readOnly = true;
		document.getElementById("animal").style.backgroundColor = "#CCCCCC";
    }
    
    var dt = new Date();
    today = dt.getFullYear()+"-"+(dt.getMonth()+1).pad(2)+"-"+dt.getDate().pad(2);
    today = today.substring(2);
    if ("coords" in dict){
        coords = dict["coords"].split(",");
        dict["coordsAP"]=coords[0];
        dict["coordsML"]=coords[1];
        dict["coordsDV"]=coords[2];
    }
    for (var key in dict) {
        if (dict[key]=='today') dict[key]=today;
        if (dict[key]=='hidden') continue;
        try{
            document.getElementById(key).value=dict[key];
        }
        catch(err){}
    }

    if ("hidden" in dict){
        if (dict["hidden"]=="" || dict["hidden"]=="0" || dict["hidden"]=="display"){
            document.getElementById("hidden").value=0;
            document.getElementById("hidden").innderHTML="display";
        } else {
            document.getElementById("hidden").value=1;
            document.getElementById("hidden").innderHTML="hidden";
        }
    }

    document.getElementById("wholeForm").style.display='block';
    document.getElementById("animal").focus();
    checkDateFormat("dateSac");
    checkDateFormat("dateSx");
}
</script>

</head>
<body>


<!-- START: EDIT ANIMAL FORM -->
<?php if ($_POST["animal"]!="") echo " <!-- "; ?>

<form action = "surgeries2.php" method="post"  id="wholeForm" style="display: none;">
    <div  style='display:inline-block; background-color: #DDEEDD; margin: 20px 0px 20px 0px; padding: 10px 10px 10px 10px; border: 2px solid #BBDDBB; line-height: 150%;'>
        <div id="entryTitle" style='font-size: 200%; font-weight: bold;'>New Surgery Entry</div>
        <i>Buttons are just suggestions. Any text can be manually entered.</i>
        <table>
        <tr><td align="right">Animal ID:</td><td><input id="animal" name="animal" /></td><td></td></tr>
        <tr><td align="right">Original cage:</td><td><input id="originalCage" name="originalCage"></td><td></td></tr>
        <tr><td align="right">Species/Strain:</td><td><input id="genotype" name="genotype"></td><td>
                                                <button type="button" onclick="setData({genotype:'C57'})">C57</button>
                                                <button type="button" onclick="setData({genotype:'SD'})">SD</button>
                                                <button type="button" onclick="setData({genotype:'OT-Cre'})">OT-Cre</button>
                                                </td></tr>
        <tr><td align="right">Sex:</td><td><input id="gender" name="gender"></td><td>
                                                <button type="button" onclick="setData({gender:'M'})">male</button>
                                                <button type="button" onclick="setData({gender:'F'})">female</button
                                                </td></tr>
        <tr><td align="right">Target:</td><td><input id="target" name="target"></td><td>
                                                <button type="button" onclick="setData({target:'AMG'})">AMG</button>
                                                <button type="button" onclick="setData({target:'PFC'})">PFC</button>
                                                <button type="button" onclick="setData({target:'PVN'})">PVN</button>
                                                <button type="button" onclick="setData({target:'RVLM'})">RVLM</button>
                                                </td></tr>
        <tr><td align="right">Substance:</td><td><input id="substance" name="substance"></td><td>
                                                <button type="button" onclick="setData({substance:'GRB 40nm'})">GRB 40nm</button>
                                                <button type="button" onclick="setData({substance:'RB 40nm'})">RB 40nm</button>
                                                <button type="button" onclick="setData({substance:'AAV-ChR2'})">AAV-ChR2</button>
                                                </td></tr>
        <tr><td align="right">Volume (nL):</td><td><input id="volume" name="volume"></td><td>
                                                <button type="button" onclick="setData({volume:'1000'})">1,000</button>
                                                <button type="button" onclick="setData({volume:'600'})">600</button>
                                                </td></tr>
        <tr><td align="right">Coordinates:</td><td colspan="2" style="line-height: 150%">
                                                    AP:<input id="coordsAP" name="coordsAP" onchange="fixCoords();" style="width: 40px;">
                                                    ML:<input id="coordsML" name="coordsML" onchange="fixCoords();" style="width: 40px;">
                                                    DV:<input id="coordsDV" name="coordsDV" onchange="fixCoords();" style="width: 40px;">
                                                       <input id="coords" name="coords" value="???">
                                                </td><td></td></tr>
        <tr><td align="right">Surgery Date:</td><td><input id="dateSx" name="dateSx" onchange="checkDateFormat('dateSx');"></td><td>
                                                <button type="button" onclick="setData({dateSx:'today'})">today</button>
                                                <span id="dateSxMsg" style="background-color: yellow;"></span>
                                                </td></tr>
        <tr><td align="right">Sac Date:</td><td><input id="dateSac" name="dateSac" onchange="checkDateFormat('dateSac');"></td><td>
                                                <button type="button" onclick="setData({dateSac:'today'})">today</button>
                                                <span id="dateSacMsg" style="background-color: yellow;"></span>
                                                </td></tr>
        <tr><td align="right">Visibility:</td><td><select id="hidden" name="hidden"><option value="0">display</option><option value="1">hidden</option></select></td><td></td></tr>
        <tr><td align="right" valign="top">Notes:</td><td colspan='2'>
                                                <textarea id="notes" name="notes" style='width:100%; height: 50px;'></textarea>
                                                </td></tr>
        <tr><td colspan='3' align="right">
            <button type="button"  onclick="setData({animal:'', originalCage:'', genotype:'', gender:'', target:'', 
                substance:'', volume:'', coordsAP:'', coordsML:'', coordsDV:'', dateSx:'', dateSac:'', notes:'', hidden:'0'})">RESET</button>
            <input type="submit" id="submit" value="Submit">
            </td></tr>
        </table>
    </div>
</form>
<br><button style='font-size: 150%; font-weight: bold;' onclick="setData({animal:'', originalCage:'', genotype:'', gender:'', target:'', 
                substance:'', volume:'', coordsAP:'', coordsML:'', coordsDV:'', dateSx:'', dateSac:'', notes:'', hidden:'0'})">Add New Animal</button><br><br>

<?php if ($_POST["animal"]!="") echo " --> "; ?>
<!-- END: EDIT ANIMAL FORM -->




<!-- DISPLAY SURGERY LOG -->
<?php 
if ($_POST["animal"]=="") {
    display_sx_log(true);
    display_sx_log(false);
	echo "<div>Scott recommends <a href='http://sqlitebrowser.org'>DB Browser for SQLite</a> to edit the database manually.</div>";
}
?>



</body>
</html>