<?php
include '../cfg.php';
$csfiles = array(
	'jqModal.css',
	'fmx.css',
	'fmxui.css',
	'nav.css',
	'context.css'
	);

$lastmod = 0;
$totsize = 0;
foreach ($csfiles as $csf) {
	$lastmod = max($lastmod, filemtime($csf));
	$totsize += filesize($csf) + strlen($csf) + 6;
}
$hash = $lastmod . '-' . md5(implode(':',$csfiles));
header("Etag: " . $hash);

if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) == $hash) {
	// Return visit and no modifications, so do not send anything 
	header ("HTTP/1.0 304 Not Modified"); 
	header ('Content-Length: 0'); 
} else {
	//package the css files for one access
	header("Content-type: text/css");
	header("Content-Length: " . $totsize);
	foreach ($csfiles as $csf) {
		echo"/*{$csf}*/\n";
		readfile($csf);
		echo"\n";
	}
}
