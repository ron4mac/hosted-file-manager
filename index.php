<?php
include 'fmx.ini';
include 'cfg.php';

function cmpFm ($a, $b)
{
	if ($a[1] && $b[1]) {
		$am = $a[1][9];
		$bm = $b[1][9];
		if ($am == $bm) { return 0; }
		return ($am < $bm) ? -1 : 1;
	}
	return 0;
}
function cmpFmd ($a, $b)
{
	return cmpFm ($b, $a);
}

function cmpFs ($a, $b)
{
	if ($a[1] && $b[1]) {
		$as = $a[1][7];
		$bs = $b[1][7];
		if ($as == $bs) { return 0; }
		return ($as < $bs) ? -1 : 1;
	}
	return 0;
}
function cmpFsd ($a, $b)
{
	return cmpFs ($b, $a);
}

if ($fmxInJoomla) {
	defined('_JEXEC') or die('Restricted access');
	JHtml::stylesheet('components/com_fmx/fmx/css/css.php');
	JFactory::getDocument()->addScript('components/com_fmx/fmx/js/js.php');
	$userN = $this->user->id;
	$cooknam = 'jfil_vew' . ($userN ? "_$userN" : '');
	$basDir = isset($_COOKIE[$cooknam]) ? convert_uudecode($_COOKIE[$cooknam]) : false;
	if (!$basDir) {
		$basDir = JPATH_ROOT.'/'. $this->params->get('fmx_base');
		$rootcook = setcookie($cooknam,convert_uuencode($basDir), 0, '/'.basename(JPATH_ROOT));
	}
	$pDir = JFactory::getApplication()->input->getPath('dir','');
	$appB = 'components/com_fmx/fmx/';
	$popW = isset($fmx_upload_winpop)?'true':'false';
	$fmxAppPath = JUri::base().'components/com_fmx/fmx/';
	$fmx_AJ = basename($_SERVER['PHP_SELF']) . '?format=raw';
	$scr = "var fmx_appPath = '{$fmxAppPath}';
var fmx_juid={$userN};
var fmx_AJ='{$fmx_AJ}';
var curDir='{$pDir}/';
var upload_winpop = {$popW};
";
	JFactory::getDocument()->addScriptDeclaration($scr);
} else {
	$rmtuser = getenv('REMOTE_USER');
	$cooknam = 'fil_vew' . ($rmtuser ? "_$rmtuser" : '');

	$basDir = '';
	$pDir = '';
	$rDir = '';
	$rootD = '';
	$rootcook = '';
	$appB = '';

	// establish (or get) a base directory path
	if (isset($_GET['bgn'])) {
		$basDir = $_GET['bgn'];
		$rootcook = setcookie($cooknam,convert_uuencode($basDir));
	} else {
		$basDir = isset($_COOKIE[$cooknam]) ? convert_uudecode($_COOKIE[$cooknam]) : false;
		if (!$basDir) {
			$basDir = trim(`cd ~;pwd`);	$basDir = dirname($_SERVER['DOCUMENT_ROOT']);
			$rootcook = setcookie($cooknam,convert_uuencode($basDir));
			}
	}

	// get any current directory path
	if (isset($_GET['dir'])) {
		$pDir = urldecode($_GET['dir']);
	}
}

// full path to current directory
$rDir = $basDir . ($pDir ? "/$pDir" : '');
// current directory name
$rootD = basename($basDir);

// get count of trash files
$trshs = @scandir($basDir.'/tmp/Trash');
$trshc = ($trshs && count($trshs)>2) ? ('('.(count($trshs)-2).')') : '';

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
?>
<?php if (!$fmxInJoomla): ?>
<?php header('Cache-Control: no-cache'); ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US" xml:lang="en-US">
<head>
<title>Files :: <?php echo $rootD?></title>
<meta charset="UTF-8">
<meta name="google" content="notranslate">
<link rel="stylesheet" href="<?php echo $fontawsm; ?>" />
<link rel="stylesheet" type="text/css" href="css/css.php" />
<script src="<?=$jqlink?>"></script>
<script src="js/js.php" type="text/javascript"></script>
<script type="text/javascript">
if (!window.fetch) alert("This web browser is not new enough to properly run this version of the File Manager");
var fmx_docroot = '<?php echo dirname($_SERVER['DOCUMENT_ROOT']); ?>';
var fmx_appPath = '';
var fmx_AJ='fmxjx.php';
var curDir='<?php echo $pDir; ?>/';
var ctxPrf='<?php echo isset($_SERVER['CONTEXT_PREFIX']) ? $_SERVER['CONTEXT_PREFIX'] : ''; ?>';
var upload_winpop = <?php echo isset($fmx_upload_winpop)?'true':'false' ?>;
</script>
</head>
<body class="fmgt">
<?php endif; ?>
<span class="pathbread">
<?php
if ($pDir) {
	print '<a href="'.$_SERVER['PHP_SELF']/*index.php*/.'">'.$rootD.'</a>';
	$href = 'index.php?dir=';
	$path = explode('/', $pDir);
	$curD = array_pop($path);
	foreach ($path as $fold) {
		$href .= $fold;
		print '/<a href="'.$href.'">'.$fold.'</a>';
		$href .= '/';
	}
	print "/$curD";
	$aRef = $_SERVER['PHP_SELF'];	//'index.php';
	if (dirname($pDir)!='.') {
		$aRef .= '?dir=' . urlencode(dirname($pDir));
	}
	$parntBut = '<a href="'.$aRef.'" title="Up to parent"><i class="fa fa-chevron-up" style="color:blue;font-size:large"></i></a>';
} else {
	print $rootD;
	$parntBut = '&nbsp;';
}
$srtBy = $_GET['O'] ?? 'n';
?>
</span>
<hr style="margin:6px 0" />
<div id="fmnu">
	<nav>
	<ul>
		<li>
			<a href="#" class="altm archive" data-mnu="mnu">archive</a>
			<ul class="fallback altm">
				<li class="mnur"><a href="#" data-mnu="zip" data-req="2">zip</a></li>
				<li class="mnua"><a href="#" data-mnu="zipx" data-req="2">zip-x</a></li>
				<li><a href="#" data-mnu="uzip" data-req="2">unzip</a></li>
				<li><a href="#" data-mnu="tarz" data-req="2">tar/gz</a></li>
				<li><a href="#" data-mnu="utrz" data-req="2">untar/gz</a></li>
				<li class="mnusep"></li>
				<li><a href="#" data-mnu="mgzp" data-req="2">View</a></li>
			</ul>
		</li>
		<li><a href="#" id="cppaMenu" data-mnu="cppa" data-req="2" class="cppaMenu">copy/paste</a></li>
		<!-- <li><a href="#" data-mnu="delf" data-req="2">delete</a></li> -->
		<li><a href="#" data-mnu="trsh" data-req="2" class="delfMenu">delete<?=$trshc?></a></li>
		<li><a href="#" data-mnu="dnld" data-req="2">download</a><div class="dnldprg"> rr</div></li>
		<li><a href="#" data-mnu="dupl" data-req="2">duplicate</a></li>
		<li><a href="#" id="markMenu" data-mnu="mark" data-req="2" class="markMenu">mark</a></li>
		<li><a href="#" data-mnu="mdya" data-req="0">media</a></li>
		<li><a href="#" id="mvtoMenu" data-mnu="mvto" data-req="2" class="mvtoMenu">move/to</a></li>
		<li>
			<a href="#" data-mnu="mnu">new</a>
			<ul class="fallback">
				<li><a href="#" data-mnu="nfle" data-req="0">file</a></li>
				<li><a href="#" data-mnu="nfld" data-req="0">folder</a></li>
			</ul>
		</li>
		<li><a href="#" data-mnu="refr" data-req="0">refresh</a></li>
		<li><a href="#" data-mnu="rnam" data-req="1">rename</a></li>
		<li>
			<a href="#" data-mnu="mnu">search</a>
			<ul class="fallback">
				<li><a href="#" data-mnu="srhf" data-req="0">file</a></li>
				<li><a href="#" data-mnu="srhc" data-req="0">content</a></li>
			</ul>
		</li>
		<li>
			<a href="#" data-mnu="mnu">transfer</a>
			<ul class="fallback">
				<li><a href="#" data-mnu="furl" data-req="0">from URL</a></li>
				<li><a href="#" data-mnu="turl" data-req="0">to URL</a></li>
			</ul>
		</li>
		<li><a href="#" data-mnu="upld" data-req="0" class="upldMenu">upload</a></li>
		<li><a href="#" data-mnu="webv" data-req="0">webview</a></li>
		<li>&nbsp;&nbsp;&nbsp;||&nbsp;</li>
		<li>
			<a href="#" data-mnu="mnu">develop</a>
			<ul class="fallback">
				<li><a href="#" data-mnu="gitr" data-req="0">gitter</a></li>
				<li><a href="#" data-mnu="jxtr" data-req="1">jextract</a></li>
				<li><a href="#" data-mnu="mmiz" data-req="0">minify(.js)</a></li>
				<li><a href="#" data-mnu="sql3" data-req="0">sqlite3</a></li>
			</ul>
		</li>
		<li><a href="#" data-mnu="fmxi" data-req="0">?</a></li>
	</ul>
	</nav>
</div>
<hr style="margin:6px 0;clear:both" />
<form name="filst" id="filsform">
	<input type="hidden" name="dir" value="<?php echo $pDir;?>" />
	<table id="ftbl">
		<thead>
		<tr>
		<td><input type="checkbox" id="checkAll" onchange="allSelect(event,this)" /></td>
		<td><?=$parntBut?></td>
<?php
	$mlab = '<a href="#" onclick="refreshFilstO(\'m\')">Last Modified</a>';
	$nlab = '<a href="#" onclick="refreshFilstO(\'n\')">Name</a>';
	$slab = '<a href="#" onclick="refreshFilstO(\'s\')">Size</a>';
	$so_asc = '<i class="fa fa-arrow-down"></i>';
	$so_dsc = '<i class="fa fa-arrow-up"></i>';
	if ($srtBy[0] == 'n') {
		if ($srtBy == 'nd') {
			$nsrtd = $so_dsc;
			$nsrtc = 'n';
		} else {
			$nsrtd = $so_asc;
			$nsrtc = 'nd';
		}
		$nlab = 'Name <a href="#" onclick="refreshFilstO(\''.$nsrtc.'\')">'.$nsrtd.'</a> ';
	} elseif ($srtBy[0] == 'm') {
		if ($srtBy == 'md') {
			$msrtd = $so_dsc;
			$msrtc = 'm';
		} else {
			$msrtd = $so_asc;
			$msrtc = 'md';
		}
		$mlab = 'Last Modified <a href="#" onclick="refreshFilstO(\''.$msrtc.'\')">'.$msrtd.'</a> ';
	} elseif ($srtBy[0] == 's') {
		if ($srtBy == 'sd') {
			$ssrtd = $so_dsc;
			$ssrtc = 's';
		} else {
			$ssrtd = $so_asc;
			$ssrtc = 'sd';
		}
		$slab = 'Size <a href="#" onclick="refreshFilstO(\''.$ssrtc.'\')">'.$ssrtd.'</a> ';
	}
?>
		<th class='left'><?=$nlab?></th>
		<th class='left tpad'><?=$mlab?></th>
		<th class='right tpad'><?=$slab?></th>
		<th class='left tpad'>Description</th>
		</tr>
		</thead>
		<tbody>
<?php
$dFiles = false;
$dtop = $fmx_dirs_at_top ?? false;
$dirs = $fils = [];
$path = $rDir;
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
	echo '<span style="color:red">Could not read directory: '.$dDir.'<br />Error: '.$error['message'].'</span>';	//Error("Could not read directory $rDir: $!");
}
if ($dFiles) {
	if (strpos('nms', $srtBy[0])) usort($dFiles, 'cmpF'.$srtBy);
	foreach ($dFiles as $flear) {
		$fle = $flear[0];
		$fPth = "$rDir/{$fle}";
		$fs = $flear[1];	//lstat($fPth);
		if (!$fs) {
			echo '<tr><td>Can not stat '.$rDir.'/'.$fle.'</td></tr>';
			continue;
		}
		$lok = true;
		$isLnk = ($fs[2] & 0xA000)==0xA000;
		$rlnk = '';
		if ($isLnk) {
			$fs = @stat($fPth);
			$lok = (bool)$fs;
			$fs = $fs ?: [0,0,0,0,0,0,0,0,0,0];
			$rlnk = readlink($fPth);
		}
		$afle = ($pDir ? "$pDir/" : '') . $fle;
		$efle = urlencode($afle);
		if ($fs[2] & 040000) {
			echo "<tr data-fref='$fle'>";
			$dt = date('M d, Y g:ia', $fs[9]);
			if (is_writable($fPth)) {
				echo '<td><input type="checkbox" class="fsel" name="files[]" value="'.$fle.'/" /></td>';
			} else {
				echo '<td>&nbsp;</td>';
			}
			echo '<td class="diricon foldCtxt"><i class="fa fa-folder" aria-hidden="true"></i></td>';
			echo '<td class="foldCtxt">';
			if ($lok) { echo '<a href="index.php?dir='.$efle.'">'.$fle.'</a>'; }
			else { echo $fle; }
			echo ($isLnk ? " &rarr; $rlnk" : '').'</td>';
			echo '<td class="tpad">'.$dt.'</td>';
			echo '<td class="right tpad">--</td>';
			echo '<td class="tpad"><a href="#" data-act="finf"><i class="fa fa-info-circle" aria-hidden="true"></i></a></td>';
			echo '</tr>'."\n";
			}
		else {
			echo "<tr data-fref=\"$fle\">";
			$ufle = htmlspecialchars($fle);
			$fnp = explode('.',$ufle);
			$ufle = str_replace(' ','&nbsp;',$ufle);
			$flext = array_pop($fnp);
			$filedt = preg_match('/php|js|html|htm|pl|cgi|css|ini|xml|sql|txt|csv|htaccess/i', $flext);
			$imgedt = preg_match('/jpg|jpeg|png|gif|bmp/i', $flext);
			$sz = $fs[7];
			if ($sz > 1073741824) {$sz = sprintf('%.1f', ($sz / 1073741824)). 'g';}
			elseif ($sz > 1048575) {$sz = sprintf('%.1f', ($sz / 1048576)) . 'm';}
			elseif ($sz > 1023) {$sz = sprintf('%.1f', ($sz / 1024)) . 'k';}
			$dt = date('M d, Y g:ia', $fs[9]);
			if (is_writable($fPth)) {
				echo '<td><input type="checkbox" class="fsel" name="files[]" value="'.$fle.'" /></td>';
			} else {
				echo '<td>&nbsp;</td>';
			}
			if ($filedt) {
				echo '<td class="filedticon fileCtxt" onclick="doFileAction(\'fedt\',this,event)"><i class="fa fa-file" aria-hidden="true"></td>';
			} elseif ($imgedt) {
				echo '<td class="imgedticon fileCtxt" onclick="doFileAction(\'iedt\',this,event)"><i class="fa fa-file-image-o" aria-hidden="true"></i></td>';
			} else {
				echo '<td class="filicon fileCtxt"><i class="fa fa-file" aria-hidden="true"></i></td>';
			}
			echo '<td class="fileCtxt">';
			if ($lok) { echo '<a href="#" data-act="fvue">'.$ufle.'</a>'; }
			else { echo $ufle; }
			echo ($isLnk ? " &rarr; $rlnk" : '').'</td>';
			echo '<td class="tpad">'.$dt.'</td>';
			echo '<td class="right tpad">'.$sz.'</td>';
			echo '<td class="tpad"><a href="#" data-act="finf"><i class="fa fa-info-circle" aria-hidden="true"></i></a></td>';
			echo '</tr>'."\n";
			}
	}
}
?>
		</tbody>
	</table>
</form>
<div id="footerPop">
	<i id="footerPopButton" class="fa fa-bars fa-2x" aria-hidden="true"></i>
	<div id="footerPopContent">
		<form name="cliterm" method="post">
		<input type="hidden" name="dir" value="<?php echo $pDir ?>" />
		<input type="hidden" name="mcmdlin" value="" />
		Command: <input type="text" id="cmdlin" name="cmdlin" size="80" maxlength="512" />
		<input type="button" name="doCmd" value="Do it" onclick="fils2up()" />
		<a href="#" data-mnu="cmcs" data-req="0">?</a>
		</form>
	</div>
</div>
<!-- <hr /><br /> -->
<?php if (isset($fmx_ui_cli) && $fmx_ui_cli) : ?>
<div id="trmfrm">
<?php if (isset($rsptxt) && $rsptxt) : ?>
<a name="cmdRsp"></a>
<br />With selection:
&nbsp;<a href="#" onclick="event.preventDefault();selectionAction(true)">edit</a>
&nbsp;<a href="#" onclick="event.preventDefault();selectionAction(false)">view</a>
<br /><div style="padding:6px;border:1px solid #BBB;background-color:#FFF"><xmp style="margin:0"><?php echo $rsptxt ?></xmp></div>
<script type="text/javascript">window.location.hash="cmdRsp";</script>
<?php endif; ?>
</div>
<?php endif; ?>
<div id="upload" class="jqmWindow"><div class="upldr"></div><span class="button jqmClose"><img src="<?=$appB?>css/closex.png" alt="close" /></span></div>
<div id="element_to_pop_up" class="jqmWindow">
	<div class="bpDlgHdr"><span class="bpDlgTtl">TITLE</span><span class="button jqmClose"><img src="<?=$appB?>css/closex.png" alt="close" /></span></div>
	<div class="bpDlgCtn"><form class="bp-dctnt" name="myUIform" onsubmit="return false"></form></div>
	<div class="bpDlgFtr"><div class="bp-bttns"></div></div>
</div>
<div style="display:none">
	<div id="aMsgDlog" title="Message:"><span class="aMsg">{msg}</span></div>
	<div id="aSchDlog" title="Search:"><input type="hidden" name="cmd" value="{cmd}" /><input type="text" class="dlgitxt" name="sterm" value="{trm}" maxlength="80" /></div>
	<div id="fRenDlog" title="Rename:"><input type="hidden" name="oldnm" value="{old}" /><input type="text" class="dlgitxt" name="nunam" value="{new}" maxlength="80" /></div>
	<div id="fNamDlog" title="{ttl}"><input type="hidden" name="act" value="{act}" /><input type="text" class="dlgitxt" name="fref" maxlength="80" /></div>
	<div class="contextMenu" id="clrdMenu">
		<ul>
			<li id="clrdClr">Clear</li>
			<li id="clrdDsp">Display</li>
		</ul>
	</div>
	<div class="contextMenu" id="clrdMenuSL">
		<ul>
			<li id="clrdClr">Clear</li>
			<li id="clrdDsp">Display</li>
			<li id="symLnk">SymLink</li>
		</ul>
	</div>
	<div class="contextMenu" id="delfMenu">
		<ul>
			<li id="delfTrue">Truly Delete</li>
			<li id="delfMpty">Empty Trash</li>
			<li id="delfView">View Trash</li>
		</ul>
	</div>
	<div class="contextMenu" id="upldMenu">
		<ul>
			<li id="H5w">H5win</li>
			<li id="H5o">H5ovr</li>
			<li id="L4w">L4win</li>
			<li id="L4o">L4ovr</li>
		</ul>
	</div>
	<div class="contextMenu" id="fileCtxt">
		<ul>
			<li id="cfi_edt">Edit</li>
			<li id="cfi_del">Delete</li>
			<li id="cfi_dld">Download</li>
			<li id="cfi_dup">Duplicate</li>
			<li id="cfi_ren">Rename</li>
			<li id="cfi_zip">Zip</li>
		</ul>
	</div>
	<div class="contextMenu" id="foldCtxt">
		<ul>
			<li id="cfo_del">Delete</li>
			<li id="cfo_dld">Download</li>
			<li id="cfo_dup">Duplicate</li>
			<li id="cfo_ren">Rename</li>
			<li id="cfo_zip">Zip</li>
		</ul>
	</div>
</div>
<script>
	var $table = $('#ftbl'),
		$bodyCells = $table.find('tbody tr:first').children(),
		colWidth;

	// Get the tbody columns width array
	colWidth = $bodyCells.map(function() {
		return $(this).width();
	}).get();

	// Set the width of thead columns
	$table.find('thead tr').children().each(function(i, v) {
		$(v).width(colWidth[i]);
	});

	// Set file table scrolling height
	//$('#ftbl tbody').css('max-height', (window.innerHeight - 180) + 'px');
	$(window).resize(function() {
		var bhgt = Math.ceil($('#ftbl tbody').position().top) + parseInt($('body').css('margin-bottom')) + 6;
		$('#ftbl tbody').css('max-height', (window.innerHeight - bhgt) + 'px');
	//	console.log(bhgt);
	});
	$(window).trigger('resize');

	// set footer popup trigger
	var fpopen = false;
	$('#footerPopButton').click(function () {
		if (fpopen === false) {
				$('#footerPopContent').addClass('open');
			fpopen = true;
		} else {
				$('#footerPopContent').removeClass('open');
			fpopen = false;
		}
	});

	$('a.altm').on('mouseenter', (e) => {
		let telm = $(e.target);
		if (e.altKey) {
			telm.next().children('.mnur').hide();
			telm.next().children('.mnua').show();
		} else {
			telm.next().children('.mnua').hide();
			telm.next().children('.mnur').show();
		}
	});
</script>
<?php if (!$fmxInJoomla): ?>
</body>
</html>
<?php endif; ?>