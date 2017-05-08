<?php include($_SERVER['DOCUMENT_ROOT'] . "/SWHLabPHP/src/swhlab.php"); ?>
<?php html_top(); ?>

<?php

$askProject = isset($_GET['project']) ? $_GET['project'] : '';
$askID = isset($_GET['id']) ? $_GET['id'] : '';

echo "PROJET: $askProject<br>";
echo "askID: $askID<br>";

?>


<?php html_bot(); ?>