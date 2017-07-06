<?php

// customizable settings
$template="01_barebones";
$defaultPath="\\spike\X_Drive\Data\SCOTT\2017-01-09 AT1 NTS";

// this is for rewriting \\network\paths\ to web-safe served with aliased virtual directories
// it's an alternative to using Apache mod_rewrite
// currently it only applies to served files (images) and doesn't rewrite URLs of pages

// first clean up caps
$fileReplacements[]=["\\\\Spike\\","\\\\spike\\"]; // caps matter in apache, not Windows.

// then remap network paths to apache virtual directories
$fileReplacements[]=["\\\\spike\\X_Drive\\Data\\","/dataX/"];
$fileReplacements[]=["\\\\SPIKE\\X_DRIVE\\Data\\","/dataX/"];
$fileReplacements[]=["D:\\Data\\","/data/"];
$fileReplacements[]=["\\\\192.168.1.100\\X_Mirror\\Data\\","/xmirror/"];

?>