<?php
include 'fmx.ini';
if ($fmxInJoomla) {
	if (isset($this)) {
		$userN = $this->user->id;
	} else {
		$userN = fmx_getJoomlaUserId();
	}
	$cooknam = 'jfil_vew' . ($userN ? "_$userN" : '');
} else {
	$rmtuser = getenv('REMOTE_USER');
	$cooknam = 'fil_vew' . ($rmtuser ? "_$rmtuser" : '');
}
$cookie = $_COOKIE[$cooknam];
if (!$cookie) { exit('Unauthorized'); }
$baseDir = convert_uudecode($cookie).'/';
$fmxVersion = '3.1.6 - November 2017';

function FileMimeType ($fpath) {
	$mtyp = 'text/plain';
	if (function_exists('finfo_file')) {
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mtyp = finfo_file($finfo, $fpath);
		finfo_close($finfo);
	} elseif (function_exists('mime_content_type')) { 
		$mtyp = mime_content_type($fpath);
	} else {
		$sfe = array();
		$rslt = 0;
		$sf = exec('file --mime-type -b '.$fpath, $sfe, $rslt);
		if (!$rslt) $mtyp = $sf;
	}
	return $mtyp;
}

function return_bytes ($val) {
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

function doUnescape ($inp) {
	if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) return stripslashes($inp);
	else return $inp;
}

function fmx_getJoomlaUserId() {
	define( '_JEXEC', 1 );
	define( 'JPATH_BASE', realpath(dirname(__FILE__).'/../../..' ));
	define( 'DS', DIRECTORY_SEPARATOR );

	require_once JPATH_BASE.DS.'includes'.DS.'defines.php';
	require_once JPATH_BASE.DS.'includes'.DS.'framework.php';
	$app = JFactory::getApplication('site');
	$app->initialise();
	return JFactory::getUser()->id;
}