
<?php if(!($page=="menu")) : ?>
<div class="adminbar">
    ADMIN MENU: | 
    <span TITLE="page generated in <?php echo timer(1); ?> at <?php html_timestamp();?>">hover</span> | 
    <a href="?page=action&project=<?php echo $project;?>">actions</a> |    
    <a href="?page=action_tif&project=<?php echo $project;?>">TIF->JPG conv</a> | 
    <a href="?page=action_analyze_all&project=<?php echo $project;?>" target="_blank">analyze ABFs page</a> | 
    <a href="?page=action_analyze&project=<?php echo $project;?>" target="_blank">analyze ABFs graph</a> | 
    <a href="?page=action_caps&project=<?php echo $project;?>">ext case fix</a> | 
    <a href="?page=action_delete&project=<?php echo $project;?>">delete ALL</a> | 
    <a href="../../../../../../../../">HOME</a> | 
</div>
<?php endif; ?>

<!--<?php html_msg(); ?>-->
</body>
</html>