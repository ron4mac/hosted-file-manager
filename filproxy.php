<?php
// this file proxy is used to facilitate viewing files outside of the webroot
require_once 'functions.php';
$fp = $baseDir . urldecode($_GET['f']);
if (!file_exists($fp)) die('nosuchfile: '.$fp);
// get a mime type
$mtype = FileMimeType($fp);
header("Content-Type: $mtype");
readfile($fp);
