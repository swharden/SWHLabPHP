<html>
<head>
<title>SWHLab</title>
<link rel="stylesheet" href="<?php echo $__PATH_SRC_WEB__.'/template/style.css'?>">
<?php
$refresh = (isset($_GET['refresh']) ? $_GET['refresh'] : 999999);
echo "<meta http-equiv='refresh' content='$refresh'>";
?>

<script>
function copyToClipboard(elementId) {
  var input = document.createElement("input");
  document.body.appendChild(input);
  input.value=document.getElementById(elementId).innerText;
  input.select();
  document.execCommand("copy");
  document.body.removeChild(input);
}
</script>

<script>
	function setClicked(id) {
		elems = document.getElementsByClassName('abflink');
		for (i = 0; i < elems.length; i++) {
			elems[i].style.fontWeight="normal";
			if (elems[i].id==id) {
				elems[i].style.fontWeight="bold";
			}
		}
		elems = document.getElementsByClassName('abftick');
		for (i = 0; i < elems.length; i++) {
			elems[i].style.visibility="hidden";
			if (elems[i].id==id) {
				elems[i].style.visibility="visible";
			}
		}
	}
</script>

</head>

<?php
$showBody=TRUE;
$showTopAndBot=TRUE;

if (isset($_GET['frames'])) $showBody = FALSE;
if (isset($_GET['nobody'])) $showBody = FALSE;

if (isset($_GET['menu'])) $showTopAndBot = FALSE;
//if (isset($_GET['data'])) $showTopAndBot = FALSE;
//if (isset($_GET['splash'])) $showTopAndBot = FALSE;
?>

<?php if ($showBody): ?>

    <body>

    <?php if ($showTopAndBot): ?>
    <div class="block_topMenu">
        <b style="color: black;">Frazier Laboratory</b> |
        <a target="_top" href="/">home</a> |
        <a target="_top" href="http://192.168.1.9/SWHLabPHP/recode/src/?view=abf">data index</a> |
        <?php echo "view: $view"; ?>
    </div>
    <?php endif; ?>

<?php endif; ?>