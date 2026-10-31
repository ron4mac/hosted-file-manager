<?php
/**
* @package		hosted-file-manager
* @copyright	Copyright (C) 2022-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		4.0.0
*/
namespace Rjc\Hfm\Controllers;

defined('EXEC_HFM') or die('Access denied');

use Rjc\Hfm\Core\App;
use Rjc\Hfm\Core\Cfg;
use Rjc\Hfm\Core\Input;

class OtherController
{
	public function gitter (Input $input): void
	{
		App::render('gitter', [
			'input' => $input
		]);
	}

	public function jsmini (Input $input): void
	{
		App::render('jsmini', [
			'input' => $input
		]);
	}

	public function medya (Input $input): void
	{
		App::render('media', [
			'input' => $input
		]);
	}

}