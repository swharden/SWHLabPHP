<?php include('top.php'); ?>

<div style="background-color: red; color: white;">
<code>
<span style="font-size: 200%; font-weight: bold;">DELETING ALL ANALYSIS DATA</span><br>
<?php echo $project;?>
</code>
</div>

<code style="background-color: #FFEEEE;">
<?php analyze_delete_cell($project,$cellID); ?>
<br>
<b>DELETION OF DATA COMPLETE.</b>
</code>

<?php include('bot.php'); ?>