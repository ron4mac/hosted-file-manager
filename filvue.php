<?php
require_once 'functions.php';

$fref = urldecode($_GET['fref']);
$ffref = $baseDir.$fref;
$effref = str_replace(' ','\ ',$ffref);
$fcon = '';
$mtyp = $mtyp_arg = isset($_GET['mtyp']) ? escapeshellcmd($_GET['mtyp']) : '';
if (!$mtyp) $mtyp = FileMimeType($ffref);
list($x,$y) = preg_split('/\//',$mtyp.'/');
switch ($y) {
	case 'zip':
		$mtyp = 'text/plain';
		$fcon = `unzip -l $effref`;
		break;
	case 'x-gzip':
		$mtyp = 'text/plain';
		$fcon = `tar -tzvf $effref`;
		break;
	case 'x-tar':
		$mtyp = 'text/plain';
		$fcon = `tar -tvf $effref`;
		break;
	case 'x-bzip2':
		$mtyp = 'text/plain';
		$fcon = `tar -tjvf $effref`;
		break;
	case 'rfc822':
		$mtyp = 'text/plain';
		if (function_exists('mailparse_msg_parse_file')) {
			$fcon = 'CAN DO MAIL PARSE';
			$mmsg = file_get_contents($ffref);
			$mres = mailparse_msg_create();
			mailparse_msg_parse($mres, $mmsg);
			$mstr = mailparse_msg_get_structure($mres);
			$fcon = '';
			foreach ($mstr as $st) {
				$section = mailparse_msg_get_part($mres, $st); 
				$info = mailparse_msg_get_part_data($section);
			//	$fcon .= print_r($info, true);
				if ($info['content-type'] == 'text/plain') {
					$fcon .= "@SECTION {$st}@\n";
					$spb = $info['starting-pos-body'];
					$epb = $info['ending-pos-body'];
					$fcon .= substr($mmsg, $spb, $epb-$spb)."\n";
				}
			}
			mailparse_msg_free($mres);
		} else {
			$fcon = 'MAIL PARSE NOT AVAILABLE';
			include 'mparse/emlvue.php';
			if (class_exists('MySimpleMailParse')) {
				$mtyp = 'text/html';
				$fcon = my_mail_parse($ffref);
			} else {
				$fcon .= '<br>EMAIL_PARSER NOT AVAILABLE';
			}
		}
		break;
	default:
		if ($x=='image') {
			$mtyp = 'text/html';
			$fcon = '<!DOCTYPE html><html><head>
<style>
* { padding: 0; margin: 0; }
html { background-color:darkgray }
.fit { max-width: 100%; max-height: 100%; }
.center { display: block; margin: auto; }
</style>
</head>';
			$fcon .= '<body><img class="center fit" src="filproxy.php?f='.urlencode($fref).'" /></body></html>';
			break;
		}
		if (!file_exists($ffref)) break;
		if ($mtyp==='0x') {
//			$mtyp = 'text/plain';
			$mtyp = 'text/html';
			$gc = 4;
			$lin = '';
			$fcon .= '<table style="font-family:monospace"><tr><td>';
			$fhan = fopen($ffref,'r');
			while (!feof($fhan)) {
				$cnk = fread($fhan,8);
				$l = htmlspecialchars($cnk, ENT_SUBSTITUTE, 'ISO-8859-1');
				$l = preg_replace('/ /',"\xa0",$l);
				$lin .= preg_replace('/[\x00-\x1F\x7F]/',"\xb7",$l);
				$fcon .= bin2hex($cnk) . ' ';
				if (!--$gc) {
//					$fcon .= '  ' . $lin . "\n";
					$fcon .= "\xa0\xa0\xa0".'</td><td>' . $lin . "</td></tr>\n<tr><td>";
					//$fcon .= "\n";
					$lin = '';
					$gc = 4;
					}
				}
//			if ($lin) $fcon .= '  ' . $lin . "\n";
			if ($lin) $fcon .= '</td><td>' . $lin . "</td></tr>\n";
			fclose($fhan);
			$fcon .= '</table>';
			}
		elseif (pathinfo($ffref, PATHINFO_EXTENSION) == 'md' && $mtyp_arg !== 'text/plain') {
			include 'md/MarkdownExtra.inc.php';
			$mtyp = 'text/html';
			$fcon = \Michelf\MarkdownExtra::defaultTransform(file_get_contents($ffref));
		} elseif ($mtyp == 'text/plain' && pathinfo($ffref, PATHINFO_EXTENSION) == 'json') {
			//$fcon = print_r(json_decode(file_get_contents($ffref), true), true);
			$fcon = json_encode(json_decode(file_get_contents($ffref), true), JSON_PRETTY_PRINT);
		}
		else $fcon = file_get_contents($ffref);
		break;
}

header("Content-Type: $mtyp");
echo $fcon ? $fcon : ('Failed to open: '.$fref);
