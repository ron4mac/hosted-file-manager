<?php
/**
* @package		hosted-file-manager
* @copyright	Copyright (C) 2022-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		4.0.0
*/
namespace Rjc\Hfm\Controllers;

defined('EXEC_HFM') or die('Access denied');

use Rjc\Hfm\Core\Cfg;
use Rjc\Hfm\Core\Input;
use Rjc\Hfm\Helpers\HfmHelper;
use Rjc\Hfm\Models\UpdaterModel;

class ActionController
{
	protected $fref;

	public function __construct (Input $input)
	{
		$this->fref = $input->postHas('fref') ? BASE_DIR.$input->path('fref') : '';
	}

	public function action (Input $input, Cfg $cfg): void
	{
		switch ($input->post('act')) {
			case 'cppa':
				$todir = escapeshellarg(BASE_DIR.$input->post('todr'));
				$path = BASE_DIR.$input->post('dir').'/';
				$files = $input->post('files');
				$rslt = 0;
				foreach ($files as $fle) {
					$farg = escapeshellarg($path.rtrim($fle,' /'));
					system('cp -a '.$farg.' '.$todir, $irslt);
					$rslt += $irslt;
				}
				if ($rslt) echo $rslt;
				break;
			case 'crlg':
			case 'crlp':
				$save_path = $input->post('path');
				$url = $input->post('url');
				$fullfile = BASE_DIR.$save_path.basename($url);
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
				if ($input->postHas('user') && $input->postHas('pass')) curl_setopt($ch, CURLOPT_USERPWD, $input->post('user').':'.$input->post('pass'));
				$rslt = curl_exec($ch);
				$msg = curl_error($ch);
				$ftime = curl_getinfo($ch, CURLINFO_FILETIME);
				curl_close($ch);
				fclose($fp);
				if ($msg) {
					@unlink($fullfile);	//remove the file
					echo $msg;
				} else {
					if ($ftime > 0) touch($fullfile, $ftime);
				}
				break;
			case 'trsh':
				$path = BASE_DIR.$input->path('dir');
				$files = $input->path('files');
				$todir = BASE_DIR.'tmp/Trash';
				@mkdir($todir, 0777, true);
				$rslt = '';
				foreach ($files as $fle) {
					$fod = rtrim($fle,' /');
					$fpth = $path.'/'.$fod;
					$tpth = $todir.'/'.$fod;
				//	if (is_dir($fpth) && file_exists($tpth)) {
				//		$nn = 1;
				//		while (file_exists($tpth.'_'.$nn)) {
				//			$nn++;
				//		}
				//		rename($tpth, $tpth.'_'.$nn);
				//	}
					$cmd = 'mv --backup=numbered -t '.escapeshellarg($todir).' '.escapeshellarg($fpth).' 2>&1';
					$rslt .= system($cmd, $irslt);
					$rslt .= $irslt ?: '';
				}
				if ($rslt) echo $cmd.$rslt;
				break;
			case 'mpty':
				$trd = BASE_DIR.'tmp/Trash/';
				$trshs = @scandir($trd);
				if (!$trshs) break;
				foreach ($trshs as $trsh) {
					if ($trsh === '.' || $trsh === '..') continue;
					$this->recursiveDelete($trd.$trsh, true);
				}
				break;
			case 'delf':
				$path = escapeshellcmd($input->post('dir'));
				$files = $input->post('files');
				foreach ($files as $fle) {
					$this->recursiveDelete(BASE_DIR.$path.'/'.rtrim($fle,' /'));
				}
				break;
			case 'dnld':
				$path = escapeshellcmd($input->post('dir'));
				$files = $input->post('files');
				if (count($files) === 1) {
					$sfil = $files[0];
					if ($sfil[strlen($sfil)-1]!=='/') {
						echo json_encode(['fpth' => $path."/$sfil"]);
						break;
					}
				}
				//zip required for multiples
				$zh = new \ZipArchive();
				$zfn = 'tmp/files.zip';
				if ($zh->open(BASE_DIR.$zfn, \ZIPARCHIVE::CREATE)!==TRUE) {
					exit("cannot open <$zfn>\n");
				}
				foreach ($files as $fle) {
					if ($fle[strlen($fle)-1]=='/') { $this->addDirToAcrhive(BASE_DIR.$path.'/', $fle, $zh); }
					else { $zh->addFile(BASE_DIR.$path.'/'.$fle, $fle); }
				}
				$zh->close();
				echo json_encode(['fpth' => $zfn,'rad' => 'Y']);
				break;
			case 'dupl':
				$path_parts = pathinfo($this->fref);
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
				$fileoi = escapeshellarg(urldecode($this->fref));
				$stat = $this->alt_stat(trim($fileoi,"'"));	//@stat(trim($fileoi,"'"));
				//system('stat --printf="%f %F<br />%A %U/%G<br />access: %x<br />modify: %y<br />change: %z" '.$fileoi,$rslt);
				//if ($rslt) echo $rslt . '<br />' . $fileoi;
				if (!$stat) die('Could not stat '.$fileoi);
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
				rename($this->fref,BASE_DIR.$input->path('nunm'));
				break;
			case 'fvue':
				$mtyp = FileMimeType($this->fref);
				header('Content-Type: '.$mtyp);
				$fcon = nl2br(htmlspecialchars(file_get_contents($this->fref)));
				echo $fcon;
				break;
			case 'jsmm':
				$ccp = $input->post('CC');
				$ccp['js_code'] = file_get_contents(BASE_DIR.$input->post('path').$input->post('up_fn'));
				$ccp['js_name'] = $input->post('up_fn');
				$pdat = http_build_query($ccp);	//.'&output_info=compiled_code&output_info=warnings&output_info=errors&output_info=statistics';
				$fdat = $this->curld('https://rjcransdev.com/jsminify/index.php', $pdat);
			//	file_put_contents('JSMINI.txt',print_r($fdat,true)); //break;
				$rdat = json_decode($fdat);
				if (isset($rdat->code)) {
					$rmjsdoc = $input->post('rmjsdoc', false);
					if ($rmjsdoc) $rdat->code = preg_replace('#/\*.+\*/[\s]*#s', '', $rdat->code);
					file_put_contents(BASE_DIR.$input->post('path').$input->post('tofile'), $rdat->code);
				} else {
					echo "Minification was not possible.\n";
					echo $rdat->error;
				}
				break;
			case 'mvto':
				$todir = escapeshellarg(BASE_DIR.$input->post('todr'));
				$path = BASE_DIR.$input->post('dir').'/';
				$files = $input->post('files');
				$rslt = 0;
				foreach ($files as $fle) {
					$cmd = 'mv -b -t '.$todir.' '.escapeshellarg($path.rtrim($fle,' /'));
					system($cmd, $irslt);
					$rslt += $irslt;
				}
				if ($rslt) echo $cmd.$rslt;
				break;
			case 'nfld':
				mkdir($this->fref);
				break;
			case 'nfle':
				$fh = fopen($this->fref,'x');
				fclose($fh);
				break;
			case 'jxtr':
				require_once 'joomext.php';
				$joomext = new Joomext($this->fref);
				$joomext->pull(escapeshellcmd($input->post('dir')));
				break;
			case 'fmxi':
				$newver = HfmHelper::checkForUpdate();
				$msg = 'FMX Version: ' . $cfg->val('fmxVersion');
				$msg .= '<br />PHP Version: ' . phpversion();
				$msg .= function_exists('mysqli_get_client_info') ? ('<br />MySql(i) Client Version: ' . str_replace('$','',mysqli_get_client_info())) : '<br /><span class="notify">No PHP myqsli support</span>';
				if (class_exists('SQLite3')) {
					$sql3v = \SQLite3::version();
					$msg .= '<br />SQLite3 Version: ' . $sql3v['versionString'];
				} else {
					$msg .= '<br /><span class="notify">SQLite3 not available</span>';
				}
				if (!class_exists('ZipArchive')) {
					$msg .= '<br /><span class="notify">No PHP ZipArchive support</span>';
				}
				$msg .= '<br /><br />';
				if ($newver) {
					$vinf = explode('|', $newver);
					$msg .= '<span class="notify">There is an FMX update: '.$vinf[0].'</span>';
				} else {
					$msg .= 'There is no available FMX update.';
				}
				echo json_encode(['updt' => $newver,'msg' => $msg]);
				break;
			case 'slnk':
				$droot = dirname($input->server('DOCUMENT_ROOT')).'/';
				$bk = 0;
				$lfr = $droot.urldecode($input->post('fref'));
				$lat = $lad = $droot.$input->post('tref');
				while (substr($lfr, 0, strlen($lat)) !== $lat) {
					$lat = dirname($lat);
					$bk++;
				}
				$arg1 = escapeshellarg(str_repeat('../', $bk).rtrim(substr($lfr, strlen($lat)+1),'/'));
				$arg2 = escapeshellarg($lad . $input->post('alnk'));
				$cmd = 'ln -s '.$arg1.' '.$arg2;
				system($cmd, $rslt);
				if ($rslt) echo $cmd.$rslt;
				break;
			case 'updt':
				$newver = escapeshellcmd($input->post('nver'));
				if ($newver) {
					$m = new UpdaterModel();
					$m->performUpdate($newver);
					break;
				}
				$newver = checkForUpdate();
				if ($newver) {
					echo $newver;
				}
				break;
			case 'CLIC':
				if (file_exists('cliref.html')) echo file_get_contents('cliref.html');
				if (file_exists('src/Etc/cliref.html')) echo file_get_contents('src/Etc/cliref.html');
				break;
			default:
				echo $input->post('act');
		}
	}

	private function recursiveDelete ($pstr, $dlnk=false): void
	{
		if ($dlnk && is_link($pstr)) { @unlink($pstr); }
		elseif (is_file($pstr)) { @unlink($pstr); }
		elseif (is_dir($pstr)) {
			$dh = opendir($pstr);
			while ($node = readdir($dh)) {
				if ($node !== '.' && $node !== '..') {
					$path = $pstr.'/'.$node;
					$this->recursiveDelete($path, $dlnk);
				}
			}
			closedir($dh);
			@rmdir($pstr);
		}
	}

	private function alt_stat ($file): false|array
	{
		clearstatcache();
		$ss = @stat($file);
		if (!$ss) return false; //Couldnt stat file
	
		$ts = [
			0140000 => 'ssocket',
			0120000 => 'llink',
			0100000 => '-file',
			0060000 => 'bblock',
			0040000 => 'ddir',
			0020000 => 'cchar',
			0010000 => 'pfifo'
		];
	
		$p = $ss['mode'];
		$t = decoct($ss['mode'] & 0170000); // File Encoding Bit
	
		$str = (array_key_exists(octdec($t),$ts))?$ts[octdec($t)][0]:'u';
		$str .= (($p&0x0100)?'r':'-').(($p&0x0080)?'w':'-');
		$str .= (($p&0x0040)?(($p&0x0800)?'s':'x'):(($p&0x0800)?'S':'-'));
		$str .= (($p&0x0020)?'r':'-').(($p&0x0010)?'w':'-');
		$str .= (($p&0x0008)?(($p&0x0400)?'s':'x'):(($p&0x0400)?'S':'-'));
		$str .= (($p&0x0004)?'r':'-').(($p&0x0002)?'w':'-');
		$str .= (($p&0x0001)?(($p&0x0200)?'t':'x'):(($p&0x0200)?'T':'-'));
	
		$s = [
			'perms' => [
				'umask' => sprintf('%04o',@umask()),
				'human' => $str,
				'octal1' => sprintf('%o', ($ss['mode'] & 000777)),
				'octal2' => sprintf('0%o', 0777 & $p),
				'decimal' => sprintf('%04o', $p),
				'fileperms' => @fileperms($file),
				'mode1' => $p,
				'mode2' => $ss['mode']
			],
			'owner' => [
				'fileowner' => $ss['uid'],
				'filegroup' => $ss['gid'],
				'owner' => (function_exists('posix_getpwuid')) ? @posix_getpwuid($ss['uid']) : '',
				'group' => (function_exists('posix_getgrgid')) ? @posix_getgrgid($ss['gid']) : ''
			],
			'file' => [
				'filename' => $file,
				'realpath' => (@realpath($file) != $file) ? @realpath($file) : '',
				'dirname' => @dirname($file),
				'basename' => @basename($file)
			],
			'filetype' => [
				'type' => substr($ts[octdec($t)],1),
				'type_octal' => sprintf('%07o', octdec($t)),
				'is_file' => @is_file($file),
				'is_dir' => @is_dir($file),
				'is_link' => @is_link($file),
				'is_readable' =>  @is_readable($file),
				'is_writable' =>  @is_writable($file)
			],
			'device' => [
				'device' => $ss['dev'], //Device
				'device_number' => $ss['rdev'], //Device number, if device.
				'inode' => $ss['ino'], //File serial number
				'link_count' => $ss['nlink'], //link count
				'link_to' => (substr($ts[octdec($t)],1) === 'link') ? @readlink($file) : ''
			],
			'size' => [
				'size' => $ss['size'], //Size of file, in bytes.
				'blocks' => $ss['blocks'], //Number 512-byte blocks allocated
				'block_size' =>  $ss['blksize'] //Optimal block size for I/O.
			],
			'time' => [
				'mtime' => $ss['mtime'], //Time of last modification
				'atime' => $ss['atime'], //Time of last access.
				'ctime' => $ss['ctime'], //Time of last status change
				'accessed' => @date('d M Y H:i:s',$ss['atime']),
				'modified' => @date('d M Y H:i:s',$ss['mtime']),
				'created' => @date('d M Y H:i:s',$ss['ctime'])
			]
		];
	 
		clearstatcache();
		return $s;
	}

	private function addDirToAcrhive ($base,$dirn,$zh): void
	{
		if (!is_dir($base.$dirn)) {
			throw new Exception('Directory ' . $dirName . ' does not exist');
		}
	
		$dirName = realpath($base.$dirn);
		if (substr($dirName, -1) !== '/') {
			$dirName.= '/';
		}
	
		$dirStack = [$dirName];
		//Find the index where the last dir starts
		$cutFrom = strrpos(substr($dirName, 0, -1), '/')+1;
	
		while ($dirStack !== []) { 
			$currentDir = array_pop($dirStack);
			$filesToAdd = [];
	
			$dir = dir($currentDir);
			while (false !== ($node = $dir->read())) {
				if (($node == '..') || ($node == '.')) { continue; }
				if (is_dir($currentDir . $node)) {
					$dirStack[] = $currentDir . $node . '/';
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

	private function curld ($url, $data=''): null|bool|string
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_PORT , 443);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$rData = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Curl error: ' . curl_error($ch);
			return null;
		}
		curl_close($ch);
		return $rData;
	}

}