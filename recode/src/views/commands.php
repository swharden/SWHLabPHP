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

//script_command_add("cool test command");
echo "<h3>Commands</h3>";
script_command_readfile("commands");
echo "<h3>Command Log</h3>";
script_command_readfile("log");

?>