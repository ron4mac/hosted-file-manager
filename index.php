<?php
include 'fmx.ini';
include 'cfg.php';
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
			$basDir = trim(`cd ~;pwd`);
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
	if (get_magic_quotes_gpc()) { $cmd = stripslashes($cmd); }
	if (isset($_POST['mcmdlin']) && $_POST['mcmdlin']) { $cmd = str_replace('::',$basDir.'/',$_POST['mcmdlin']); }
	chdir($rDir);
	$rsptxt = `$cmd 2>&1`;
	// get rid of backspaced characters (found in man pages)
	$rsptxt = preg_replace('/.\x08/','',$rsptxt);
	// mostly because of a possible </textarea>, escape tags
	$rsptxt = preg_replace(array('/<xmp/','/<\/xmp/'),array('<x m p','</x m p'),$rsptxt);		//removed since using <xmp> to wrap output
	}
?>
<?php if (!$fmxInJoomla): ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US" xml:lang="en-US">
<head>
<title>Files :: <?php echo $rootD?></title>
<meta charset="UTF-8">
<meta name="google" content="notranslate">
<link rel="stylesheet" type="text/css" href="css/css.php" />
<script src="<?=$jqlink?>"></script>
<script src="js/js.php" type="text/javascript"></script>
<script type="text/javascript">
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
	}
else {
	print $rootD;
	}
?>
</span>
<hr style="margin:6px 0" />
<div id="fmnu">
	<nav>
	<ul>
		<li>
			<a href="#" data-mnu="mnu">archive</a>
			<ul class="fallback">
				<li><a href="#" data-mnu="zip" data-req="2">zip</a></li>
				<li><a href="#" data-mnu="uzip" data-req="2">unzip</a></li>
				<li><a href="#" data-mnu="tarz" data-req="2">tar/gz</a></li>
				<li><a href="#" data-mnu="utrz" data-req="2">untar/gz</a></li>
			</ul>
		</li>
		<li><a href="#" data-mnu="cppa" data-req="2" class="cppaMenu">copy/paste</a></li>
		<!-- <li><a href="#" data-mnu="delf" data-req="2">delete</a></li> -->
		<li><a href="#" data-mnu="trsh" data-req="2" class="delfMenu">delete<?=$trshc?></a></li>
		<li><a href="#" data-mnu="dnld" data-req="2">download</a><div class="dnldprg"> rr</div></li>
		<li><a href="#" data-mnu="dupl" data-req="2">duplicate</a></li>
		<li><a href="#" data-mnu="mark" data-req="2" class="markMenu">mark</a></li>
		<li><a href="#" data-mnu="mvto" data-req="2" class="mvtoMenu">move/to</a></li>
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
				<li><a href="#" data-mnu="pvck" data-req="0">phpVerReq</a></li>
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
		<tr>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
		<th class='left'>Name</th>
		<th>&nbsp;&nbsp;</th>
		<th class='left'>Last Modified</th>
		<th>&nbsp;&nbsp;</th>
		<th class='right'>Size</th>
		<th>&nbsp;&nbsp;</th>
		<th class='left'>Description</th>
		</tr>
<?php
if ($pDir) {
	$aRef = $_SERVER['PHP_SELF'];	//'index.php';
	if (dirname($pDir)!='.') {
		$aRef .= '?dir=' . urlencode(dirname($pDir));
		}
	echo '<tr><td><input type="checkbox" onchange="allSelect(event,this)" /></td><td><img src="'.$appB.'icons/arrow-ret.png" width="16" height="16" alt="" /></td><td colspan="7"><a href="'.$aRef.'">Parent&nbsp;Folder</a></td></tr>';
}
$dFiles = array();
if ($drsrc = @opendir($rDir)) {
    while (false !== ($entry = readdir($drsrc))) {
        if ($entry != "." && $entry != "..") {
            $dFiles[] = $entry;
        }
    }
    closedir($drsrc);
} else {
	$error = error_get_last();
	echo '<span style="color:red">Could not read directory: '.$dDir.'<br />Error: '.$error['message'].'</span>';	//Error("Could not read directory $rDir: $!");
}

sort($dFiles);
foreach ($dFiles as $fle) {
	$fPth = "$rDir/$fle";
	$fs = lstat($fPth);
	if (!$fs) {
		echo '<tr><td>Can not stat '.$rDir.'/'.$fle.'</td></tr>';
		continue;
	}
	$isLnk = ($fs[2] & 0xA000)==0xA000;
	$rlnk = '';
	if ($isLnk) {
		$fs = @stat($fPth);
		$rlnk = readlink($fPth);
	}
	$afle = ($pDir ? "$pDir/" : '') . $fle;
	$efle = urlencode($afle);
	if ($fs[2] & 040000) {
		echo "<tr data-fref='$fle'>";
		$dt = strftime("%b %d, %Y  %l:%M%P", $fs[9]);
		if (is_writable($fPth)) {
			echo '<td><input type="checkbox" class="fsel" name="files[]" value="'.$fle.'/" /></td>';
		} else {
			echo '<td>&nbsp;</td>';
		}
		echo '<td class="diricon foldCtxt"> </td>';
		echo '<td class="foldCtxt"><a href="index.php?dir='.$efle.'">'.$fle.'</a>'.($isLnk ? " &rarr; $rlnk" : '').'</td>';
		echo '<td></td><td>'.$dt.'</td><td></td>';
		echo '<td class="right">--</td>';
		echo '<td></td><td><a href="#" data-act="finf"><img src="'.$appB.'graphics/info10x10.gif" width="10" height="10" alt="" /></a></td>';
		echo '</tr>'."\n";
		}
	else {
		echo "<tr data-fref='$fle'>";
		$ufle = htmlspecialchars($fle);
		$fnp = explode('.',$ufle);
		$ufle = str_replace(' ','&nbsp;',$ufle);
		$flext = array_pop($fnp);
		$filedt = preg_match('/php|js|html|htm|pl|cgi|css|ini|xml|sql|txt|csv|htaccess/i', $flext);
		$imgedt = preg_match('/jpg|jpeg|png|gif|bmp/i', $flext);
		$sz = $fs[7];
		if ($sz > 1048575) {$sz = sprintf('%.1f', ($sz / 1048576)) . 'm';}
		elseif ($sz > 1023) {$sz = sprintf('%.1f', ($sz / 1024)) . 'k';}
		$dt = strftime("%b %d, %Y  %l:%M%P", $fs[9]);
		if (is_writable($fPth)) {
			echo '<td><input type="checkbox" class="fsel" name="files[]" value="'.$fle.'" /></td>';
		} else {
			echo '<td>&nbsp;</td>';
		}
		if ($filedt) {
			echo '<td class="filedticon fileCtxt" onclick="doFileAction(\'fedt\',this,event)">&nbsp;</td>';
		} elseif ($imgedt) {
			echo '<td class="imgedticon fileCtxt" onclick="doFileAction(\'iedt\',this,event)">&nbsp;</td>';
		} else {
			echo '<td class="filicon fileCtxt">&nbsp;</td>';
		}
		print '<td class="fileCtxt"><a href="#" data-act="fvue">'.$ufle.'</a>'.($isLnk ? " &rarr; $rlnk" : '').'</td>';
		echo '<td></td><td>'.$dt.'</td><td></td>';
		echo '<td class="right">'.$sz.'</td>';
		echo '<td></td><td><a href="#" data-act="finf"><img src="'.$appB.'graphics/info10x10.gif" width="10" height="10" alt="" /></a></td>';
		echo '</tr>'."\n";
		}
}
?>
	</table>
</form>
<hr /><br />
<?php if (isset($fmx_ui_cli) && $fmx_ui_cli) : ?>
<div id="trmfrm">
<form name="cliterm" method="post">
<input type="hidden" name="dir" value="<?php echo $pDir ?>" />
<input type="hidden" name="mcmdlin" value="" />
Command: <input type="text" id="cmdlin" name="cmdlin" size="80" maxlength="200" />
<input type="button" name="doCmd" value="Do it" onclick="fils2up()" />
<a href="#" data-mnu="cmcs" data-req="0">?</a>
</form>
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
<div style="display:none">
<div id="aMsgDlog" title="Message:"><span class="aMsg">{msg}</span></div>
<div id="aSchDlog" title="Search:"><input type="hidden" name="cmd" value="{cmd}" /><input type="text" name="sterm" value="{trm}" size="55" maxlength="80" /></div>
<div id="fRenDlog" title="Rename:"><input type="hidden" name="oldnm" value="{old}" /><input type="text" name="nunam" value="{new}" size="55" maxlength="80" /></div>
<div id="fNamDlog" title="{ttl}"><input type="hidden" name="act" value="{act}" /><input type="text" name="fref" size="55" maxlength="80" /></div>
<div id="fCpmDlog" title="CopMov:"><input type="hidden" name="act" value="{act}" /><input type="hidden" name="cpmfnm" value="{cpm}" /><p>From: {cpm}</p>To: <input type="text" name="cpm2nam" value="{cpm}" size="70" maxlength="100" /></div>
</div>
<div class="jqmWindow" id="upload"><div class="upldr"></div><span class="button jqmClose"><img src="<?=$appB?>css/closex.png" alt="close" /></span></div>
<div id="element_to_pop_up" class="jqmWindow">
	<div class="bpDlgHdr"><span class="bpDlgTtl">TITLE</span><span class="button jqmClose"><img src="<?=$appB?>css/closex.png" alt="close" /></span></div>
	<div class="bpDlgCtn"><form class="bp-dctnt" name="myUIform" onsubmit="return false"></form></div>
	<div class="bpDlgFtr"><div class="bp-bttns"></div></div>
</div>
<div class="contextMenu" id="cppaMenu">
	<ul>
		<li id="cppaClr">Clear</li>
		<li id="cppaDsp">Display</li>
	</ul>
</div>
<div class="contextMenu" id="delfMenu">
	<ul>
		<li id="delfTrue">Truly Delete</li>
		<li id="delfMpty">Empty Trash</li>
	</ul>
</div>
<div class="contextMenu" id="upldMenu">
	<ul>
		<li id="H5w">H5win</li>
		<li id="H5o">H5ovr</li>
		<li id="L4w">L4win</li>
		<li id="L4o">L4ovr</li>
		<li id="Chk">Chunked</li>
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
<?php if (!$fmxInJoomla): ?>
</body>
</html>
<?php endif; ?>