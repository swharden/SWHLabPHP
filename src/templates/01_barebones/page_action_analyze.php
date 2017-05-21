<?php include('top.php'); ?>

<div style="background-color: red; color: white;">
<code>
<span style="font-size: 200%; font-weight: bold;">ABF FILE ANALYSIS</span><br>
<?php echo $project;?>
</code>
</div>

<code style="background-color: #FFEEEE;">
<?php analyze_abf_all($project); ?>
<br>
<b>ABF FILE ANALYSIS COMPLETE.</b>
</code>

<?php include('bot.php'); ?>