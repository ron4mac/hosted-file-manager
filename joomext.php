<?php
define('DS', DIRECTORY_SEPARATOR);

class DirCheck
{
	protected $dirray = array();
	protected $pfx = '';

	public function __construct ($dirPath) {
		if ($dirPath[strlen($dirPath)-1] != DS)
			$dirPath .= DS;
		$this->pfx = $dirPath;
		$this->dirray = $this->getDirTree($dirPath);
	}

	public function remPath ($path) {
		$path = substr($path, strlen($this->pfx));
		$parts = explode(DS, $path);
		$cnt = count($parts);
		$segm =& $this->dirray;
		for ($i = 0; $i < $cnt; $i++) {
			if (in_array($parts[$i], array_keys($segm))) {
				$last =& $segm;
				$segm =& $segm[$parts[$i]];
			} else return;
		}
		unset($last[array_pop($parts)]);
	}

	public function dirDump ($htm=false) {
		//var_dump($this->pfx,$this->dirray);
		if (!$this->dirray) return;
		$this->traverse($this->dirray, $htm);
	}

	private function traverse ($node, $htm=false, $lvl=0) {
		$lc = $htm ? '$nbsp;$nbsp;' : '  ';
		$l = str_repeat($lc, $lvl);
		while ($val = current($node)) {
			echo $l . key($node) . ($htm ? '<br />' : "\n");
		//	if (is_array($val)) $this->traverse($val, $htm, $lvl+1);
			next($node);
		}
	}

	private function getDirTree ($dir) {
		$d = dir($dir);
		$x = array();
		while (false !== ($r = $d->read())) {
			if ($r != '.' && $r != '..') {
				if (is_dir($dir.$r)) {
					$x[$r] = $this->getDirTree($dir.$r.DS);
				} else {
					$x[$r] = 1;
				}
			}
		}
		$d->close();
		return $x;
	}

}

class Joomext
{
	protected $instXml;
	protected $instXmlRef = '';
	protected $jbase = '';
	protected $extdir = '';
	protected $extype = '';
	protected $libname = '';
	protected $group = '';
	protected $extver = '';
	protected $mfest = '';
	protected $foldPath = '';
	protected $fbase = '';
	protected $inAdmin = false;
	protected $sdchk;
	protected $asdchk;
	protected $log = '';

	public function __construct ($fref)
	{
		global $baseDir;
		$this->instXmlRef = $fref;
		$this->fbase = $baseDir.'tmp'.DS.'joomext';
		$this->instXml = simplexml_load_file($fref);
		$this->mfest = $this->instXml->getName();
	}

	public function pull ($curdir)
	{
		if (!in_array($this->mfest, array('extension','install'))) {
			echo 'Invalid manifest';
			return;
		}
		foreach($this->instXml->attributes() as $a => $b) {
			switch ($a) {
				case 'type':
					$this->extype = $b;
					break;
				case 'group':
					$this->group = $b;
					break;
				case 'version':
					$this->extver = $b;
					break;
				default:
					//echo $a,'="',$b,"\"\n";
					break;
			}
		}
		if ($this->extype && $this->extver) {
			$dirs = explode(DS, $curdir);
			array_pop($dirs);
			switch ($this->extype) {
				case 'component':
				case 'plugin':
					$jb = array_slice($dirs,0,-3);
					break;
				case 'module':
					$jb = array_slice($dirs,0,-2);
					break;
				case 'library':
					$jb = array_slice($dirs,0,-3);
					break;
				default:
					echo 'Can not yet extract this type ('.$this->extype.')';
					return;
			}
			$this->jbase = implode(DS,$jb);
			$this->extdir = array_pop($dirs);	//var_dump($this->resolvedSource());exit();
			$this->sdchk = new DirCheck($this->resolvedSource());
			if ($this->extype == 'component')
				$this->asdchk = new DirCheck($this->resolvedSource(true));
			echo $this->jbase . ' :: ' . $this->extdir ."\n";
			echo 'The base is ' . $this->fbase . "\n";
			if (file_exists($this->fbase)) $this->recursiveDelete($this->fbase);
			mkdir($this->fbase);
			$this->traverse($this->instXml);
			$this->log .= $this->instXmlRef.' -> '.$this->fbase."\n";
			system('cp -p "'.$this->instXmlRef.'" "'.$this->fbase.'"',$rslt);
			if ($rslt) $this->log .= $rslt."\n";
			if ($this->extype == 'component') $this->asdchk->remPath($this->instXmlRef);
			else $this->sdchk->remPath($this->instXmlRef);
			file_put_contents($this->fbase.DS.'log.txt',$this->log);
			if ($this->extype == 'component') $this->asdchk->dirDump();
			$this->sdchk->dirDump();
		} else {
			echo 'Unknown format';
		}
	}

	private function traverse ($elem)
	{
		foreach ($elem->children() as $kid) {
			$nam = $kid->getName();
			switch ($nam) {
				case 'files':
					$this->processFiles($kid);
					break;
				case 'administration':
					$this->processAdmin($kid);
					break;
				case 'scriptfile':
					$this->copyFile($kid, true);
					break;
				case 'languages':
					$this->processLangs($kid);
					break;
				case 'media':
					$this->processMedia($kid);
					break;
				case 'install':
					$this->doStall($kid);
					break;
				case 'uninstall':
					$this->doStall($kid, false);
					break;
				case 'libraryname':
					$this->libname = (string) $kid;	//var_dump($this->resolvedSource());exit();
					$this->sdchk = new DirCheck($this->resolvedSource());
					break;
				default:
					echo 'The name is ' . $nam . "\n";
					break;
			}
		}
	}

	private function processFiles ($elem)
	{
		foreach ($elem->attributes() as $a => $b) {
			switch ($a) {
				case 'folder':
					$this->foldPath = $b;
					break;
				default:
					echo $a,'="',$b,"\"\n";
					break;
			}
		}
		foreach ($elem->children() as $kid) {
			$nam = $kid->getName();
			switch ($nam) {
				case 'file':
				case 'filename':
					$this->copyFile($kid);
					break;
				case 'folder':
					$this->copyFolder($kid);
					break;
				default:
					echo 'The file is ' . $nam . "\n";
					break;
			}
		}
		$this->foldPath = '';
	}

	private function processLangs ($elem)
	{
		foreach($elem->attributes() as $a => $b) {
			switch ($a) {
				case 'folder':
					$this->foldPath = $b;
					break;
				default:
					echo $a,'="',$b,"\"\n";
					break;
			}
		}
		foreach ($elem->children() as $kid) {
			$nam = $kid->getName();
			switch ($nam) {
				case 'language':
					$this->copyLangFile($kid, false, true);
					break;
				case 'folder':
					$this->copyFolder($kid);
					break;
				default:
					echo 'The lang is ' . $nam . "\n";
					break;
			}
		}
		$this->foldPath = '';
	}

	private function processMedia ($elem)
	{
		global $baseDir;
		$media_base = $baseDir.$this->jbase.DS.'media'.DS;
		foreach ($elem->attributes() as $a => $b) {
			switch ($a) {
				case 'folder':
					$this->foldPath = $b;
					break;
				case 'destination':
					$media_base .= $b;
					break;
				default:
					echo $a,'="',$b,"\"\n";
					break;
			}
		}
		foreach ($elem->children() as $kid) {
			$nam = $kid->getName();
			switch ($nam) {
				case 'file':
				case 'filename':
					$this->copyFile($kid, false, false, $media_base);
					break;
				case 'folder':
					$this->copyFolder($kid, $media_base);
					break;
				default:
					echo 'The file is ' . $nam . "\n";
					break;
			}
		}
		$this->foldPath = '';
	}

	private function doStall ($elem, $in=true)
	{
		$this->traverse($elem);
	}

	private function processAdmin ($elem)
	{
		$this->inAdmin = true;
		$this->traverse($elem);
	}

	private function copyLangFile ($elem, $fradm=false, $islng=true)
	{
		$tag = '';
		foreach($elem->attributes() as $a => $b) {
			switch ($a) {
				case 'tag':
					$tag = DS.$b;
					break;
				default:
					echo $a,'="',$b,"\"\n";
					break;
			}
		}

		$dpath = $this->fbase;
		if ($this->foldPath) $dpath .= DS.$this->foldPath;
		// create any intermediate folders
		$dn = dirname($elem);
		if ($dn != '.') $dpath .= DS.$dn;
		if (!is_dir($dpath)) {
			$this->log .= 'mkdir: '.$dpath."\n";
			@mkdir($dpath,0777,true);
		}

		$fr = $this->resolvedLangSource($fradm).$tag.DS.basename($elem);
		$to = $dpath.DS;
		//echo $fr.' -> '.$to."\n";
		$this->log .= $fr.' -> '.$to."\n"; //return;
		system('cp -p "'.$fr.'" "'.$to.'"',$rslt);
		if ($rslt) $this->log .= $rslt."\n";	//echo $rslt;
		if ($this->inAdmin || $fradm) $this->asdchk->remPath($fr);
		else $this->sdchk->remPath($fr);
	}

	private function copyFile ($elem, $fradm=false, $islng=false, $fBase=false)
	{
		$dpath = $this->fbase;
		if ($this->foldPath) $dpath .= DS.$this->foldPath;
		// create any intermediate folders
		$dn = dirname($elem);
		if ($dn != '.') $dpath .= DS.$dn;
		if (!is_dir($dpath)) {
			$this->log .= 'mkdir: '.$dpath."\n";
			@mkdir($dpath,0777,true);
		}
		if ($islng) {
			$fr = ($fBase ? $fBase : $this->resolvedLangSource($fradm)) .DS.$elem;
		} else {
			$fr = ($fBase ? $fBase : $this->resolvedSource($fradm)) .DS.$elem;
		}
		$to = $dpath.DS;
		//echo $fr.' -> '.$to."\n";
		$this->log .= $fr.' -> '.$to."\n"; //return;
		system('cp -p "'.$fr.'" "'.$to.'"',$rslt);
		if ($rslt) $this->log .= $rslt."\n";	//echo $rslt;
		if ($this->inAdmin || $fradm) $this->asdchk->remPath($fr);
		else $this->sdchk->remPath($fr);
	}

	private function copyFolder ($elem, $fBase=false)
	{
		$dpath = $this->fbase;
		if ($this->foldPath) $dpath .= DS.$this->foldPath;
		$ddir = $dpath.DS.$elem;
		// create any intermediate folders
		$dn = dirname($ddir);
		if ($dn != '.' && !file_exists($dn)) {
			mkdir($dn,0777,true);
			$this->log .= 'mkdir: '.$dn."\n";
		}
		$fr = ($fBase ? $fBase : $this->resolvedSource()) .DS.$elem;
		$to = $ddir;
		//echo $fr.' -> '.$to."\n";
		$this->log .= $fr.' -> '.$to."\n"; //return;
//		system('cp -a "'.$fr.'" "'.$to.'"',$rslt);
		system('rsync -at --exclude=sv_* --exclude=~* "'.$fr.'/" "'.$to.'"',$rslt);
		if ($rslt) $this->log .= $rslt."\n";	//echo $rslt;
		if ($this->inAdmin) $this->asdchk->remPath($fr);
		else $this->sdchk->remPath($fr);
	}

	private function resolvedSource ($fradm=false)
	{
		global $baseDir;
		$spath = $baseDir.$this->jbase;
		$xpath = '';
		switch ($this->extype) {
			case 'component':
				$spath .= ($this->inAdmin || $fradm ? DS.'administrator' : '').DS.'components';
				break;
			case 'plugin':
				$spath .= DS.'plugins'.DS.$this->group;
				break;
			case 'module':
				$spath .= DS.'modules';
				break;
			case 'library':
				$xpath = DS.$this->libname;
				break;
		}
		return $spath.DS.$this->extdir.$xpath;
	}

	private function resolvedLangSource ($fradm=false)
	{
		global $baseDir;
		$spath = $baseDir.$this->jbase;
		switch ($this->extype) {
			case 'component':
				$spath .= ($this->inAdmin || $fradm ? DS.'administrator' : '');
				break;
			case 'plugin':
			case 'module':
			case 'library':
				$spath .= DS.'administrator';
				break;
		}
		return $spath.DS.'language';
	}

	private function recursiveDelete ($pstr)
	{
	//echo "$pstr\n";
	if (is_file($pstr)) { @unlink($pstr); }
	elseif (is_dir($pstr)) {
		$dh = opendir($pstr);
		while ($node = readdir($dh)) {
			if ($node != '.' && $node != '..') {
				$path = $pstr.DS.$node;
				$this->recursiveDelete($path);
				}
			}
		closedir($dh);
		@rmdir($pstr);
		}
	}

}
?>