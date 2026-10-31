<?php
/**
* @package		hosted-file-manager
* @copyright	Copyright (C) 2022-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		4.0.0
*/
namespace Rjc\Hfm\Models;

defined('EXEC_HFM') or die('Access denied');

use Rjc\Hfm\Helpers\HfmHelper;

class UpdaterModel
{

	public static function performUpdate ($which): void
	{
		$tmpf = (sys_get_temp_dir()?sys_get_temp_dir():BASE_DIR.'__tmp__').'/fmx_upd_'.time().'.zip';
		$newUpdate = HfmHelper::getUrlData($which);
		$dlHandler = fopen($tmpf, 'w');
		if (!fwrite($dlHandler, $newUpdate)) { echo 'Could not save new update. Operation aborted.'; return; }
		fclose($dlHandler);
		self::installUpdate($tmpf);
		unlink($tmpf);
	}
	
	// recursively delete a directory
	private static function delTree ($dir): void
	{
		if (!file_exists($dir)) return;
		$files = array_diff(scandir($dir), ['.','..']);
		foreach ($files as $file) {
			$pth = "$dir/$file";
			(is_dir($pth)) ? self::delTree($pth) : @unlink($pth);
		}
		@rmdir($dir);
	}
	
	// remove defunct files from a given list
	private static function cleanupFiles ($fray): void
	{
		foreach ($fray as $f) {
			if (substr($f,-1) === '/') {
				self::delTree(substr($f,0,-1));
			} else {
				@unlink($f);
			}
		}
	}
	
	private static function installUpdate ($fpath): void	// NEED TO GET PATH TO INSTALL
	{
		$f2p = ['.user.ini','fmx.ini'];	//files to preserve if they already exist
		$zip = new \ZipArchive();
		$res = $zip->open($fpath);
		$fils2clean = [];
		if ($res === TRUE) {
			for ($i = 0; $i < $zip->numFiles; $i++ ) {
				$stat = $zip->statIndex( $i );
				//echo $stat['name'] . '<br />';
				list($bd,$fp) = explode('/', $stat['name'], 2);
				if (substr($fp, -1) === '/') {
					@mkdir($fp);
					//self::logIt("DIR: {$fp}");
				} elseif ($fp) {
					if (in_array($fp, $f2p) && file_exists($fp)) continue;
					$fc = $zip->getFromIndex($i);
					if (basename($fp) === 'clean.json') {
						$fils2clean = json_decode($fc, true);
					} else {
						file_put_contents($fp, $fc);
					}
					//self::logIt("PUT: {$fp}");
				}
			}
			$zip->close();
		//	self::cleanupFiles($fils2clean);
		} else {
			echo 'failed, code:' . $res;
		}
	}

	private static function logIt ($msg): void
	{
		file_put_contents('log.txt', "{$msg}\n", FILE_APPEND);
	}
	

}
