<?php include('top.php'); ?>


<div style="background-color: black; color: white;">
<code>
<span style="font-size: 200%; font-weight: bold;">ADMIN ACTIONS</span><br>
<?php echo $project;?>
</code>
</div>

<ul>
<li><a href="?page=action_tif&project=<?php echo $project;?>">TIF -> JPG conversion</a><br>
<li><a href="?page=action_analyze&project=<?php echo $project;?>">analyze unanalyzed ABFs</a><br>
<li><a href="?page=action_caps&project=<?php echo $project;?>">fix file extension capitalization</a><br>
<li><a href="?page=action_delete&project=<?php echo $project;?>">delete ALL analysis data</a><br>
</ul>

<?php include('bot.php'); ?>