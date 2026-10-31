<?php
/**
* @package		hosted-file-manager
* @copyright	Copyright (C) 2022-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		4.0.0
*/
namespace Rjc\Hfm\Models;

defined('EXEC_HFM') or die('Access denied');

use Rjc\Hfm\Core\Input;

class HomeModel
{
	protected $cfg;

	public function __construct ($cfg)
	{
		$this->cfg = $cfg;
	}

	public function index (Input $input, string $rDir, string $srtBy): array
	{
		$dFiles = false;
		$dtop = $this->cfg->val('fmx_dirs_at_top', false);
		$dirs = $fils = [];
		$path = $rDir;
		$srtBy = $input->get('O','n');
		$scn = @scandir($path, $srtBy == 'nd' ? SCANDIR_SORT_DESCENDING : 0);
		if ($scn !== false) {
			$ntrys = array_diff($scn, ['.','..']);
			$path .= '/';
			foreach ($ntrys as $ntry) {
				$nls = lstat($path.$ntry);
				$nls = array_slice($nls, 0, 10);
				if ($dtop && $nls[2] & 040000) {
					$dirs[] = [$ntry, $nls];
				} else {
					$fils[] = [$ntry, $nls];
				}
			}
			$dFiles = array_merge($dirs, $fils);
		}
		if ($dFiles === false) {
			$error = error_get_last();
			echo '<span style="color:red">Could not read directory: '.$rDir.'<br />Error: '.$error['message'].'</span>';	//Error("Could not read directory $rDir: $!");
		}
		return $dFiles;
	}

	public function submit (Input $input): void
	{
		$email = $input->post('email');
		App::render('home', [
			'name' => 'Form Submitted',
			'email' => $email
		]);
	}

	private function cmpFm ($a, $b): int
	{
		if ($a[1] && $b[1]) {
			$am = $a[1][9];
			$bm = $b[1][9];
			if ($am == $bm) { return 0; }
			return ($am < $bm) ? -1 : 1;
		}
		return 0;
	}
	private function cmpFmd ($a, $b): int
	{
		return $this->cmpFm ($b, $a);
	}
	private function cmpFs ($a, $b): int
	{
		if ($a[1] && $b[1]) {
			$as = $a[1][7];
			$bs = $b[1][7];
			if ($as == $bs) { return 0; }
			return ($as < $bs) ? -1 : 1;
		}
		return 0;
	}
	private function cmpFsd ($a, $b): int
	{
		return $this->cmpFs ($b, $a);
	}

}