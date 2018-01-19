<html>
<style>
body {font-family: monospace;}
code{background-color: #EEE; padding-left: 3px; padding-right: 3px;}
</style>
<body>
<?php

function search_filesystem($string, $maximum_records=500, $path="D:/X_Drive/"){
    $string = str_replace("*","%",$string);
    
    $sql="SELECT System.ItemPathDisplay FROM SYSTEMINDEX WHERE System.FileName LIKE '$string'";
    echo "<!-- \n\n\n SQL QUERY: $sql \n\n\n -->";
    
    // requires COM extension to be installed (edit php.ini)
    $files=[];
    $conn = new COM("ADODB.Connection") or die("Cannot start ADO");
    $recordset = new COM("ADODB.Recordset");
    $recordset -> MaxRecords = $maximum_records;
    $conn -> Open("Provider=Search.CollatorDSO;Extended Properties='Application=Windows';");
    $recordset -> Open($sql, $conn);
    //$recordset -> MoveFirst();   
    while (!$recordset -> EOF) {
        $found_file_path=$recordset -> Fields -> Item("System.ItemPathDisplay") -> Value;
        //echo "<li>$found_file_path";
        $files[]=$found_file_path;
        $recordset -> MoveNext();
    }
    
    
    return $files;
}

if (isset($_REQUEST["match"])){
    $match=$_REQUEST["match"];
    echo "searching for <code>$match</code> ... ";

    $time_start = microtime(true);
    $files = search_filesystem($match);
    $time_elapsed = round((microtime(true) - $time_start)*1000,2);
    $count = count($files);
    $action = "found";
    if ($count==$maximum_records) $action = "maxed-out at";
    echo "<i><b>$action $count results</b> in $time_elapsed</i> ms!<hr>";

    foreach ($files as $path){
        echo "$path<br>";
    }

} else {
    echo "ERROR: try adding ?match=test.jpg to the URL";
}

?>
</body>
</html>