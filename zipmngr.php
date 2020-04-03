<?php
require_once 'functions.php';
include 'cfg.php';

$fref = $_GET['fref'];
$fpath = $baseDir . $fref;
if (!file_exists($fpath)) {
	echo 'FILE DOES NOT EXIST: '.$fpath;
	exit();
}
$fInfo = FileMimeType($fpath);
if ($fInfo != 'application/zip') {
	echo 'FILE MUST BE A ZIP FILE: '.$fpath;
	exit();
}

if (!empty($_GET['act']) && $_GET['act']=='dnld') {
	if (!file_exists($fpath)) exit(-1);
	$idx = $_GET['idx'];
	$za = new ZipArchive();
	$za->open($fpath);
	$fName = $za->getNameIndex($idx);
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename='.basename($fName));
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: ' . $za->statIndex($idx)['size']);
	@ob_clean();
	@flush();
	echo $za->getFromIndex($idx);
	$za->close();
	exit;
}


function display_entry ()
{
	global $fref, $fpath, $aceBase, $acetheme;

	$modes = ['js'=>'javascript'];
	$za = new ZipArchive();
	$za->open($fpath);
	$idx = $_POST['idx'];
	$fnam = $za->getNameIndex($idx);

	$dsp = new EntryView(basename($fnam));
	$dsp->addScript('
	function dnld_entry (e) {
		e.preventDefault();
		var dlURL = "zipmngr.php?fref='.$fref.'&act=dnld&idx=" + '.$idx.';
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

	$fext = pathinfo($fnam, PATHINFO_EXTENSION);
	$mode = empty($modes[$fext]) ? $fext : $modes[$fext];
	$dsp->addScript('<script src="'.$aceBase.'ace.js" type="text/javascript"></script>', false);
	$dsp->addStyle('#editor{margin:0;position:absolute;top:30px;bottom:0;left:0;right:0;}');
	$body .= '<pre id="editor">'.htmlspecialchars($za->getFromIndex($_POST['idx'])).'</pre>';
	$za->close();
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
	display_entry();
	exit();
}


function display_archive ()
{
	global $fref, $fpath, $jqlink;

	$dsp = new ArchiveView(basename($fpath));

	$addScr = '
	function dnld_entry (e) {
		e.preventDefault();
		var slctd = $(".slctd")[0];
		var idx = slctd.getAttribute("data-idx");
		if (idx === null) { alert("Please select a file first."); return; }
		var dlURL = "zipmngr.php?fref='.$fref.'&act=dnld&idx=" + idx;
		var dlframe = document.createElement("iframe");
		dlframe.src = dlURL;
		dlframe.style.display = "none";
		document.body.appendChild(dlframe);
	}
	function view_entry (e) {
		e.preventDefault();
		var slctd = $(".slctd")[0];
		var idx = slctd.getAttribute("data-idx");
		if (idx === null) { alert("Please select a file first."); return; }
		$("#act").val("vue");
		$("#idx").val(idx);
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
	</form>';

	$zip = zip_open($fpath);
	if (!is_resource($zip)) {
		echo 'EROR OPENING ZIP FILE: '.$fpath;
		echo '<br>ERROR: '.$zip;
		exit();
	}
	$ndx = 0;
	$body .= '<div class="rTable main">';
	while (($ntry = zip_read($zip)) && ($ntry !== false)) {
		if (is_resource($ntry)) {
			$nam = zip_entry_name($ntry);
			if (substr($nam, -1) == '/') {
				$body .= '<div class="rTableRow">';
				$body .= '<div class="rTableCell zdir numr">'.$ndx.'</div><div class="rTableCell zdir"></div><div class="rTableCell zdir">'.$nam.'</div>';
			} else {
				$body .= '<div class="rTableRow" data-idx="'.$ndx.'">';
				$body .= '<div class="rTableCell numr">'.$ndx;
				$sz = int2kmg(zip_entry_filesize($ntry));
				$body .= '</div><div class="rTableCell numr">'.$sz.'</div>';
	    		$body .= '<div class="rTableCell fpath">'.$nam.'</div>';
			}
		} else {
			$body .= '<div class="rTableRow"><div class="rTableCell">Error: '.$ndx.' : '.$ntry.'</div>';
		}
		$body .= '</div>'."\n";
		$ndx++;
	}
	$body .= '</div>';
	
	zip_close($zip);

	$scrt = '<script src="'.$jqlink.'"></script>';
	$dsp->display($body, $scrt);
}

display_archive();


abstract class Display
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


class ArchiveView extends Display
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


class EntryView extends Display
{
	public function __construct ($t)
	{
		parent::__construct($t);
	}
}