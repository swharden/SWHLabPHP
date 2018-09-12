
<?php

$d = new DateTime('', new DateTimeZone('US/Eastern')); 
$pageGenTimeStamp = $d->format('Y-m-d H:i:s');
$serverName=$_SERVER['SERVER_ADDR'];
$url='http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/'.$_SERVER['QUERY_STRING'];
$urlLength=strlen($url);
$tailMessage= "<a href='https://github.com/swharden/SWHLabPHP'>SWHLabPHP</a> by Scott Harden | ";
$tailMessage.= "<a href='?view=commands&viewLog'>view log</a> | ";
$tailMessage.= "<a href='?view=commands&clearLog'>clear log</a> |";
$tailMessage.= "<a href='?view=commands&refresh=3'>running commands</a> <br>";
$tailMessage.= "<span style='color: #AAA;'>";
$tailMessage.= "Server: $serverName<br>";
$tailMessage.= "Timestamp: $pageGenTimeStamp<br>"; 
$tailMessage.= "Page generation time: $page_time ms<br>";
$tailMessage.= "URL length: $urlLength chars<br>";
$tailMessage.= str_replace("&","<br>&nbsp;",$url)."<br>";
$tailMessage.= "</span>";

            
?>

<?php if ($showBody): ?>
    <?php if ($showTopAndBot): ?>
        <br><br><br><br><br>
        <div class="block_topMenu" style="color: black;">
            <?php echo $tailMessage;?>
        </div>
    <?php endif; ?>

    <?php if (!$showTopAndBot): ?>
        <!--
        <?php echo $tailMessage;?>
        -->
    <?php endif; ?>
    </body>
<?php endif; ?>

</html>