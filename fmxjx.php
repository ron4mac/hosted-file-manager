<?php
require_once('functions.php');

$fref = isset($_POST['fref']) ? $baseDir.doUnescape($_POST['fref']) : '';

switch ($_POST['act']) {
	case 'copy':
		$tonam = $baseDir.escapeshellcmd($_POST['tonm']);
		system('cp -a "'.$fref.'" "'.$tonam.'"',$rslt);
		if ($rslt) echo $rslt;
		break;
	case 'cppa':
		$todir = $baseDir.escapeshellcmd($_POST['todr']);
		$path = escapeshellcmd($_POST['dir']);
		$files = $_POST['files'];
		$rslt = '';
		foreach ($files as $fle) {
			system('cp -a "' . "$baseDir$path/".rtrim(doUnescape($fle),' /') . '" "'.$todir.'"',$irslt);
			$rslt += $irslt;
			}
		if ($rslt) echo $rslt;
		break;
	case 'delf':
		$path = escapeshellcmd($_POST['dir']);
		$files = $_POST['files'];
		foreach ($files as $fle) {
			recursiveDelete("$baseDir$path/".rtrim(doUnescape($fle),' /'));
			}
		break;
	case 'dnld':
		$path = escapeshellcmd($_POST['dir']);
		$files = $_POST['files'];
		if (count($files)==1) {
			$sfil = doUnescape($files[0]);
			if ($sfil[strlen($sfil)-1]!=='/') {
				echo json_encode(array("fpth"=>$path."/$sfil"));
				break;
				}
			}
		//zip required for multiples
		$zh = new ZipArchive();
		$zfn = 'tmp/files.zip';
		if ($zh->open($baseDir.$zfn, ZIPARCHIVE::CREATE)!==TRUE) {
			exit("cannot open <$zfn>\n");
			}
		foreach ($files as $fle) {
			if ($fle[strlen($fle)-1]=='/') { addDirToAcrhive("$baseDir$path/",doUnescape($fle),$zh); }
			else { $zh->addFile("$baseDir$path/".doUnescape($fle),$fle); }
			}
		$zh->close();
		echo json_encode(array("fpth"=>$zfn,"rad"=>"Y"));
		break;
	case 'dupl':
		$path_parts = pathinfo($fref);
		$pfmx = $path_parts['dirname'].'/'.$path_parts['filename'];		//file path minus extension
		$pfxp = $path_parts['extension']?('.'.$path_parts['extension']):'';		//file path estension part
		$nn = 1;
		while (file_exists($pfmx.'_'.$nn.$pfxp)) {
			$nn++;
			}
		system('cp -a "'.$pfmx.$pfxp.'" "'.$pfmx.'_'.$nn.$pfxp.'"',$rslt);
		if ($rslt) echo $rslt;
		break;
	case 'finf':
		$fileoi = escapeshellarg(urldecode($fref));
		system('stat --printf="%f %F<br />%A %U/%G<br />access: %x<br />modify: %y<br />change: %z" '.$fileoi,$rslt);
		if ($rslt) echo $fileoi;
		break;
	case 'fren':
		rename($fref,$baseDir.doUnescape($_POST['nunm']));
		break;
	case 'fvue':
		$mtyp = FileMimeType($fref);
		header("Content-Type: $mtyp");
		$fcon = nl2br(htmlspecialchars(file_get_contents($fref)));
		echo $fcon;
		break;
	case 'move':
		$tonam = $baseDir.escapeshellcmd($_POST['tonm']);
		system('mv -f "'.$fref.'" "'.$tonam.'"',$rslt);
		if ($rslt) echo $rslt;
		break;
	case 'mvto':
		$todir = $baseDir.escapeshellcmd($_POST['todr']);
		$path = escapeshellcmd($_POST['dir']);
		$files = $_POST['files'];
		$rslt = '';
		foreach ($files as $fle) {
			$cmd = 'mv -b -t "'.$todir.'" "'."$baseDir$path/".rtrim(doUnescape($fle),' /').'"';
			system($cmd, $irslt);
			$rslt += $irslt;
			}
		if ($rslt) echo $cmd.$rslt;
		break;
	case 'nfld':
		mkdir($fref);
		break;
	case 'nfle':
		$fh = fopen($fref,'x');
		fclose($fh);
		break;
	case 'jxtr':
		require_once('joomext.php');
		$joomext = new Joomext($fref);
		$joomext->pull(escapeshellcmd($_POST['dir']));
		break;
	case 'fmxi':
		echo 'FMX Version: ' . $fmxVersion;
		echo "\nPHP Version: " . phpversion();
		echo "\nSQLite3 Version: " . SQLite3::version()['versionString'];
		break;
	case 'CLIC':
		echo file_get_contents('cliref.htm');
		break;
	default:
		echo $_POST['act'];
}

function displayDir ($dir) {
	$dfils = array_diff(scandir($dir),array('.','..'));
	echo '<table><tbody>';
	foreach ($dfils as $dfil) {
		$st = stat($dir.'/'.$dfil);
		echo '<tr><td>' . $dfil . '</td>' . sprintf('<td>%o</td>',$st[2]&07777) . '</tr>';
	}
	echo '</tbody></table>';
}

function recursiveDelete ($pstr) {
	if (is_file($pstr)) { @unlink($pstr); }
	elseif (is_dir($pstr)) {
		$dh = opendir($pstr);
		while ($node = readdir($dh)) {
			if ($node != '.' && $node != '..') {
				$path = $pstr.'/'.$node;
				recursiveDelete($path);
			}
		}
		closedir($dh);
		@rmdir($pstr);
	}
}

function smartCopy ($source, $dest)  {
	$result = false;
	
	if (is_file($source)) {
		if ($dest[strlen($dest)-1]=='/') {
			if (!file_exists($dest)) {
				mkdir($dest,0,true);
			}
			$__dest=$dest."/".basename($source);
		} else {
			$__dest = $dest;
		} 
		$result = copy($source, $__dest);
	} elseif (is_dir($source)) {
		if ($dest[strlen($dest)-1]=='/') {
			if ($source[strlen($source)-1]=='/') {
				//Copy only contents
			} else {
				//Change parent itself and its contents
				$dest = $dest.basename($source);
				mkdir($dest);
			} 
		} else {
			if ($source[strlen($source)-1]=='/') {
				//Copy parent directory with new name and all its content
				mkdir($dest);
			} else {
				//Copy parent directory with new name and all its content
				mkdir($dest);
			}
		}

		$dirHandle = opendir($source);
		while ($file = readdir($dirHandle))
		{
			if ($file!="." && $file!="..") {
				if (!is_dir($source."/".$file)) {
					$__dest = $dest."/".$file;
				} else {
					$__dest = $dest."/".$file;
				}
				$result = smartCopy($source."/".$file, $__dest);
			}
		}
		closedir($dirHandle);

	} else {
		$result = false;
	}
	return $result;
}

function addDirToAcrhive ($base,$dirn,$zh) {
	if (!is_dir($base.$dirn)) {
		throw new Exception('Directory ' . $dirName . ' does not exist');
	}

	$dirName = realpath($base.$dirn);
	if (substr($dirName, -1) != '/') {
		$dirName.= '/';
	}

	$dirStack = array($dirName);
	//Find the index where the last dir starts
	$cutFrom = strrpos(substr($dirName, 0, -1), '/')+1;

	while (!empty($dirStack)) { 
		$currentDir = array_pop($dirStack);
		$filesToAdd = array();

		$dir = dir($currentDir);
		while (false !== ($node = $dir->read())) {
			if (($node == '..') || ($node == '.')) { continue; }
			if (is_dir($currentDir . $node)) {
				array_push($dirStack, $currentDir . $node . '/');
			}
			if (is_file($currentDir . $node)) {
				$filesToAdd[] = $node;
			}
		}

		$localDir = substr($currentDir, $cutFrom);
		$zh->addEmptyDir($localDir);

		foreach ($filesToAdd as $file) {
			$zh->addFile($currentDir . $file, $localDir . $file);
		}
	}

}

?>
