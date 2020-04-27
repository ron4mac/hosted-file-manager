<?php
/* uplodr v0.8 */

class Up_Load
{
	protected $target_dir;
	protected $pvals;
	protected $file;
	protected $target_file;
	protected $filup_cb = null;
	// for chunking
	protected $ckid;
	protected $ckpath;
	protected $tmpath;

	public function __construct ($config=[])
	{
		// pull in configuration items
		foreach ($config as $k => $v) { $this->$k = $v; }

		// pull in the POSTed values
		$this->pvals = (object)$_POST;

		// process the incoming data
		try {
			if (empty($this->pvals->chunkact)) {
				$this->receiveFile();
			} else {
				$this->processChunk();
			}
		}
		catch (Exception $e) {
			header('HTTP/1.1 '.(400+$e->getCode()).' Failed to store file');
			echo 'Error storing file: ' . $e->getMessage();
		}
	}

	// check uploaded file data for issues
	private function vetUpload ($fec=true)
	{
		$file = $_FILES['Filedata'];
		if (!$file) throw new Exception('Parameters error', 9);
		switch ($file['error']) {
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
				throw new Exception('Unknown error. '.$file['error']);
		}
		$target_file = $this->target_dir . basename($file['name']);
		if ($fec && file_exists($target_file)) throw new Exception('File already exists');
		$this->file = $file;
		$this->target_file = $target_file;
	}

	// normal receipt of a single uploaded file
	private function receiveFile ()
	{
		$this->vetUpload();
		if (!move_uploaded_file($this->file['tmp_name'], $this->target_file)) throw new Exception('Could not place file');
		if ($this->filup_cb) call_user_func($this->filup_cb, basename($this->target_file));
	}

	// receive a file from multiple 'chunks'
	private function processChunk ()
	{
		$this->ckid = $this->pvals->ident;
		$this->tmpath = sys_get_temp_dir() . '/';
		$this->ckpath = $this->tmpath . $this->ckid;
		switch ($this->pvals->chunkact) {
			case 'pref':
				$target_file = $this->target_dir . basename($this->pvals->file);
				if (file_exists($target_file)) throw new Exception('File already exists');
				// create the temporary directory, if necessary
				if ($this->ckid && !is_dir($this->ckpath)) {
					mkdir($this->ckpath, 0777, true);
					$this->upldLog('created chunk dir: '.$this->ckpath);
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
	private function addChunk ()
	{
		$chnkn = $this->pvals->chnkn;
		$dest = $this->ckpath.'/part'.$chnkn;
		if (!move_uploaded_file($this->file['tmp_name'], $dest)) {
			$this->upldLog('failed to place chunk: '.$dest);
			die('Failed to place chunk #'.$chnkn);
		}
		$this->upldLog('placed chunk: '.$dest);

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
			if (($fp = @fopen($dest, 'w')) !== false) {
				for ($i=1; $i<=$totalchunks; $i++) {
					fwrite($fp, file_get_contents($this->ckpath.'/part'.$i));
				}
				fclose($fp);
			} else {
				$this->upldLog('failed to open destination file: '.$dest);
				die('failed to open destination file: '.$dest);
			}
	
			$this->upldLog('combined chunks: '.$dest);
			$this->cleanup();
			if ($this->filup_cb) call_user_func($this->filup_cb, basename($dest));
		}
	}

	// remove temporary storage that was used for chunks
	private function cleanup ()
	{
		if ($this->ckid) $this->rrmdir($this->ckpath);
		$this->upldLog('chunks cleared: '.$this->ckpath);
	}

	private function rrmdir ($dir) {
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != '.' && $object != '..') {
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
	
	private function upldLog ($ntry)
	{	return;
		file_put_contents('UPLOG.txt', $ntry."\n", FILE_APPEND);
	}

}
