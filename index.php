<?php
$rmtuser = getenv('REMOTE_USER');
$cooknam = 'fil_vew' . ($rmtuser ? "_$rmtuser" : '');

$basDir = '';
$pDir = '';
$rDir = '';
$rootD = '';
$rootcook = '';

// establish (or get) a base directory path
if (isset($_GET['bgn'])) {
	$basDir = $_GET['bgn'];
	$rootcook = setcookie($cooknam,convert_uuencode($basDir));
} else {
	$basDir = convert_uudecode($_COOKIE[$cooknam]);
	if (!$basDir) {
		$basDir = trim(`cd ~;pwd`);
		$rootcook = setcookie($cooknam,convert_uuencode($basDir));
		}
}

// get any current directory path
if (isset($_GET['dir'])) {
	$pDir = urldecode($_GET['dir']);
}

// full path to current directory
$rDir = $basDir . ($pDir ? "/$pDir" : '');
// current directory name
$rootD = basename($basDir);

if (isset($_POST['cmdlin'])) {
	$cmd = $_POST['cmdlin'];
	if (get_magic_quotes_gpc()) { $cmd = stripslashes($cmd); }
	if (isset($_POST['mcmdlin']) && $_POST['mcmdlin']) { $cmd = str_replace('::',$basDir.'/',$_POST['mcmdlin']); }
	chdir($rDir);
	$rsptxt = `$cmd 2>&1`;
	// get rid of backspaced characters (found in man pages)
	$rsptxt = preg_replace('/.\x08/','',$rsptxt);
	// mostly because of a possible </textarea>, escape tags
//	$rsptxt = preg_replace(array('/</','/>/'),array('&lt;','&gt;'),$rsptxt);		removed since using <xmp> to wrap output
	}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US" xml:lang="en-US">
<head>
<title>Files :: <?php echo $rootD?></title>
<meta charset="UTF-8">
<meta http-equiv="Content-Language" content="en" />
<meta name="google" content="notranslate">
<link rel="stylesheet" type="text/css" href="css/jqModal.css" />
<link rel="stylesheet" type="text/css" href="css/fmx.css" />
<link rel="stylesheet" type="text/css" href="css/fmxui.css" />
<link rel="stylesheet" type="text/css" href="css/nav.css" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="js/js.php" type="text/javascript"></script>
<script type="text/javascript">
var curDir='<?php echo $pDir;?>/';
try { sessionStorage.fmx_ok = 1; }
catch(err) { alert("Your browser 'sessionStorage' is not functioning. (private browsing?) Not all functions of FMX will work successfully."); }
</script>
</head>
<body class="fmgt">
<span class="pathbread">
<?php
if ($pDir) {
	print '<a href="index.php">'.$rootD.'</a>';
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
<hr />
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
		<li><a href="#" data-mnu="copy" data-req="2">copy</a></li>
		<li><a href="#" data-mnu="cppa" data-req="2">copy/paste</a></li>
		<li><a href="#" data-mnu="delf" data-req="2">delete</a></li>
		<li><a href="#" data-mnu="dnld" data-req="2">download</a><div class="dnldprg"> rr</div></li>
		<li><a href="#" data-mnu="dupl" data-req="2">duplicate</a></li>
		<li><a href="#" data-mnu="mark" data-req="2">mark</a></li>
		<li><a href="#" data-mnu="move" data-req="2">move</a></li>
		<li><a href="#" data-mnu="mvto" data-req="2">move/to</a></li>
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
		<li><a href="#" data-mnu="upld" data-req="0">upload</a></li>
		<li><a href="#" data-mnu="webv" data-req="0">webview</a><!-- <span style="padding-left:8px"> || </span> --></li>
		<li>&nbsp;&nbsp;&nbsp;||&nbsp;</li>
		<li><a href="#" data-mnu="jxtr" data-req="1">jextract</a></li>
		<li><a href="#" data-mnu="fmxi" data-req="0">?</a></li>
	</ul>
	</nav>
	<nav style="float:right">
	<ul>
		<li><a href="#" data-mnu="updt" data-req="0">update</a></li>
	</ul>
	</nav>
</div>
<br /><hr />
<form name="filst">
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
	$aRef = 'index.php';
	if (dirname($pDir)!='.') {
		$aRef .= '?dir=' . urlencode(dirname($pDir));
		}
	echo '<tr><td><input type="checkbox" onchange="allSelect(event,this)" /></td><td><img src="icons/arrow-ret.png" width="16" height="16" alt="" /></td><td colspan="7"><a href="'.$aRef.'">Parent&nbsp;Folder</a></td></tr>';
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
		$fs = stat($fPth);
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
		echo '<td class="diricon"> </td>';
		echo '<td><a href="index.php?dir='.$efle.'">'.$fle.'</a>'.($isLnk ? " &rarr; $rlnk" : '').'</td>';
		echo '<td></td><td>'.$dt.'</td><td></td>';
		echo '<td class="right">--</td>';
		echo '<td></td><td><a href="#" data-act="finf"><img src="graphics/info10x10.gif" width="10" height="10" alt="" /></a></td>';
		echo '</tr>'."\n";
		}
	else {
		echo "<tr data-fref='$fle'>";
		$ufle = htmlspecialchars($fle);
		$fnp = explode('.',$ufle);
		$ufle = str_replace(' ','&nbsp;',$ufle);
		$canedt = preg_match('/php|js|html|htm|pl|cgi|css|ini|xml|sql|txt|csv|htaccess/', array_pop($fnp));
		$sz = $fs[7];
		if ($sz > 1048576) {$sz = sprintf('%.1f', ($sz / 1048576)) . 'm';}
		elseif ($sz > 1024) {$sz = sprintf('%.1f', ($sz / 1024)) . 'k';}
		$dt = strftime("%b %d, %Y  %l:%M%P", $fs[9]);
		if (is_writable($fPth)) {
			echo '<td><input type="checkbox" class="fsel" name="files[]" value="'.$fle.'" /></td>';
		} else {
			echo '<td>&nbsp;</td>';
		}
		if ($canedt) {
			echo '<td class="filedticon" onclick="doFileAction(\'fedt\',this,event)">&nbsp;</td>';
		} else {
			echo '<td class="filicon">&nbsp;</td>';
		}
		print '<td><a href="#" data-act="fvue">'.$ufle.'</a>'.($isLnk ? " &rarr; $rlnk" : '').'</td>';
		echo '<td></td><td>'.$dt.'</td><td></td>';
		echo '<td class="right">'.$sz.'</td>';
		echo '<td></td><td><a href="#" data-act="finf"><img src="graphics/info10x10.gif" width="10" height="10" alt="" /></a></td>';
		echo '</tr>'."\n";
		}
}
?>
	</table>
</form>
<br /><br /><hr /><br />
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
<!-- <input type="button" value="Act on selection" onclick="selectionAction(false);"> -->
<br /><div style="padding:6px;border:1px solid #BBB;background-color:#FFF"><xmp style="margin:0"><?php echo $rsptxt ?></xmp></div>
<!-- <br /><textarea id="cmdRsp" name="rsptxt" rows="40" onselect="noselectionAction()"><-?php echo $rsptxt ?-></textarea> -->
<script type="text/javascript">window.location.hash="cmdRsp";</script>
<?php endif; ?>
</div>
<div style="display:none">
<!-- <div id="aMsgDlg" title="Message:"><span id="aMsg"></span></div> -->
<!-- <div id="fMrkDlg" title="Marked files:"><span id="fMrk"></span></div> -->
<!-- <div id="fRenDlg" title="Rename:"><input type="hidden" id="oldnm" value="old" /><input type="text" id="nunam" name="nunam" size="55" maxlength="80" /></div> -->
<!-- <div id="fNamDlg" title="Name:"><input type="hidden" id="ffact" value="new" /><input type="text" id="ffnam" name="ffnam" size="55" maxlength="80" /></div> -->
<!-- <div id="fCpyDlg" title="Copy:"><input type="hidden" id="cpyfnm" value="old" /><p id="cpyfnam"></p>To: <input type="text" id="cpy2nam" name="nunam" size="70" maxlength="100" /></div> -->
<!-- <div id="fMovDlg" title="Move:"><input type="hidden" id="movfnm" value="old" /><p id="movfnam"></p>To: <input type="text" id="mov2nam" name="nunam" size="70" maxlength="100" /></div> -->
<!-- <div id="fCpyDlog" title="Copy:"><input type="hidden" name="cpyfnm" value="{cpy}" /><p>From: {cpy}</p>To: <input type="text" name="cpy2nam" value="{cpy}" size="70" maxlength="100" /></div> -->
<!-- <div id="fMovDlog" title="Move:"><input type="hidden" name="movfnm" value="{mov}" /><p>From: {mov}</p>To: <input type="text" name="mov2nam" value="{mov}" size="70" maxlength="100" /></div> -->
<div id="aMsgDlog" title="Message:"><span class="aMsg">{msg}</span></div>
<div id="aSchDlog" title="Search:"><input type="hidden" name="cmd" value="{cmd}" /><input type="text" name="sterm" value="{trm}" size="55" maxlength="80" /></div>
<div id="fRenDlog" title="Rename:"><input type="hidden" name="oldnm" value="{old}" /><input type="text" name="nunam" value="{new}" size="55" maxlength="80" /></div>
<div id="fNamDlog" title="{ttl}"><input type="hidden" name="act" value="{act}" /><input type="text" name="fref" size="55" maxlength="80" /></div>
<div id="fCpmDlog" title="CopMov:"><input type="hidden" name="act" value="{act}" /><input type="hidden" name="cpmfnm" value="{cpm}" /><p>From: {cpm}</p>To: <input type="text" name="cpm2nam" value="{cpm}" size="70" maxlength="100" /></div>
</div>
<div class="jqmWindow" id="upload"><div class="upldr"></div><span class="button jqmClose"><img src="css/closex.png" alt="close" /></span></div>
<div id="element_to_pop_up" class="jqmWindow">
	<div class="bpDlgHdr"><span class="bpDlgTtl">TITLE</span><span class="button jqmClose"><img src="css/closex.png" alt="close" /></span></div>
	<div class="bpDlgCtn"><form class="bp-dctnt" name="myUIform" onsubmit="return false"></form></div>
	<div class="bpDlgFtr"><div class="bp-bttns"></div></div>
</div>
</body>
</html>
