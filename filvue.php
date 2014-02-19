<?php
require_once('functions.php');
$fref = $baseDir . $_GET['fref'];
$fcon = '';
$mtyp = $mtyp_arg = isset($_GET['mtyp']) ? escapeshellcmd($_GET['mtyp']) : '';
if (!$mtyp) $mtyp = FileMimeType($fref);
list($x,$y) = preg_split('/\//',$mtyp);
switch ($y) {
	case 'zip':
		$mtyp = 'text/plain';
		$fcon = `unzip -l $fref`;
		break;
	case 'x-gzip':
		$mtyp = 'text/plain';
		$fcon = `tar -tzvf $fref`;
		break;
	case 'x-tar':
		$mtyp = 'text/plain';
		$fcon = `tar -tvf $fref`;
		break;
	case 'x-bzip2':
		$mtyp = 'text/plain';
		$fcon = `tar -tjvf $fref`;
		break;
	default:
		if ($x=='image') {
			$mtyp = 'text/html';
			$hfref = str_replace('public_html','',$_GET['fref']);
			$fcon = '<!DOCTYPE html><html><head>
<style>
* {
	padding: 0;
	margin: 0;
}
.fit {
	max-width: 100%;
	max-height: 100%;
}
.center {
	display: block;
	margin: auto;
}
</style>
</head>';
			$fcon .= '<body><img class="center fit" src="'.$hfref.'" /></body></html>';
			break;
		}
		if (!file_exists($fref)) break;
		if ($mtyp==='0x') {
			$mtyp = 'text/plain';
			$gc = 4;
			$lin = '';
			$fhan = fopen($fref,'r');
			while (!feof($fhan)) {
				$cnk = fread($fhan,8);
				$lin .= preg_replace('/[\x00-\x1F\x7F]/',' ',$cnk);
//				$lin .= htmlentities($cnk);
				$fcon .= bin2hex($cnk) . ' ';
				if (!--$gc) {
					$fcon .= '  ' . $lin . "\n";
					//$fcon .= "\n";
					$lin = '';
					$gc = 4;
					}
				}
			if ($lin) $fcon .= '  ' . $lin . "\n";
			fclose($fhan);
			}
		elseif (pathinfo($fref, PATHINFO_EXTENSION) == 'md' && $mtyp_arg !== 'text/plain') {
			include 'md/MarkdownExtra.inc.php';
			$mtyp = 'text/html';
			$fcon = \Michelf\MarkdownExtra::defaultTransform(file_get_contents($fref));
		}
		else $fcon = file_get_contents($fref);
		break;
}
header("Content-Type: $mtyp");
echo $fcon ? $fcon : ('Failed to open: '.$fref);
?>