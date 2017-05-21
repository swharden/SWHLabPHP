<?php include('top.php'); ?>

<div style="background-color: red; color: white;">
<code>
<span style="font-size: 200%; font-weight: bold;">TIF -> JPG CONVERSION</span><br>
<?php echo $project;?>
</code>
</div>

<code style="background-color: #FFEEEE;">
<?php analyze_tifConvert($project); ?>
<br>
<b>TIF-TO-JPG CONVERSION COMPLETE.</b>
</code>

<?php include('bot.php'); ?>