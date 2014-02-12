<?php
require_once('functions.php');
$fref = $baseDir . doUnescape($_GET['fle']);
if ($fd = fopen($fref, 'r')) {
	$fsize = filesize($fref);
	$path_parts = pathinfo($fref);
	$ext = strtolower($path_parts['extension']);
	switch ($ext) {
		case 'pdf':
			header('Content-type: application/pdf'); // add here more headers for diff. extensions
			header('Content-Disposition: attachment; filename="'.$path_parts['basename'].'"'); // use 'attachment' to force a download
			break;
		default;
			header('Content-type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.$path_parts['basename'].'"');
	}
	header("Content-length: $fsize");
	header('Cache-control: private'); //use this to open files directly
	while(!feof($fd)) {
		$buffer = fread($fd, 8192);
		echo $buffer;
	}
	fclose($fd);
	if (isset($_GET['rad']) && $_GET['rad']=='Y') unlink($fref);
} else print_r($_GET);
exit;
?>