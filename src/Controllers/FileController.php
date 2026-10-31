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
use Rjc\Hfm\Models\UploadModel;

class FileController
{
	protected $fref;

	public function __construct (Input $input)
	{
		$this->fref = $input->get('fref', '');
	}

	public function view (Input $input): void
	{
		if ($input->get('C','')) {
			$mtyp = escapeshellcmd($input->get('mtyp',''));
			App::render('filevue', ['fref' => $this->fref, 'mtyp' => $mtyp]);
		} else {
			App::render('fileview', ['fref' => $this->fref]);
		}
	}

	public function edit (Input $input, Cfg $cfg): void
	{
		App::render('fileedit', [
			'fref' => $this->fref,
			'aceBase' => $cfg->val('aceBase'),
			'acetheme' => $cfg->val('acetheme'),
			'cfg' => $cfg,
			'input' => $input
		]);
	}

	public function save (Input $input, Cfg $cfg): void
	{
		if (!$input->post('savef', false)) die('File submission error');
	//	file_put_contents('SAVEPOST.txt',print_r($input->post(),true));
		$fref = $input->post('fref');
		if (!$fref) die('File submission error');
		$fpath = BASE_DIR . $fref;
		if ($cfg->val('backup_edit', false) && $input->post('bkup', false)) {
			$bc = $cfg->val('backup_edit');
			$sp = $fpath.'~';
			while ($bc-- && file_exists($sp)) {
				$sp .= '~';
			}
			if ($bc >= 0) {
				if (!rename($fpath, $sp)) {
					die('FAILED TO BACKUP ORIGINASL :O(');
				}
			}
		}
		$fcon = $input->raw('fcontent');
		$rslt = file_put_contents($fpath, str_replace("\r\n","\n",$fcon));
		if ($rslt === FALSE) {
			die('FAILED TO SAVE :O(');
		}
	}

	public function arcview (Input $input, Cfg $cfg): void
	{
		App::render('arcview', [
			'fref' => $this->fref,
			'cfg' => $cfg,
			'input' => $input
		]);
	}

	public function download (Input $input): void
	{
		$fref = BASE_DIR . $input->get('fle');
		if (file_exists($fref)) {
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename='.basename($fref));
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($fref));
			@ob_clean();
			@flush();
			readfile($fref);
			if ($input->get('rad')=='Y') unlink($fref);
			exit;
		}
	}

	public function filupld5 (Input $input): void
	{
		App::render('filupld5', [
			'fref' => $this->fref,
			'input' => $input
		]);
	}

	public function upload (Input $input): void
	{
		$m = new UploadModel($input);
		$m->process();
	}

	public function imgedit (Input $input, Cfg $cfg): void
	{
		App::render('imgedit', [
			'fref' => $input->post('fref'),
			'cfg' => $cfg,
			'input' => $input
		]);
	}

	public function filcurlm (Input $input): void
	{
		App::render('filcurlm', [
			'input' => $input
		]);
	}

	public function test (Input $input): void
	{
		echo'<xmp>';var_dump($input);echo'</xmp>';
	}

}