<?php
require_once('functions.php');

if (isset($_POST['savef'])) {
	$fref = doUnescape($_POST['fref']);
	if (!$fref) exit(0);
	$fpath = $baseDir . $fref;
	$fcon = doUnescape($_POST['fcontent']);
	$rslt = file_put_contents($fpath, str_replace("\r\n","\n",$fcon));
	if ($rslt === FALSE) echo 'FAILED TO SAVE :O(';
	else echo 'SAVED :O)';
	exit(0);
}

$fref = doUnescape($_GET['fref']);
$fpath = $baseDir . $fref;
if (!file_exists($fpath)) {
	echo 'FILE DOES NOT EXIST: '.$fpath;
	exit();
}
$fInfo = FileMimeType($fpath);
$fcon = file_get_contents($fpath);

$fext = pathinfo($fref,PATHINFO_EXTENSION);
switch (strtolower($fext)) {
	case 'htm':
	case 'html':
		$mode = 'html';
		break;
	case 'js':
		$mode = 'javascript';
		break;
	case 'php':
		$mode = 'php';
		break;
	case 'css':
		$mode = 'css';
		break;
	case 'xml':
		$mode = 'xml';
		break;
	default:
		$mode = '';
}
$scrptFilPrts = explode('/',__FILE__);
?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo $fref; ?></title>
<link rel="stylesheet" type="text/css" href="css/sdrop.css" />
<script src="js/ace/ace.js" data-ace-base="js/ace" type="text/javascript" charset="utf-8"></script>
<script src="js/ace/ext-language_tools.js" type="text/javascript" charset="utf-8"></script>
<?php if(!$mode): ?>
<script src="js/ace/ext-modelist.js" type="text/javascript" charset="utf-8"></script>
<?php endif; ?>
<script type="text/javascript">
ace.require("ace/ext/language_tools");
function pop(url, h1, w1) {
	var h2 = (screen.height-h1)/2;
	var w2 = (screen.width-w1)/2;
	var wcon="toolbar=no,status=no,location=no,menubar=no,resizable=0,scrollbars=1,width="+w1+",height="+h1+",left="+w2+",top="+h2;
	return open(url, "", wcon);
}
</script>
<style>
html, body {width:100%;height:100%;margin:0;padding:0;}
div.cntrl {float:left;margin-right:10px;}
.sbutton {border:1px solid #633;cursor:pointer;margin:0;}
#editor { position: absolute;top:33px;right:0;bottom:0;left:0;}
</style>
</head>
<body>
	<form action="<?php echo array_pop($scrptFilPrts); ?>" method="post" name="sform" style="position:relative:height:33px;" onsubmit="eData.value=editor.getSession().getValue();return true;">
		<ul id="navc" class="drop">
			<li><img src="graphics/cfg16.png" />
				<ul>
					<li onclick="editor.setShowInvisibles(!editor.getShowInvisibles())">Toggle Invisibles</li>
					<li onclick="editor.renderer.setShowGutter(!editor.renderer.getShowGutter())">Toggle Gutter</li>
					<li>Code Mode
						<ul>
							<li onclick="editor.getSession().setMode('ace/mode/javascript')">javascript</li>
							<li onclick="editor.getSession().setMode('ace/mode/html')">html</li>
							<li onclick="editor.getSession().setMode('ace/mode/php')">php</li>
							<li onclick="editor.getSession().setMode('ace/mode/css')">css</li>
							<li onclick="editor.getSession().setMode('ace/mode/perl')">perl</li>
							<li onclick="editor.getSession().setMode('ace/mode/xml')">xml</li>
							<li onclick="editor.getSession().setMode('ace/mode/json')">json</li>
							<li onclick="editor.getSession().setMode('ace/mode/mysql')">mysql</li>
						</ul>
					</li>
					<li onclick="pop('acecommands.html',600,600)">Command Guide</li>
				</ul>
			</li>
		</ul>
		<div id="ftbar" style="height:20px;padding:6px 6px;background-color:#FFA;border-bottom:1px solid #CCC">
			<div class="cntrl">
				<input type="button" name="undo" value="&larr;" class="sbutton" title="Undo (cmd-Z)" onclick="editor.undo()" />
			</div>
			<div class="cntrl">
				<input type="button" name="redo" value="&rarr;" class="sbutton" title="Redo (cmd-Y)" onclick="editor.redo()" />
			</div>
			<div class="cntrl">
				<input type="button" name="cmnt" value="//" class="sbutton" title="Comment selected (cmd-/)" onclick="editor.toggleCommentLines()" />
			</div>
			<div class="cntrl">
				<input type="button" name="uncm" value="/*" class="sbutton" title="Un-comment selected (cmd-shft-/)" onclick="editor.toggleBlockComment()" />
			</div>
<!--			<div class="cntrl">
				<input type="button" name="frmt" value="{}" class="sbutton" title="Format selected" onclick="autoFormatSelection()" />
			</div> -->
			<div class="cntrl">
				<input type="submit" name="savef" value="Save" class="sbutton" title="Save changes" />
			</div>
			<span><?php echo $fref; ?> (<?=$mode?>)</span>
		</div>
		<textarea id="editBox" name="fcontent" style="display:none"><?php echo htmlspecialchars($fcon,ENT_IGNORE); ?></textarea>
		<input type="hidden" name="fref" value="<?php echo $fref; ?>" />
	</form>
	<div id="editor"></div>
<script type="text/javascript">
var eData = document.getElementById('editBox');
var editor = ace.edit("editor");
editor.setShowPrintMargin(false);
editor.getSession().setUseSoftTabs(false);
editor.getSession().setValue(eData.value);
editor.setTheme("ace/theme/rjcode");
<?php if ($mode): ?>
	editor.getSession().setMode("ace/mode/<?=$mode?>");
<?php else: ?>
	var modelist = ace.require("ace/ext/modelist");
	var mobj = modelist.getModeForPath("<?php echo $fref; ?>");
	editor.getSession().setMode(mobj.mode);
<?php endif; ?>
editor.setOptions({
	enableBasicAutocompletion: true,
	enableSnippets: true
});
editor.focus();
</script>
</body>
</html>
