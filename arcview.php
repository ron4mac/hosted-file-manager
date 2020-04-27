<?php
require_once 'functions.php';
include 'cfg.php';

$fref = $_GET['fref'];
$fpath = $baseDir . $fref;
if (!file_exists($fpath)) {
	exit('FILE DOES NOT EXIST: '.$fref);
}
$fInfo = FileMimeType($fpath);
switch ($fInfo) {
	case 'application/zip':
		$arcvue = new MyZipView($fpath);
		break;
	case 'application/x-tar':
	case 'application/x-gzip':
		$arcvue = new MyTgzView($fpath);
		break;
	default:
		exit('NOT AN ACCEPTABLE ARCHIVE TYPE: '.$fInfo);
}


if (!empty($_GET['act'])) {
	$idx = $_GET['idx'];
	$pth = $_GET['pth'];
	switch ($_GET['act']) {
		case 'dnld':
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename='.basename($pth));
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . $arcvue->getFileSize($idx, $pth));
			break;
		case 'img';
			$fext = pathinfo($pth, PATHINFO_EXTENSION); if ($fext=='svg') $fext = 'svg+xml';
			header("Content-Type: image/{$fext}");
			header('Content-Length: ' . $arcvue->getFileSize($idx, $pth));
			break;
		default:
			die('HUH?');
	}
	@ob_clean();
	@flush();
	echo $arcvue->getFileContent($idx, $pth);
	exit();
}


function display_entry ($av)
{
	global $fref, $aceBase, $acetheme;

	$modes = ['js'=>'javascript','pl'=>'perl','cgi'=>'perl'];
	$idx = $_POST['idx'];
	$pth = $_POST['pth'];
	$fext = pathinfo($pth, PATHINFO_EXTENSION);

	$dsp = new MyEntryView(basename($pth));
	$dsp->addScript('
	function dnld_entry (e) {
		e.preventDefault();
		var dlURL = "arcview.php?fref='.$fref.'&act=dnld&idx='.$idx.'&pth='.$pth.'";
		var dlframe = document.createElement("iframe");
		dlframe.src = dlURL;
		dlframe.style.display = "none";
		document.body.appendChild(dlframe);
	}');

	$body = '
	<div class="navbar">
		<a href="#" onclick="window.history.back();">&lt;&lt; Back</a>
		<a href="#" onclick="dnld_entry(event)">Download</a>
	</div>';

	if (in_array($fext, ['gif','jpg','jpeg','png','svg'])) {
		$imgsrc = $_SERVER['HTTP_REFERER'].'&act=img&idx='.$idx;
		$body .= '<div style="margin-top:31px"><image src="'.$imgsrc.'" /></div>';
		$dsp->display($body);
		exit();
	}

	$mode = empty($modes[$fext]) ? $fext : $modes[$fext];
	$dsp->addScript('<script src="'.$aceBase.'ace.js" type="text/javascript"></script>', false);
	$dsp->addStyle('#editor{margin:0;position:absolute;top:30px;bottom:0;left:0;right:0;}');
	$body .= '<pre id="editor">'.htmlspecialchars($av->getFileContent($idx, $pth)).'</pre>';
	$botscr = '<script>
	var editor = ace.edit("editor");
	editor.setTheme("ace/theme/'.$acetheme.'");
	editor.session.setMode("ace/mode/'.$mode.'");
	editor.setReadOnly(true);
	editor.setShowPrintMargin(false);
	</script>';

	$dsp->display($body, '', $botscr);
}

if (!empty($_POST['act'])) {
	display_entry($arcvue);
	exit();
}



function display_archive ($av)
{
	global $fref, $fpath, $jqlink;

	$dsp = new MyArchiveView(basename($fpath));

	$addScr = '
	function dnld_entry (e) {
		e.preventDefault();
		var slctd = $(".slctd")[0];
		var idx = slctd ? slctd.getAttribute("data-idx") : null;
		if (idx === null) { alert("Please select a file first."); return; }
		var pth = encodeURI($(".slctd div:nth-child(3)").html());
		var dlURL = "arcview.php?fref='.$fref.'&act=dnld&idx=" + idx + "&pth=" + pth;
		var dlframe = document.createElement("iframe");
		dlframe.src = dlURL;
		dlframe.style.display = "none";
		document.body.appendChild(dlframe);
	}
	function view_entry (e) {
		e.preventDefault();
		var slctd = $(".slctd")[0];
		var idx = slctd ? slctd.getAttribute("data-idx") : null;
		if (idx === null) { alert("Please select a file first."); return; }
		$("#act").val("vue");
		$("#idx").val(idx);
		$("#pth").val($(".slctd div:nth-child(3)").html());
		document.getElementById("actfrm").submit();
	}
	function iSelect (elm) {
		$(".rTableRow").each(function(){ $(this).removeClass("slctd") });
		$(elm).addClass("slctd");
	}
	$(function() {
		$(".rTableRow").click(function(){ iSelect(this) });
	});';
	$dsp->addScript($addScr);

	$body = '
	<div class="navbar">
		<a href="#" onclick="dnld_entry(event)">Extract</a>
		<a href="#" onclick="view_entry(event)">View</a>
	</div>
	<form action="" id="actfrm" method="post">
		<input type="hidden" id="act" name="act" value="" />
		<input type="hidden" id="idx" name="idx" value="" />
		<input type="hidden" id="pth" name="pth" value="" />
	</form>';

	$body .= '<div class="rTable main">';
	ob_start();
	$av->list_contents();
	$body .= ob_get_contents();
	ob_end_clean();
	$body .= '</div>';
	
	$scrt = '<script src="'.$jqlink.'"></script>';
	$dsp->display($body, $scrt);
}

display_archive($arcvue);



abstract class MyArcView
{
	protected $fpath;
	protected $nss = 0;

	public function __construct ($fpath)
	{
		$this->fpath = $fpath;
	}

	public function action ($act='list')
	{
	}

	protected function list_row ($ndx, $size, $name, $isdir=false)
	{
		$dnam = substr($name, $this->nss);
		if ($isdir) {
			echo '<div class="rTableRow">';
			echo '<div class="rTableCell zdir numr">'.$ndx.'</div><div class="rTableCell zdir"></div>';
			echo '<div class="rTableCell zdir">'.$dnam.'</div>';
		} else {
			echo '<div class="rTableRow" data-idx="'.$ndx.'">';
			echo '<div class="rTableCell numr">'.$ndx;
			echo '</div><div class="rTableCell numr">'.int2kmg($size).'</div>';
			echo '<div class="rTableCell fpath">'.$dnam.'</div>';
		}
		echo '</div>'."\n";
	}
}

class MyZipView extends MyArcView
{
	protected $za;

	public function list_contents ()
	{
		$zip = zip_open($this->fpath);
		if (!is_resource($zip)) die('ERROR OPENING ZIP FILE: '.$this->fpath.'<br>ERROR: '.$zip);
		$ndx = 0;
		while (($ntry = zip_read($zip)) && ($ntry !== false)) {
			if (is_resource($ntry)) {
				$nam = zip_entry_name($ntry);
				if (substr($nam, -1) == '/') {
					$this->list_row($ndx, 0, $nam, true);
				} else {
					$this->list_row($ndx, zip_entry_filesize($ntry), $nam);
				}
			} else {
				echo '<div class="rTableRow"><div class="rTableCell">Error: '.$ndx.' : '.$ntry.'</div>';
			}
			$ndx++;
		}
	}

	public function getFileSize ($idx, $pth)
	{
		$this->openArchive($idx, $pth);
		return $this->za->statIndex($idx)['size'];
	}

	public function getFileContent ($idx, $pth)
	{
		$this->openArchive($idx, $pth);
		return $this->za->getFromIndex($idx);
	}

	private function openArchive ($idx, $pth)
	{
		if (empty($this->za)) {
			$this->za = new ZipArchive();
			$this->za->open($this->fpath);
		}
	}

}


class MyTgzView extends MyArcView
{
	protected $pfi;

	public function list_contents ()
	{
		$phr = new PharData($this->fpath);
		if (!$phr) die('ERROR OPENING ARCHIVE: '.$this->fpath.'<br>ERROR: '.$phr);
		$this->nss = strlen(dirname($phr->getPathname()))+1;
		$ndx = 0;
		$this->doDir($phr, $ndx);
	}

	public function getFileSize ($idx, $pth)
	{
		$this->get_pfi($pth);
		return $this->pfi->getCompressedSize();
	}

	public function getFileContent ($idx, $pth)
	{
		$this->get_pfi($pth);
		return $this->pfi->getContent();
	}

	private function get_pfi ($pth)
	{
		if (empty($this->pfi)) {
			$phr = new PharData($this->fpath);
			$this->pfi = $phr->offsetGet($pth);
		}
	}

	private function doDir ($dir, &$ndx)
	{
		foreach ($dir as $child) {
			$nam = $child->getPathname();
			if ($child->isDir()) {
				$this->list_row($ndx, 0, $nam, true);
			} else {
				$this->list_row($ndx, $child->getCompressedSize(), $nam);
			}
			$ndx++;
			if ($child->isDir()) {
				$this->doDir(new PharData($child), $ndx);
			}
		}
	}

}


////////// PAGE DISPLAY CLASSES //////////

abstract class MyDisplay
{
	protected $title = '';
	protected $style = '';
	protected $script = '';
	protected $styles = [];
	protected $scripts = [];

	public function __construct ($t)
	{
		$this->title = $t;
		$this->style = '
	body {margin: 0;}
	.navbar {
		overflow: hidden;
		background-color: #333;
		position: fixed;
		top: 0;
		width: 100%;
		height: 30px;
	}
	.navbar a {
		float: left;
		display: block;
		color: #f2f2f2;
		text-align: center;
		padding: 4px 16px;
		text-decoration: none;
		font-size: 17px;
		margin-top: 1px;
	}
	.navbar a:hover {
		background: #ddd;
		color: black;
	}';
	}

	public function addScript ($scr, $inl=true)
	{
		if ($inl) {
			$this->script .= $scr;
		} else {
			$this->scripts[] = $scr;
		}
	}

	public function addStyle ($sty, $inl=true)
	{
		if ($inl) {
			$this->style .= $sty;
		} else {
			$this->styles[] = $sty;
		}
	}

	public function display ($body, $xscrt='', $xscrb='')
	{
		echo '<!DOCTYPE html><html><head>'."\n";
		echo '<title>'.htmlspecialchars($this->title).'</title>';
		echo "\n".'<style>'.$this->style.'</style>';
		if ($this->scripts) {
			foreach ($this->scripts as $scr) {
				echo "\n".$scr;
			}
		}
		if ($xscrt) echo "\n".$xscrt;
		if ($this->script) echo "\n".'<script>'.$this->script.'</script>';
		echo "\n".'</head><body>'.$body;
		if ($xscrb) echo "\n".$xscrb;
		echo "\n".'</body></html>';
	}
}


class MyArchiveView extends MyDisplay
{
	public function __construct ($t)
	{
		parent::__construct($t);
		$this->style .= '
	.main { padding: 16px; margin-top: 31px; }
	.fpath { width:100%; }
	.rTable { display: table; border-collapse: collapse; }
	.rTableRow { display: table-row; }
	.rTableHeading { display: table-header-group; }
	.rTableBody { display: table-row-group; }
	.rTableFoot { display: table-footer-group; }
	.rTableCell, .rTableHead { display: table-cell; padding: 3px 10px; border: 1px solid #999; }
	.numr { text-align: right; }
	.zdir { background-color: #CCC; }
	.slctd { background-color: #E0E0FF; }';
	}
}


class MyEntryView extends MyDisplay
{
	public function __construct ($t)
	{
		parent::__construct($t);
	}
}
