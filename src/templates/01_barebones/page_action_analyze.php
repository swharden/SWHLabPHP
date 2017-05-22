<?php include('top.php'); ?>

<div style="background-color: red; color: white;">
<code>
<span style="font-size: 200%; font-weight: bold;">ABF FILE ANALYSIS</span><br>
<?php echo $project;?>
</code>
</div>

<code style="font-size: 75%">
<?php 
$remainingABFs=analyze_abf_next($project); 
if ($num=='') $num=$remainingABFs;
if ($remainingABFs){
    echo "<meta http-equiv='refresh' content='0;url=?page=action_analyze&project=$project&num=$num' />";
} else {
    echo "ANALYSIS COMPLETE";
}
$fracDone=100*($num-$remainingABFs)/$num;
$thisABF=$num-$remainingABFs;
?>
</code>

<br><br><br>
<div align="center">
<?php echo "Analyzing ABF $thisABF/$num ..."; ?>
<table width="80%" cellpadding=0 cellspacing=0 style="border: 1px solid black; border-collapse: collapse;">
<tr>
<td style="background-color: #6666FF;" width="<?php echo $fracDone; ?>%" height="20px">&nbsp</td>
<td style="background-color: #DDD;" width="100%">&nbsp</td>
</tr>
</table>
</div>
<br><br><br>



<?php include('bot.php'); ?>