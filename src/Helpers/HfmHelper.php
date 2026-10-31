<?php
/**
* @package		hosted-file-manager
* @copyright	Copyright (C) 2022-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		4.0.0
*/
namespace Rjc\Hfm\Helpers;

defined('EXEC_HFM') or die('Access denied');

use Rjc\Hfm\Core\Cfg;

class HfmHelper
{
	public static function FileMimeType ($fpath): string|false
	{
		$mtyp = 'text/plain';
		if (function_exists('finfo_file')) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mtyp = finfo_file($finfo, $fpath);
			finfo_close($finfo);
		} elseif (function_exists('mime_content_type')) { 
			$mtyp = mime_content_type($fpath);
		} else {
			$sfe = [];
			$rslt = 0;
			$sf = exec('file --mime-type -b '.$fpath, $sfe, $rslt);
			if (!$rslt) $mtyp = $sf;
		}
		// workaround for php mis-interpretation of svg file
		if ($mtyp=='image/svg') return 'image/svg+xml';
		return $mtyp;
	}
	
	public static function return_bytes ($val): int
	{
		$val = trim($val);
		$last = strtolower($val[strlen($val)-1]);
		$val = (int) $val;
		switch ($last) {
			case 'g': $val *= 1024;
			case 'm': $val *= 1024;
			case 'k': $val *= 1024;
		}
		return $val;
	}
	
	public static function int2kmg (int $val): string|int
	{
		$sz = $val;
		if ($sz > 1073741824) {$sz = sprintf('%.1f', ($sz / 1073741824)). 'g';}
		elseif ($sz > 1048575) {$sz = sprintf('%.1f', ($sz / 1048576)) . 'm';}
		elseif ($sz > 1023) {$sz = sprintf('%.1f', ($sz / 1024)) . 'k';}
		return $sz;
	}
	
	public static function fmx_getJoomlaUserId ()
	{
		define( '_JEXEC', 1 );
		define( 'JPATH_BASE', realpath(dirname(__FILE__).'/../../..' ));
		define( 'DS', DIRECTORY_SEPARATOR );
	
		require_once JPATH_BASE.DS.'includes'.DS.'defines.php';
		require_once JPATH_BASE.DS.'includes'.DS.'framework.php';
		$app = JFactory::getApplication('site');
		$app->initialise();
		return JFactory::getUser()->id;
	}

	public static function curly ($url,$agent): bool|string
	{
		// create curl resource
		$ch = curl_init();
		// set url
		curl_setopt($ch, CURLOPT_URL, $url);
		//return the transfer as a string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, $agent);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		// $output contains the output string
		$output = curl_exec($ch);
		// close curl resource to free up system resources
		curl_close($ch);
		// return the data
		return $output;
	}
	
	public static function getUrlData ($url): bool|string
	{
		global $github_credential;	// optional and set in 'fmx.ini' file as <username>:<password>
		if ($github_credential) {
			$url = str_replace('//','//'.$github_credential.'@',$url);
		}
		$ua = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.75.14 (KHTML, like Gecko) Version/7.0.3 Safari/537.75.14';
		if (ini_get('allow_url_fopen')) {
			ini_set('user_agent', $ua);
			$rfc = file_get_contents($url);
			if (!$rfc) return curly($url, $ua);
			return $rfc;
		}
		return curly($url, $ua);
	}
	
	public static function checkForUpdate (): string|false
	{
		$fmxVersion = (new Cfg())->val('fmxVersion');
		$fv = substr($fmxVersion, 0, strpos($fmxVersion,' '));
		// get release list from github (ignore errors in case of refused access)
		$fmx_releases = json_decode(@self::getUrlData('https://api.github.com/repos/ron4mac/hosted-file-manager/releases'));
		if (!$fmx_releases) { echo 'Release of FMX not found at Github'; exit(); }
		$fmx_ball = $fmx_releases[0]->zipball_url;
		$uver = $fmx_releases[0]->tag_name;
		if (preg_match('/\d+\.\d+\.\d+/',$uver,$m)) {
			if (version_compare($m[0], $fv) === 1)
			return $uver.'|'.$fmx_ball;
		}
		return false;
	}

}