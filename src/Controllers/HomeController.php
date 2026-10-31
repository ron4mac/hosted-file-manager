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
use Rjc\Hfm\Models\HomeModel;

class HomeController
{
	protected $pDir;
	protected $rootD;
	protected $basDir;
	protected $rDir;
	protected $srtBy;
	protected $msg = '';

	public function __construct (Input $input, Cfg $cfg)
	{
		$this->pDir = urldecode($input->path('dir', ''));
		$cooknam = $cfg->val('cooknam');
		// establish (or get) a base directory path
		if ($input->getHas('bgn')) {
			$basDir = $input->get('bgn');
			$input->setCookie($cooknam,convert_uuencode($basDir));
		} else {
			$basDir = $input->cookieHas($cooknam) ? convert_uudecode($input->cookie($cooknam)) : false;
			if (!$basDir) {
				$basDir = dirname($input->server('DOCUMENT_ROOT'));
				$input->setCookie($cooknam,convert_uuencode($basDir));
			}
		}
		// full path to current directory
		$this->rDir = $basDir . ($this->pDir ? "/$this->pDir" : '');
		// current directory name
		$this->rootD = basename($basDir);
		$this->basDir = $basDir;
		$this->srtBy = $input->get('O','n');
	}

	public function index (Input $input, Cfg $cfg): void
	{
		$rsptxt = '';
		if ($input->post('cmdlin')) {
			$cmd = $input->raw('cmdlin');		//$input->post('cmdlin');
		//	if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) { $cmd = stripslashes($cmd); }
			if ($input->post('mcmdlin')) { $cmd = str_replace('::',BASE_DIR.'/',$input->post('mcmdlin')); }
			chdir($this->rDir);
			$rsptxt = `$cmd 2>&1`;
			// get rid of backspaced characters (found in man pages)
			$rsptxt && $rsptxt = trim(preg_replace('/.\x08/','',$rsptxt));
			// mostly because of a possible </textarea>, escape tags
			$rsptxt && $rsptxt = trim(preg_replace(['/<xmp/','/<\/xmp/'], ['<x m p','</x m p'], $rsptxt));		//removed since using <xmp> to wrap output
		}
//$this->msg = '<xmp>'.print_r((new Cfg())->all_val(), true).'</xmp>';
		$m = new HomeModel($cfg);
		$flist = $m->index($input, $this->rDir, '');
		App::render('home', [
			'input' => $input,
			'cfg' => $cfg,
			'basDir' =>$this->basDir,
			'pDir' => $this->pDir,
			'rootD' => $this->rootD,
			'fontawsm' => $cfg->val('fontawsm'),
			'rDir' => $this->rDir,
			'flist' => $flist,
			'srtBy' => $this->srtBy,
			'rsptxt' => $rsptxt,
			'spec_msg' => $this->msg
			]);
	}

	public function submit (Input $input): void
	{

		if (isset($_POST['cmdlin'])) {
			$cmd = $_POST['cmdlin'];
		//	if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) { $cmd = stripslashes($cmd); }
			if (isset($_POST['mcmdlin']) && $_POST['mcmdlin']) { $cmd = str_replace('::',$basDir.'/',$_POST['mcmdlin']); }
			chdir($rDir);
			$rsptxt = `$cmd 2>&1`;
			// get rid of backspaced characters (found in man pages)
			$rsptxt && $rsptxt = trim(preg_replace('/.\x08/','',$rsptxt));
			// mostly because of a possible </textarea>, escape tags
			$rsptxt && $rsptxt = trim(preg_replace(['/<xmp/','/<\/xmp/'], ['<x m p','</x m p'], $rsptxt));		//removed since using <xmp> to wrap output
		}

		$email = $input->post('email');
		App::render('home', [
			'name' => 'Form Submitted',
			'email' => $email
		]);
	}
}