<?php include('top.php'); ?>

<div style="background-color: red; color: white;">
<code>
<span style="font-size: 200%; font-weight: bold;">FILENAME CAPITALIZATION</span><br>
<?php echo $project;?>
</code>
</div>

<code style="background-color: #FFEEEE;">
<?php rename_lowercase_extensions($project); ?>
<br>
<b>FILENAME CAPITALIZATION CONVERSION COMPLETE.</b>
</code>

<?php include('bot.php'); ?>