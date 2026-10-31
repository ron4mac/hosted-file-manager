<?php
/**
* @package		hosted-file-manager
* @copyright	Copyright (C) 2022-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		4.0.0
*/
namespace Rjc\Hfm\Models;

defined('EXEC_HFM') or die('Access denied');
error_reporting(-1);

use Rjc\Hfm\Core\Input;

class UploadModel
{
	protected Input $input;

	public function __construct(Input $input)
	{
		$this->input = $input;
	}

	public function process (): void
	{
		$fpath = $this->input->path('fpath');

		if (!$this->input->fileHas('user_file')) {
			// process html5/js uploads iincluding chunked
//			require_once 'upload.php';
			$upld = new Up_Load([
				'target_dir' => BASE_DIR.$fpath,
				'pvals' => (object)$this->input->post(),
				'file' => $this->input->file('Filedata')
			]);
		} else {
			// process standard/legacy uploads
			echo '<style>
			body {background-color:#E7FFFF;}
			h4 {text-align:center;padding:6px;border:1px solid #999;margin:2px 0;background-color:#FFD;}
			.success {color:#292;}
			.failure {color:#F00;}
		</style>';
			$errcnt = 0;
			foreach ($this->input->file('user_file')['error'] as $key => $error) {
				if ($error == UPLOAD_ERR_OK) {
					$tmp_name = $this->input->file('user_file')['tmp_name'][$key];
					if (is_uploaded_file($tmp_name)) {
						$name = $this->input->file('user_file')['name'][$key];
						move_uploaded_file($tmp_name, $baseDir.$fpath.$name);
					} else echo '<p class="failure">Error: failed to upload</p>';
				} elseif ($error !== 4 || $key == 0) {
					echo'<p class="failure">File: '.htmlspecialchars($this->input->file('user_file')['name'][$key]).'<br>Error: '.$upld_err_txt[$error].'</p>';
					$errcnt++;
				}
			}

		//	echo '<h4 class="failure">=== PSEUDO PROCESSED ===</h4>';
			echo '<h4 class="success">Upload Complete</h4>';
			if (is_null($this->input->file('w', null))) {
				echo '<script>window.parent.refreshFilst();</script>';
			} else {
				echo '<script>parent.opener.refreshFilst();</script>';
			}
		}
	}

}

/* uplodr v0.8 */

class Up_Load
{
	protected $target_dir;
	protected \stdClass $pvals;
	protected $file;
	protected $target_file;
	protected $as_file = '';
	protected $filup_cb;
	// for chunking
	protected $ckid;
	protected $ckpath;
	protected $tmpath;

	public function __construct ($config=[])
	{
		// pull in configuration items
		foreach ($config as $k => $v) { $this->$k = $v; }

		$this->upldLog();

		// process the incoming data
		try {
			if (empty($this->pvals->chunkact)) {
				$this->receiveFile();
			} else {
				$this->processChunk();
			}
			if ($this->as_file) {
			//	header('HTTP/1.1 100 Continue');
				echo 'Stored as: ' . $this->as_file;
			}
		}
		catch (\Throwable $e) {
			header('HTTP/1.1 '.(400+$e->getCode()).' Failed to store file');
			echo 'Error storing file: ' . $e->getMessage();
		}
	}

	// check uploaded file data for issues
	private function vetUpload (bool $fec=true): void
	{
		if (!$this->file) throw new Exception('Parameters error', 9);
		switch ($this->file['error']) {
			case UPLOAD_ERR_OK:
				break;
			case UPLOAD_ERR_NO_FILE:
				throw new Exception('No file sent.', UPLOAD_ERR_NO_FILE);
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				throw new Exception('Exceeded filesize limit.', UPLOAD_ERR_INI_SIZE);
			case UPLOAD_ERR_PARTIAL:
				throw new Exception('Only partial file uploaded.', UPLOAD_ERR_PARTIAL);
			case UPLOAD_ERR_NO_TMP_DIR:
				throw new Exception('Missing temporary folder.', UPLOAD_ERR_NO_TMP_DIR);
			case UPLOAD_ERR_CANT_WRITE:
				throw new Exception('Failed to write file.', UPLOAD_ERR_CANT_WRITE);
			default:
				throw new Exception('Unknown error. '.$this->file['error']);
		}
		$target_file = $this->target_dir . basename($this->file['name']);
		if ($fec && file_exists($target_file)) throw new Exception('File already exists');
		$this->target_file = $target_file;
	}

	// normal receipt of a single uploaded file
	private function receiveFile (): void
	{
		$this->vetUpload($this->pvals->faex ? ($this->pvals->faex=='f') : true);
		if ($this->pvals->faex=='r') {
			$this->target_file = $this->uniqueFn($this->target_file);
		}
		if (!move_uploaded_file($this->file['tmp_name'], $this->target_file)) throw new Exception('Could not place file');
		if ($this->filup_cb) call_user_func($this->filup_cb, basename($this->target_file));
	}

	// receive a file from multiple 'chunks'
	private function processChunk (): void
	{
		$this->ckid = $this->pvals->ident;
		$this->tmpath = sys_get_temp_dir() . '/';
		$this->ckpath = $this->tmpath . $this->ckid;
		switch ($this->pvals->chunkact) {
			case 'pref':
				$target_file = $this->target_dir . basename($this->pvals->file);
				$faexf = $this->pvals->faex ? ($this->pvals->faex=='f') : true;
				if ($faexf && file_exists($target_file)) throw new Exception('File already exists');
				// create the temporary directory, if necessary
				if ($this->ckid && !is_dir($this->ckpath)) {
					mkdir($this->ckpath, 0777, true);
					$this->upldLog();
				}
				break;
			case 'chnk':
				$this->vetUpload(false);
				$this->addChunk();
				break;
			case 'abrt':
				$this->cleanup();
				break;
			default:
				echo '?-?-?';
		}
	}

	// receive, process and place a 'chunk' of file data
	private function addChunk (): void
	{
		$chnkn = $this->pvals->chnkn;
		$dest = $this->ckpath.'/part'.$chnkn;
		if (!move_uploaded_file($this->file['tmp_name'], $dest)) {
			$this->upldLog();
			die('Failed to place chunk #'.$chnkn);
		}
		$this->upldLog();

		$totalchunks = (int)$this->pvals->tchnk;
		if ($chnkn == $totalchunks) {

			// count all the parts of this file
			$total_files = 0;
			foreach(scandir($this->ckpath) as $filepart) {
				if (strpos($filepart,'part') === 0) {
					$total_files++;
				}
			}

			if ($total_files !== $totalchunks) die('Missing some file chunk(s)');

			// create the final destination file
			$dest = $this->target_dir . $this->pvals->fname;
			if ($this->pvals->faex=='r') {
				$dest = $this->uniqueFn($dest);
			}
			if (($fp = @fopen($dest, 'w')) !== false) {
				for ($i=1; $i<=$totalchunks; $i++) {
					fwrite($fp, file_get_contents($this->ckpath.'/part'.$i));
				}
				fclose($fp);
			} else {
				$this->upldLog();
				die('failed to open destination file: '.$dest);
			}
	
			$this->upldLog();
			$this->cleanup();
			if ($this->filup_cb) call_user_func($this->filup_cb, basename($dest));
		}
	}

	// create a unique file name
	private function uniqueFn ($fpath)
	{
		if (!file_exists($fpath)) return $fpath;
		$path_parts = pathinfo($fpath);
		$pfmx = $path_parts['dirname'].'/'.$path_parts['filename'];		//file path minus extension
		$pfxp = isset($path_parts['extension'])?('.'.$path_parts['extension']):'';		//file path estension part
		$x = 0;
		do {
			$unique = $pfmx.'_'.++$x.$pfxp;
		} while (file_exists($unique));
		$this->as_file = basename($unique);
		return $unique;
	}

	// remove temporary storage that was used for chunks
	private function cleanup (): void
	{
		if ($this->ckid) $this->rrmdir($this->ckpath);
		$this->upldLog();
	}

	private function rrmdir ($dir): void {
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object !== '.' && $object !== '..') {
					if (filetype($dir . '/' . $object) == 'dir') {
						$this->rrmdir($dir . '/' . $object); 
					} else {
						unlink($dir . '/' . $object);
					}
				}
			}
			reset($objects);
			rmdir($dir);
		}
	}
	
	private function upldLog()
	{
	}

}
