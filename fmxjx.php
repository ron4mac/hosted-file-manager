<?php
include 'fmx.ini';
require_once 'functions.php';

$fref = isset($_POST['fref']) ? $baseDir.$_POST['fref'] : '';

switch ($_POST['act']) {
	case 'cppa':
		$todir = escapeshellarg($baseDir.$_POST['todr']);
		$path = $baseDir.$_POST['dir'].'/';
		$files = $_POST['files'];
		$rslt = 0;
		foreach ($files as $fle) {
			$farg = escapeshellarg($path.rtrim($fle,' /'));
			system('cp -a '.$farg.' '.$todir, $irslt);
			$rslt += $irslt;
		}
		if ($rslt) echo $rslt;
		break;
	case 'crlg':
		$save_path = $_POST['path'];
		$url = $_POST['url'];
		$fullfile = $baseDir.$save_path.basename($url);
		$fexists = file_exists($fullfile);	//remember that the file already existed
		if ($fexists) {
			echo basename($fullfile).' already exists here';
			break;
		}
		$fp = fopen($fullfile, 'w');
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_FILETIME, true);
		if (isset($_POST['user']) && isset($_POST['pass'])) curl_setopt($ch, CURLOPT_USERPWD, $_POST['user'].':'.$_POST['pass']);
		$rslt = curl_exec($ch);
		$msg = curl_error($ch);
		$ftime = curl_getinfo($ch, CURLINFO_FILETIME);
		curl_close($ch);
		fclose($fp);
		if ($msg) {
			if (!$fexists) @unlink($fullfile);	//remove the file
			echo $msg;
		} else {
			if ($ftime > 0) touch($fullfile, $ftime);
		}
		break;
	case 'crlp':
		$save_path = $_POST['path'];
		$url = $_POST['url'];
		$fullfile = $baseDir.$save_path.basename($url);
		$fexists = file_exists($fullfile);	//remember that the file already existed
		if ($fexists) {
			echo basename($fullfile).' already exists here';
			break;
		}
/*
$url_path_str = 'http://my_url';
$file_path_str = '/my_file_path';

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, ''.$url_path_str.'');
curl_setopt($ch, CURLOPT_PUT, 1);

$fh_res = fopen($file_path_str, 'r');

curl_setopt($ch, CURLOPT_INFILE, $fh_res);
curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file_path_str));

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$curl_response_res = curl_exec ($ch);
fclose($fh_res);
*/
		$fp = fopen($fullfile, 'w');
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_FILETIME, true);
		if (isset($_POST['user']) && isset($_POST['pass'])) curl_setopt($ch, CURLOPT_USERPWD, $_POST['user'].':'.$_POST['pass']);
		$rslt = curl_exec($ch);
		$msg = curl_error($ch);
		$ftime = curl_getinfo($ch, CURLINFO_FILETIME);
		curl_close($ch);
		fclose($fp);
		if ($msg) {
			if (!$fexists) @unlink($fullfile);	//remove the file
			echo $msg;
		} else {
			if ($ftime > 0) touch($fullfile, $ftime);
		}
		break;
	case 'trsh':
		$path = $baseDir.$_POST['dir'];
		$files = $_POST['files'];
		$todir = $baseDir.'tmp/Trash';
		@mkdir($todir, 0777, true);
		$rslt = '';
		foreach ($files as $fle) {
			$fod = rtrim($fle,' /');
			$fpth = $path.'/'.$fod;
			if (is_dir($fpth) && file_exists($todir."/$fod")) {
				$nn = 1;
				while (file_exists($todir."/$fod".'_'.$nn)) {
					$nn++;
				}
				rename($todir."/$fod", $todir."/$fod".'_'.$nn);
			}
			$cmd = 'mv --backup=numbered -t '.escapeshellarg($todir).' '.escapeshellarg($fpth);
			system($cmd, $irslt);
			$rslt .= $irslt ?: '';
		}
		if ($rslt) echo $cmd.$rslt;
		break;
	case 'mpty':
		$trd = $baseDir.'tmp/Trash/';
		$trshs = @scandir($trd);
		if (!$trshs) break;
		foreach ($trshs as $trsh) {
			if ($trsh=='.' || $trsh=='..') continue;
			recursiveDelete($trd.$trsh);
		}
		break;
	case 'delf':
		$path = escapeshellcmd($_POST['dir']);
		$files = $_POST['files'];
		foreach ($files as $fle) {
			recursiveDelete("$baseDir$path/".rtrim($fle,' /'));
		}
		break;
	case 'dnld':
		$path = escapeshellcmd($_POST['dir']);
		$files = $_POST['files'];
		if (count($files)==1) {
			$sfil = $files[0];
			if ($sfil[strlen($sfil)-1]!=='/') {
				echo json_encode(array("fpth" => $path."/$sfil"));
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
			if ($fle[strlen($fle)-1]=='/') { addDirToAcrhive("$baseDir$path/", $fle, $zh); }
			else { $zh->addFile("$baseDir$path/".$fle, $fle); }
		}
		$zh->close();
		echo json_encode(array("fpth" => $zfn,"rad" => "Y"));
		break;
	case 'dupl':
		$path_parts = pathinfo($fref);
		$pfmx = $path_parts['dirname'].'/'.$path_parts['filename'];		//file path minus extension
		$pfxp = isset($path_parts['extension'])?('.'.$path_parts['extension']):'';		//file path estension part
		$nn = 1;
		while (file_exists($pfmx.'_'.$nn.$pfxp)) {
			$nn++;
		}
		system('cp -a '.escapeshellarg($pfmx.$pfxp).' '.escapeshellarg($pfmx.'_'.$nn.$pfxp), $rslt);
		if ($rslt) echo $rslt;
		break;
	case 'finf':
		$fileoi = escapeshellarg(urldecode($fref));
		$stat = alt_stat(trim($fileoi,"'"));	//@stat(trim($fileoi,"'"));
		//system('stat --printf="%f %F<br />%A %U/%G<br />access: %x<br />modify: %y<br />change: %z" '.$fileoi,$rslt);
		//if ($rslt) echo $rslt . '<br />' . $fileoi;
		if (!$stat) die("Couldn't stat {$fileoi}");
		//echo serialize($stat);
		echo 'Permissions: ' .$stat['perms']['human'];
		echo '<br />Owner: ' .$stat['owner']['owner']['name'];
		echo '<br />Group: ' .$stat['owner']['group']['name'];
		echo '<br />Size: ' .$stat['size']['size'];
		echo '<br />Accessed: ' .$stat['time']['accessed'];
		echo '<br />Modified: ' .$stat['time']['modified'];
		echo '<br />Created: ' .$stat['time']['created'];
		break;
	case 'fren':
		rename($fref,$baseDir.$_POST['nunm']);
		break;
	case 'fvue':
		$mtyp = FileMimeType($fref);
		header("Content-Type: $mtyp");
		$fcon = nl2br(htmlspecialchars(file_get_contents($fref)));
		echo $fcon;
		break;
	case 'mmiz':
		$path = escapeshellcmd($_POST['dir']);
		$files = $_POST['files'];
		foreach ($files as $fle) {
			mmizFile("$baseDir$path/".rtrim($fle));
		}
		break;
	case 'mvto':
		$todir = escapeshellarg($baseDir.$_POST['todr']);
		$path = $baseDir.$_POST['dir'].'/';
		$files = $_POST['files'];
		$rslt = 0;
		foreach ($files as $fle) {
			$cmd = 'mv -b -t '.$todir.' '.escapeshellarg($path.rtrim($fle,' /'));
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
		require_once 'joomext.php';
		$joomext = new Joomext($fref);
		$joomext->pull(escapeshellcmd($_POST['dir']));
		break;
	case 'fmxi':
		require_once 'updater.php';
		$newver = checkForUpdate();
		$msg = 'FMX Version: ' . $fmxVersion;
		$msg .= '<br />PHP Version: ' . phpversion();
		$msg .= '<br />MySql(i) Client Version: ' . str_replace('$','',mysqli_get_client_info());
		if (class_exists('SQLite3')) {
			$sql3v = SQLite3::version();
			$msg .= '<br />SQLite3 Version: ' . $sql3v['versionString'];
		} else {
			$msg .= '<br />SQLite3 not available';
		}
		$msg .= '<br /><br />';
		if ($newver) {
			$vinf = explode('|', $newver);
			$msg .= '<span class="notify">There is an FMX update: '.$vinf[0].'</span>';
		} else {
			$msg .= 'There is no available FMX update.';
		}
		echo json_encode(array('updt' => $newver,'msg' => $msg));
		break;
	case 'slnk':
		$droot = dirname($_SERVER['DOCUMENT_ROOT']).'/';
		$bk = 0;
		$lfr = $droot.urldecode($_POST['fref']);
		$lat = $lad = $droot.$_POST['tref'];
		while (substr($lfr, 0, strlen($lat)) != $lat) {
			$lat = dirname($lat);
			$bk++;
		}
		$arg1 = escapeshellarg(str_repeat('../', $bk).rtrim(substr($lfr, strlen($lat)+1),'/'));
		$arg2 = escapeshellarg($lad . $_POST['alnk']);
		$cmd = 'ln -s '.$arg1.' '.$arg2;
		system($cmd, $rslt);
		if ($rslt) echo $cmd.$rslt;
		break;
	case 'updt':
		$newver = escapeshellcmd($_POST['nver']);
		require_once 'updater.php';
		if ($newver) {
			performUpdate($newver);
			break;
		}
		$newver = checkForUpdate();
		if ($newver) {
			echo $newver;
		}
		break;
	case 'CLIC':
		if (file_exists('my_cliref.html')) echo file_get_contents('my_cliref.html');
		if (file_exists('cliref.html')) echo file_get_contents('cliref.html');
		break;
	default:
		echo $_POST['act'];
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

function mmizFile ($path) {
	$pinf = pathinfo($path);
	if (!isset($pinf['extension']) || $pinf['extension'] != 'js') return;
	$min = json_decode(`curl -s -d compilation_level=SIMPLE_OPTIMIZATIONS -d output_format=json -d output_info=compiled_code -d output_info=errors -d output_info=warnings --data-urlencode "js_code@{$path}" https://closure-compiler.appspot.com/compile`);
	if (isset($min->errors)) {
		foreach ($min->errors as $cperr) {
			echo $cperr->type;
			echo "\n".$cperr->error;
			echo "\n\nLine: ".$cperr->lineno;
			echo ' Char: '.$cperr->charno;
			echo "\n".trim($cperr->line);
		}
		return;
	}
	if (isset($min->warnings)) {
		foreach ($min->warnings as $cpwrn) {
			echo $cpwrn->type;
			echo "\n".$cpwrn->warning;
			echo "\n\nLine: ".$cpwrn->lineno;
			echo ' Char: '.$cpwrn->charno;
			echo "\n".trim($cpwrn->line);
		}
	}
	if (isset($min->compiledCode))
		file_put_contents($pinf['dirname'].'/'.$pinf['filename'].'.min.'.$pinf['extension'], $min->compiledCode);
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

function alt_stat ($file) {
	clearstatcache();
	$ss = @stat($file);
	if (!$ss) return false; //Couldnt stat file

	$ts = array(
		0140000 => 'ssocket',
		0120000 => 'llink',
		0100000 => '-file',
		0060000 => 'bblock',
		0040000 => 'ddir',
		0020000 => 'cchar',
		0010000 => 'pfifo'
	);

	$p = $ss['mode'];
	$t = decoct($ss['mode'] & 0170000); // File Encoding Bit

	$str = (array_key_exists(octdec($t),$ts))?$ts[octdec($t)][0]:'u';
	$str .= (($p&0x0100)?'r':'-').(($p&0x0080)?'w':'-');
	$str .= (($p&0x0040)?(($p&0x0800)?'s':'x'):(($p&0x0800)?'S':'-'));
	$str .= (($p&0x0020)?'r':'-').(($p&0x0010)?'w':'-');
	$str .= (($p&0x0008)?(($p&0x0400)?'s':'x'):(($p&0x0400)?'S':'-'));
	$str .= (($p&0x0004)?'r':'-').(($p&0x0002)?'w':'-');
	$str .= (($p&0x0001)?(($p&0x0200)?'t':'x'):(($p&0x0200)?'T':'-'));

	$s = array(
		'perms' => array(
		'umask' => sprintf("%04o",@umask()),
		'human' => $str,
		'octal1' => sprintf("%o", ($ss['mode'] & 000777)),
		'octal2' => sprintf("0%o", 0777 & $p),
		'decimal' => sprintf("%04o", $p),
		'fileperms' => @fileperms($file),
		'mode1' => $p,
		'mode2' => $ss['mode']),
		'owner' => array(
			'fileowner' => $ss['uid'],
			'filegroup' => $ss['gid'],
			'owner' => (function_exists('posix_getpwuid')) ? @posix_getpwuid($ss['uid']) : '',
			'group' => (function_exists('posix_getgrgid')) ? @posix_getgrgid($ss['gid']) : ''
		),
		'file' => array(
			'filename' => $file,
			'realpath' => (@realpath($file) != $file) ? @realpath($file) : '',
			'dirname' => @dirname($file),
			'basename' => @basename($file)
		),
		'filetype' => array(
			'type' => substr($ts[octdec($t)],1),
			'type_octal' => sprintf("%07o", octdec($t)),
			'is_file' => @is_file($file),
			'is_dir' => @is_dir($file),
			'is_link' => @is_link($file),
			'is_readable' =>  @is_readable($file),
			'is_writable' =>  @is_writable($file)
		),
		'device' => array(
			'device' => $ss['dev'], //Device
			'device_number' => $ss['rdev'], //Device number, if device.
			'inode' => $ss['ino'], //File serial number
			'link_count' => $ss['nlink'], //link count
			'link_to' => (substr($ts[octdec($t)],1)=='link') ? @readlink($file) : ''
		),
		'size' => array(
			'size' => $ss['size'], //Size of file, in bytes.
			'blocks' => $ss['blocks'], //Number 512-byte blocks allocated
			'block_size' =>  $ss['blksize'] //Optimal block size for I/O.
		),
		'time' => array(
			'mtime' => $ss['mtime'], //Time of last modification
			'atime' => $ss['atime'], //Time of last access.
			'ctime' => $ss['ctime'], //Time of last status change
			'accessed' => @date('d M Y H:i:s',$ss['atime']),
			'modified' => @date('d M Y H:i:s',$ss['mtime']),
			'created' => @date('d M Y H:i:s',$ss['ctime'])
		)
	);
 
	clearstatcache();
	return $s;
}
