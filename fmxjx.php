<?php
include 'fmx.ini';
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
		$pfxp = isset($path_parts['extension'])?('.'.$path_parts['extension']):'';		//file path estension part
		$nn = 1;
		while (file_exists($pfmx.'_'.$nn.$pfxp)) {
			$nn++;
			}
		system('cp -a "'.$pfmx.$pfxp.'" "'.$pfmx.'_'.$nn.$pfxp.'"',$rslt);
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
		require_once('updater.php');
		$newver = checkForUpdate();
		$msg = 'FMX Version: ' . $fmxVersion;
		$msg .= '<br />PHP Version: ' . phpversion();
		$msg .= '<br />MySql(i) Client Version: ' . mysqli_get_client_info();
		$sql3v = SQLite3::version();
		$msg .= '<br />SQLite3 Version: ' . $sql3v['versionString'];
		$msg .= '<br /><br />';
		if ($newver) {
			$vinf = explode('|', $newver);
			$msg .= '<span class="notify">There is an FMX update: '.$vinf[0].'</span>';
		} else {
			$msg .= 'There is no available FMX update.';
		}
		echo json_encode(array('updt'=>$newver,'msg'=>$msg));
		break;
	case 'updt':
		$newver = escapeshellcmd($_POST['nver']);
		require_once('updater.php');
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
		echo file_get_contents('my_cliref.html');
		echo file_get_contents('cliref.html');
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

function alt_stat ($file) {
	clearstatcache();
	$ss=@stat($file);
	if(!$ss) return false; //Couldnt stat file

	$ts=array(
		0140000=>'ssocket',
		0120000=>'llink',
		0100000=>'-file',
		0060000=>'bblock',
		0040000=>'ddir',
		0020000=>'cchar',
		0010000=>'pfifo'
	);

	$p=$ss['mode'];
	$t=decoct($ss['mode'] & 0170000); // File Encoding Bit

	$str =(array_key_exists(octdec($t),$ts))?$ts[octdec($t)]{0}:'u';
	$str.=(($p&0x0100)?'r':'-').(($p&0x0080)?'w':'-');
	$str.=(($p&0x0040)?(($p&0x0800)?'s':'x'):(($p&0x0800)?'S':'-'));
	$str.=(($p&0x0020)?'r':'-').(($p&0x0010)?'w':'-');
	$str.=(($p&0x0008)?(($p&0x0400)?'s':'x'):(($p&0x0400)?'S':'-'));
	$str.=(($p&0x0004)?'r':'-').(($p&0x0002)?'w':'-');
	$str.=(($p&0x0001)?(($p&0x0200)?'t':'x'):(($p&0x0200)?'T':'-'));

	$s=array(
		'perms'=>array(
		'umask'=>sprintf("%04o",@umask()),
		'human'=>$str,
		'octal1'=>sprintf("%o", ($ss['mode'] & 000777)),
		'octal2'=>sprintf("0%o", 0777 & $p),
		'decimal'=>sprintf("%04o", $p),
		'fileperms'=>@fileperms($file),
		'mode1'=>$p,
		'mode2'=>$ss['mode']),
		'owner'=>array(
			'fileowner'=>$ss['uid'],
			'filegroup'=>$ss['gid'],
			'owner'=>(function_exists('posix_getpwuid')) ? @posix_getpwuid($ss['uid']) : '',
			'group'=>(function_exists('posix_getgrgid')) ? @posix_getgrgid($ss['gid']) : ''
		),
		'file'=>array(
			'filename'=>$file,
			'realpath'=>(@realpath($file) != $file) ? @realpath($file) : '',
			'dirname'=>@dirname($file),
			'basename'=>@basename($file)
		),
		'filetype'=>array(
			'type'=>substr($ts[octdec($t)],1),
			'type_octal'=>sprintf("%07o", octdec($t)),
			'is_file'=>@is_file($file),
			'is_dir'=>@is_dir($file),
			'is_link'=>@is_link($file),
			'is_readable'=> @is_readable($file),
			'is_writable'=> @is_writable($file)
		),
		'device'=>array(
			'device'=>$ss['dev'], //Device
			'device_number'=>$ss['rdev'], //Device number, if device.
			'inode'=>$ss['ino'], //File serial number
			'link_count'=>$ss['nlink'], //link count
			'link_to'=>(substr($ts[octdec($t)],1)=='link') ? @readlink($file) : ''
		),
		'size'=>array(
			'size'=>$ss['size'], //Size of file, in bytes.
			'blocks'=>$ss['blocks'], //Number 512-byte blocks allocated
			'block_size'=> $ss['blksize'] //Optimal block size for I/O.
		),
		'time'=>array(
			'mtime'=>$ss['mtime'], //Time of last modification
			'atime'=>$ss['atime'], //Time of last access.
			'ctime'=>$ss['ctime'], //Time of last status change
			'accessed'=>@date('d M Y H:i:s',$ss['atime']),
			'modified'=>@date('d M Y H:i:s',$ss['mtime']),
			'created'=>@date('d M Y H:i:s',$ss['ctime'])
		)
	);
 
	clearstatcache();
	return $s;
}


?>
