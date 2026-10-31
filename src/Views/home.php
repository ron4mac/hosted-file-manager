<?php
/**
* @package		hosted-file-manager
* @copyright	Copyright (C) 2022-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		4.0.0
*/
defined('EXEC_HFM') or die('Access denied');

// get count of trash files
$trshs = @scandir(BASE_DIR.'/tmp/Trash');
$trshc = ($trshs && count($trshs)>2) ? ('('.(count($trshs)-2).')') : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Files :: <?=$rootD?></title>
	<meta charset="UTF-8">
	<meta name="google" content="notranslate">
	<link rel="stylesheet" href="<?=$fontawsm?>" />
	<link rel="stylesheet" type="text/css" href="static/css/css.php" />
	<link rel="stylesheet" href="//rjcrans.net/rjlibs/dlog/dialog.css" />
	<link rel="stylesheet" href="//rjcrans.net/rjlibs/cntx/context.css" />
	<script src="//rjcrans.net/rjlibs/cmmn/common.js" type="text/javascript"></script>
	<script src="//rjcrans.net/rjlibs/cntx/context.js" type="text/javascript"></script>
	<script src="//rjcrans.net/rjlibs/dlog/dialog.js" type="text/javascript"></script>
	<script src="static/js/js.php" type="text/javascript"></script>
	<script type="text/javascript">
	if (!window.fetch) alert("This web browser is not new enough to properly run this version of the File Manager");
	var fmx_appPath = '';
	var fmx_AJ='index.php/fmxjx';
	var curDir='<?=$pDir?>/';
	var ctxPrf='<?=$input->server('CONTEXT_PREFIX','')?>';
	var upload_winpop = <?=isset($fmx_upload_winpop)?'true':'false'?>;
	function allSelect (elm) {
		let ckd = elm.checked;
		document.querySelectorAll('.fsel').forEach(cb => {cb.checked = ckd;});
	}
	</script>
</head>
<body class="fmgt">
<span class="pathbread">
<?php
echo $spec_msg;
if ($pDir) {
	print '<a href="'.$input->server('PHP_SELF').'">'.$rootD.'</a>';
	$href = 'index.php?dir=';
	$path = explode('/', $pDir);
	$curD = array_pop($path);
	foreach ($path as $fold) {
		$href .= $fold;
		print '/<a href="'.$href.'">'.$fold.'</a>';
		$href .= '/';
	}
	print "/$curD";
	$aRef = $input->server('PHP_SELF');
	if (dirname($pDir) !== '.') {
		$aRef .= '?dir=' . urlencode(dirname($pDir));
	}
	$parntBut = '<a href="'.$aRef.'" title="Up to parent"><i class="fa fa-chevron-up" style="color:blue;font-size:large"></i></a>';
} else {
	print $rootD;
	$parntBut = '&nbsp;';
}
?>
</span>
<hr style="margin:6px 0" />
<div id="fmnu">
	<nav>
	<ul>
		<li class="drpm">
			<a href="#" class="altm archive" data-mnu="mnu">archive</a>
			<ul class="fallback altm">
				<li class="mnur"><a href="#" data-mnu="zip">zip</a></li>
				<li class="mnua"><a href="#" data-mnu="zipx">zip-x</a></li>
				<li><a href="#" data-mnu="uzip">unzip</a></li>
				<li><a href="#" data-mnu="tarz">tar/gz</a></li>
				<li><a href="#" data-mnu="utrz">untar/gz</a></li>
				<li class="mnusep"></li>
				<li><a href="#" data-mnu="mgzp">View</a></li>
			</ul>
		</li>
		<li><a href="#" id="cppaMenu" data-mnu="cppa" data-ctx="cppCtx">copy/paste</a></li>
		<li><a href="#" data-mnu="trsh" data-ctx="delCtx">delete<?=$trshc?></a></li>
		<li><a href="#" data-mnu="dnld">download</a><div class="dnldprg"> rr</div></li>
		<li><a href="#" data-mnu="dupl">duplicate</a></li>
		<li><a href="#" id="markMenu" data-mnu="mark" data-ctx="mrkCtx">mark</a></li>
		<li><a href="#" data-mnu="mdya">media</a></li>
		<li><a href="#" id="mvtoMenu" data-mnu="mvto" data-ctx="mvtCtx">move/to</a></li>
		<li class="drpm">
			<a href="#" class="altm archive" data-mnu="mnu">new</a>
			<ul class="fallback">
				<li><a href="#" data-mnu="nfle">file</a></li>
				<li><a href="#" data-mnu="nfld">folder</a></li>
			</ul>
		</li>
		<li><a href="#" data-mnu="refr">refresh</a></li>
		<li><a href="#" data-mnu="rnam">rename</a></li>
		<li class="drpm">
			<a href="#" data-mnu="mnu">search</a>
			<ul class="fallback">
				<li><a href="#" data-mnu="srhf">file</a></li>
				<li><a href="#" data-mnu="srhc">content</a></li>
			</ul>
		</li>
		<li class="drpm">
			<a href="#" data-mnu="mnu">transfer</a>
			<ul class="fallback">
				<li><a href="#" data-mnu="furl">from URL</a></li>
				<li><a href="#" data-mnu="turl">to URL</a></li>
			</ul>
		</li>
		<li><a href="#" data-mnu="upld" data-ctx="uplCtx">upload</a></li>
		<li><a href="#" data-mnu="webv">webview</a></li>
		<li>&nbsp;&nbsp;&nbsp;||&nbsp;</li>
		<li class="drpm">
			<a href="#" data-mnu="mnu">develop</a>
			<ul class="fallback">
				<li><a href="#" data-mnu="gitr">gitter</a></li>
				<li><a href="#" data-mnu="jxtr">jextract</a></li>
				<li><a href="#" data-mnu="mmiz">minify(.js)</a></li>
				<li><a href="#" data-mnu="sql3">sqlite3</a></li>
			</ul>
		</li>
		<li><a href="#" data-mnu="fmxi">?</a></li>
	</ul>
	</nav>
</div>
<hr style="margin:6px 0;clear:both" />
<form name="filst" id="filsform">
	<input type="hidden" name="dir" value="<?=$pDir?>" />
<?php
	include 'home-ftop.php';
	include 'home-flist.php';
?>
	</tbody>
	</table>
</form>
<?php if ($cfg->val('fmx_ui_cli')) : ?>
<div id="footerPop">
	<i id="footerPopButton" class="fa fa-bars fa-2x" aria-hidden="true"></i>
	<div id="footerPopContent">
		<form name="cliterm" method="post">
		<input type="hidden" name="dir" value="<?=$pDir?>" />
		<input type="hidden" name="mcmdlin" value="" />
		Command: <input type="text" id="cmdlin" name="cmdlin" size="80" maxlength="512" autocapitalize="off" spellcheck="false" />
		<input type="button" name="doCmd" value="Do it" onclick="fils2up()" />
		<a href="#" data-mnu="cmcs">?</a>
		</form>
	</div>
</div>
<div id="trmfrm">
<?php if (isset($rsptxt) && $rsptxt) : ?>
<a name="cmdRsp"></a>
<br />With selection:
&nbsp;<a href="#" onclick="event.preventDefault();selectionAction(true)">edit</a>
&nbsp;<a href="#" onclick="event.preventDefault();selectionAction(false)">view</a>
<br /><div style="padding:6px;border:1px solid #BBB;background-color:#FFF"><xmp style="margin:0"><?=$rsptxt?></xmp></div>
<script type="text/javascript">window.location.hash="cmdRsp";</script>
<?php endif; ?>
</div>
<?php endif; ?>
<div id="upload"><div class="upldr"></div></div>
<div style="display:none">
	<div id="aMsgDlog">
		<span class="ffld aMsg">{msg}</span>
	</div>
	<div id="aSchDlog">
		<input type="text" class="ffld dlgitxt" name="sterm" maxlength="80" autocapitalize="off" spellcheck="false" required />
		<input type="hidden" name="cmd" />
	</div>
	<div id="fRenDlog">
		<input type="text" name="nunam" class="ffld dlg-fname" maxlength="80" autocapitalize="off" spellcheck="false" required />
		<input type="hidden" name="oldnm" />
	</div>
	<div id="fNamDlog" title="{ttl}">
		<input type="text" name="fref" class="ffld dlg-fname" maxlength="80" autocapitalize="off" spellcheck="false" required />
		<input type="hidden" name="act" value="{act}" />
	</div>
</div>
<script>
	// set footer popup trigger
	_rj.ae('footerPopButton', 'click', () => {
		let fpc = _rj.id('footerPopContent').classList;
		if (fpc.contains('open')) fpc.remove('open');
		else fpc.add('open');
	});
/*
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
*/
</script>
</body>
</html>
