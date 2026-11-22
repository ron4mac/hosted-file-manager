<?php

class Tool
{
	protected $langfiles;

	public function __construct ()
	{
		$this->langfiles = $this->splLangFiles();
		if (empty($this->langfiles)) die('- NO JOOMLA LANGUAGE FILES DETECTED -');
	//	var_dump($this->langfiles);
	}

	public function action (): void
	{
		$lts = [];
		foreach ($this->langfiles as $fn) {
			$lines = file($fn, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			if ($lines !== false) {
				foreach ($lines as $line) {
					if (str_starts_with($line, 'COM_')) {
						[$lc,] = explode('=', $line);
						$lts[] = $lc;
					}
				}
			}
		}
		$miscnt = 0;
		foreach ($lts as $st) {
			$out = [];
			$sll = exec('grep -R --include="*.xml" --include="*.php" "'.$st.'"', $out);
			if (empty(trim($sll)) && empty($out)) {
				echo "$st <br>";
				++$miscnt;
			}
		}
		if (!$miscnt) echo 'LANG IS CORELATED &#9734;';
	}

	private function splLangFiles ()
	{
		$directory = new RecursiveDirectoryIterator('.', FilesystemIterator::SKIP_DOTS);
		$iterator = new RecursiveIteratorIterator($directory);
		$regex = new RegexIterator($iterator, '#^.+/language/en-GB/[^/]+\.ini$#', RecursiveRegexIterator::GET_MATCH);
		$matchedFiles = [];
		foreach ($regex as $file) {
			$matchedFiles[] = $file[0];
		}
		return $matchedFiles;
	}

}
