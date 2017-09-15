<html>
<head>

<style type="text/css">
<?php include("style.css"); ?>
</style>

<script>
function copyToClipboard(elementId) {

  // Create a "hidden" input
  var aux = document.createElement("input");

  // Assign it the value of the specified element
  aux.setAttribute("value", document.getElementById(elementId).innerHTML);

  // Append it to the body
  document.body.appendChild(aux);

  // Highlight its content
  aux.select();

  // Copy the highlighted text
  document.execCommand("copy");

  // Remove it from the body
  document.body.removeChild(aux);

}
</script>

</head>
<body>
<!--<h1 style="color:#CCCCCC;">SWHLab</h1><hr>-->
<?php include('colorcodes.php'); ?>
<?php timer(0); // reset page generation timer ?>
