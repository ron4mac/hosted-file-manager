<?php
/**
* @package		hosted-file-manager
* @copyright	Copyright (C) 2022-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		4.0.0
*/
// this file proxy is used to facilitate viewing files outside of the webroot
// include all thats needed from HFM to get needed parms
define('EXEC_HFM', true);
require_once '../Helpers/HfmHelper.php';
require_once '../Core/Request.php';
require_once '../Core/Input.php';
require_once '../Core/App.php';
require_once '../Core/Cfg.php';
$app = new \Rjc\Hfm\Core\App();

$getPath = function($pth) {
    return $this->input->path($pth);
};

$fp = BASE_DIR . urldecode($getPath->call($app,'f'));
if (!file_exists($fp)) die('nosuchfile: '.$fp);
// get a mime type
$mtype = \Rjc\Hfm\Helpers\HfmHelper::FileMimeType($fp);
header("Content-Type: $mtype");
readfile($fp);
