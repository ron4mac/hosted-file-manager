<?php
$rmtuser = getenv('REMOTE_USER');
$cooknam = 'fil_vew' . ($rmtuser ? "_$rmtuser" : '');
$cookie = $_COOKIE[$cooknam];
if (!$cookie) { exit('Unauthorized'); }
$baseDir = convert_uudecode($cookie).'/';
$fmxVersion = '2.9.7 - May 2014';

function FileMimeType ($fpath) {
	$mtyp = 'text/plain';
	if (function_exists('finfo_open')) {
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mtyp = finfo_file($finfo, $fpath);
	} elseif (function_exists('mime_content_type')) { 
		$mtyp = mime_content_type($fpath);
	} else {
		$sfe = array();
		$rslt = 0;
		$sf = exec('file --mime-type -b '.$fpath, $sfe, $rslt);
		//var_dump($sf,$sfe,$rslt);exit();
		if (!$rslt) $mtyp = $sf;
	}
	return $mtyp;
}

function return_bytes ($val) {
	$val = trim($val);
	$last = strtolower($val[strlen($val)-1]);
	switch($last) {
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
