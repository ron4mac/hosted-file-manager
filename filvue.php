<?php
require_once('functions.php');
$fref = urldecode($_GET['fref']);
$ffref = $baseDir.$fref;
$fcon = '';
$mtyp = $mtyp_arg = isset($_GET['mtyp']) ? escapeshellcmd($_GET['mtyp']) : '';
if (!$mtyp) $mtyp = FileMimeType($ffref);
list($x,$y) = preg_split('/\//',$mtyp.'/');
switch ($y) {
	case 'zip':
		$mtyp = 'text/plain';
		$fcon = `unzip -l $ffref`;
		break;
	case 'x-gzip':
		$mtyp = 'text/plain';
		$fcon = `tar -tzvf $ffref`;
		break;
	case 'x-tar':
		$mtyp = 'text/plain';
		$fcon = `tar -tvf $ffref`;
		break;
	case 'x-bzip2':
		$mtyp = 'text/plain';
		$fcon = `tar -tjvf $ffref`;
		break;
	default:
		if ($x=='image') {
			$mtyp = 'text/html';
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
			$fcon .= '<body><img class="center fit" src="filproxy.php?f='.urlencode($fref).'" /></body></html>';
			break;
		}
		if (!file_exists($ffref)) break;
		if ($mtyp==='0x') {
			$mtyp = 'text/plain';
			$gc = 4;
			$lin = '';
			$fhan = fopen($ffref,'r');
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
		elseif (pathinfo($ffref, PATHINFO_EXTENSION) == 'md' && $mtyp_arg !== 'text/plain') {
			include 'md/MarkdownExtra.inc.php';
			$mtyp = 'text/html';
			$fcon = \Michelf\MarkdownExtra::defaultTransform(file_get_contents($ffref));
		}
		else $fcon = file_get_contents($ffref);
		break;
}
header("Content-Type: $mtyp");
echo $fcon ? $fcon : ('Failed to open: '.$fref);
?>