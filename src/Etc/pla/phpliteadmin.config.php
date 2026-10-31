<?php 
/**
* @package		hosted-file-manager
* @copyright	Copyright (C) 2022-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		4.0.0
*/
//defined('EXEC_HFM') or die('Access denied');

//
// This is sample configuration file
//
// You can configure phpliteadmin in one of 2 ways:
// 1. Rename phpliteadmin.config.sample.php to phpliteadmin.config.php and change parameters in there.
//    You can set only your custom settings in phpliteadmin.config.php. All other settings will be set to defaults.
// 2. Change parameters directly in main phpliteadmin.php file
//
// Please see http://code.google.com/p/phpliteadmin/wiki/Configuration for more details

//password to gain access
$password = 'hfm-fmx';

//directory relative to this file to search for databases (if false, manually list databases in the $databases variable)
$directory = false;	//'.';

//whether or not to scan the subdirectories of the above directory infinitely deep
$subdirectories = false;

//if the above $directory variable is set to false, you must specify the databases manually in an array as the next variable
//if any of the databases do not exist as they are referenced by their path, they will be created automatically
$databases = [
	['path'=> 'database1.sqlite', 'name'=> 'Database 1'],
	['path'=> 'database2.sqlite', 'name'=> 'Database 2']
];

/* ---- Interface settings ---- */

// Theme! If you want to change theme, save the CSS file in same folder of phpliteadmin or in folder "themes"
$theme = 'modern.css';	//'phpliteadmin.css';

// the default language! If you want to change it, save the language file in same folder of phpliteadmin or in folder "languages"
// More about localizations (downloads, how to translate etc.): http://code.google.com/p/phpliteadmin/wiki/Localization
$language = 'en';

// set default number of rows. You need to relog after changing the number
$rowsNum = 100;

// reduce string characters by a number bigger than 10
$charsNum = 300;

// maximum number of SQL queries to save in the history
$maxSavedQueries = 10;

/* ---- Custom functions ---- */

//a list of custom functions that can be applied to columns in the databases
//make sure to define every function below if it is not a core PHP function
$custom_functions = [
	'md5', 'sha1', 'strtotime',
	// add the names of your custom functions to this array
	/* 'leet_text', */
];

// define your custom functions here
/*
function leet_text($value)
{
  return strtr($value, 'eaAsSOl', '344zZ01');
}
*/

/* ---- Advanced options ---- */

//changing the following variable allows multiple phpLiteAdmin installs to work under the same domain.
$cookie_name = 'pla3fmx';

//whether or not to put the app in debug mode where errors are outputted
$debug = false;

// the user is allowed to create databases with only these extensions
$allowed_extensions = ['db','db3','sqlite','sqlite3'];

// BLOBs are displayed and edited as hex string
$hexblobs = false;


/*====== below here added for FMX ======*/

define('BASE_DIR', dirname($_SERVER['DOCUMENT_ROOT']).'/');
define('EXEC_HFM', true);
require_once '../../Core/Cfg.php';
$cooknam = (new Rjc\Hfm\Core\Cfg())->val('cooknam');
$password = '';

if (isset($dev_mode) and $dev_mode) $debug = true;

if (isset($_GET['dbf'])) {
	$dbfn = $_GET['dbf'];
	$databases = [['path'=> BASE_DIR.$dbfn, 'name'=> basename($dbfn)]];
} elseif (isset($_GET['dir'])) {
	$directory = BASE_DIR.$_GET['dir'];
	$expire = time()+60*60*24; //set expiration to 1 day from now
	setcookie($cooknam.'_pla', $directory, $expire, '', '', false, true);
} else {
	$databases = null;
	$directory = $_COOKIE[$cooknam.'_pla'];
}