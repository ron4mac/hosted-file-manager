<?php
/**
* @package		hosted-file-manager
* @copyright	Copyright (C) 2022-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		4.0.0
*/
define('EXEC_HFM', true);

include '../../src/Core/Cfg.php';
$cfg = new \Rjc\Hfm\Core\Cfg();

$jsfiles = [
	'fmx'.$cfg->val('jsver').'.js',
	'fmxui'.$cfg->val('jsver').'.js'
	];

$lastmod = 0;
$totsize = 0;
foreach ($jsfiles as $jsf) {
	$lastmod = max($lastmod, filemtime($jsf));
	$totsize += filesize($jsf) + strlen($jsf) + 6;
}
$hash = $lastmod . '-' . md5(implode(':',$jsfiles));
header('Etag: ' . $hash);
if (!$cfg->val('dev_mode')) {
	header('Last-Modified: '.gmdate('r', $lastmod));
	header('Expires: '.gmdate('r', time()+86400));
	header('Cache-Control: public');
}

if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) === $hash) {
	// Return visit and no modifications, so do not send anything 
	header ('HTTP/1.0 304 Not Modified'); 
	header ('Content-Length: 0'); 
} else {
	//package the script files for one access
	header('Content-type: text/javascript');
	header('Content-Length: ' . $totsize);
	foreach ($jsfiles as $jsf) {
		echo"/*{$jsf}*/\n";
		readfile($jsf);
		echo"\n";
	}
}
