<?php
    

function script_command_readfile($fname="commands"){
    $fname = realpath(dirname(dirname(__FILE__))."/scripts/$fname.txt");
    $f = fopen($fname, "r");
    echo "<code>";
    if (filesize($fname)==0){
        if (endsWith($fname,"commands.txt")){
            redirect("?view=finished");
        }
    } else {
        $raw=fread($f,filesize($fname));
        fclose($f);
        $raw=explode("\n",$raw);
        foreach ($raw as $line) {
            if (strpos($line, 'ERROR') !== false){
                echo "<span style='background-color: #FFAAAA;'>$line</span><br>";
            } else {
                echo "$line<br>";                
            }
        }
    }
    echo "</code>";
}

if (isset($_GET['clearLog'])) {
    $fname = realpath(dirname(dirname(__FILE__))."/scripts/log.txt");
    file_put_contents("$fname", "");
}

if (isset($_GET['viewLog'])) {
    // show just the log (we are done with commands)
    echo "<h3>Command Log:</h3>";
    script_command_readfile("log");
} else {
    // we are currently executing commands
    echo "<h1>Analyzing Data...</h1>";
    echo "<div style='color: #CCC;'>";
    echo "<code><b>commands queued to run on the server:</b></code><br>";
    script_command_readfile("commands");
    echo "<h3>Command Log:</h3>";
    script_command_readfile("log");
    echo "</div>";
}


?>