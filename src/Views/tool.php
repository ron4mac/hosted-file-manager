<?php
/**
* @package		hosted-file-manager
* @copyright	Copyright (C) 2022-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		4.0.7
*/
defined('EXEC_HFM') or die('Access denied');

include 'src/Etc/tools/'.$input->get('tool').'.php';
chdir(BASE_DIR . urldecode($input->get('dir')));
$tool = new Tool();
$tool->action();
