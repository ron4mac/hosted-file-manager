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
$fmxVersion = '3.4.2 - September 2021';

function FileMimeType ($fpath)
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
	if ($mtyp=='image/svg') $mtyp = 'image/svg+xml';
	return $mtyp;
}

function return_bytes ($val)
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

function int2kmg (int $val)
{
	$sz = $val;
	if ($sz > 1073741824) {$sz = sprintf('%.1f', ($sz / 1073741824)). 'g';}
	elseif ($sz > 1048575) {$sz = sprintf('%.1f', ($sz / 1048576)) . 'm';}
	elseif ($sz > 1023) {$sz = sprintf('%.1f', ($sz / 1024)) . 'k';}
	return $sz;
}

function fmx_getJoomlaUserId ()
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

//
// some common variable/array values
//
$upld_err_txt = [
	1=>'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
	'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
	'The uploaded file was only partially uploaded.',
	'No file was uploaded.',
	6=>'Missing a temporary folder.',
	'Failed to write file to disk.',
	'A PHP extension stopped the file upload.'
	];


