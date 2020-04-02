<?php
require_once 'functions.php';
include 'cfg.php';

if (isset($_POST['savef'])) {
	$fref = $_POST['fref'];
	if (!$fref) exit(0);
	$fpath = $baseDir . $fref;
	$fcon = $_POST['fcontent'];
	$rslt = file_put_contents($fpath, str_replace("\r\n","\n",$fcon));
	header("X-XSS-Protection: 0");
	if ($rslt === FALSE) echo 'FAILED TO SAVE :O(';
	else echo '<!DOCTYPE html><html><head><title></title><script>window.close();</script></head></html>';
	exit(0);
}

$fref = $_GET['fref'];
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
<script src="<?=$aceBase?>ace.js" type="text/javascript" charset="utf-8"></script>
<script src="<?=$aceBase?>ext-language_tools.js" type="text/javascript" charset="utf-8"></script>
<?php if(!isset($acetheme)): ?>
<script src="<?=$aceBase?>ext-themelist.js" type="text/javascript" charset="utf-8"></script>
<?php endif; ?>
<?php if(!$mode): ?>
<script src="<?=$aceBase?>ext-modelist.js" type="text/javascript" charset="utf-8"></script>
<?php endif; ?>
<script type="text/javascript">
ace.require("ace/ext/language_tools");
function pop(url, h1, w1) {
	var h2 = (screen.height-h1)/2;
	var w2 = (screen.width-w1)/2;
	var wcon="toolbar=no,status=no,location=no,menubar=no,resizable=0,scrollbars=1,width="+w1+",height="+h1+",left="+w2+",top="+h2;
	return open(url, "", wcon);
}
window.addEventListener("beforeunload", function (e) {
	if (!editor.session.getUndoManager().hasUndo() || editor.session.isSaving) return;
	var confirmationMessage = "You have not saved changes to this document.";
	(e || window.event).returnValue = confirmationMessage;	//Gecko + IE
	return confirmationMessage								//Webkit, Safari, Chrome etc.
});
function kbdHelp () {
	ace.config.loadModule("ace/ext/keybinding_menu", function(module) {
		module.init(editor);
		editor.showKeyboardShortcuts()
	})
}
function modeSel (elm) {
	//alert(elm.innerHTML);
	editor.getSession().setMode('ace/mode/'+elm.innerHTML);
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
	<form action="<?php echo array_pop($scrptFilPrts); ?>" method="post" name="sform" style="position:relative:height:33px;" onsubmit="eData.value=editor.session.getValue();editor.session.isSaving=true;return true;">
		<ul id="navc" class="drop">
			<li><img src="graphics/cfg16.png" />
				<ul>
					<li onclick="editor.setShowInvisibles(!editor.getShowInvisibles())">Toggle Invisibles</li>
					<li onclick="editor.renderer.setShowGutter(!editor.renderer.getShowGutter())">Toggle Gutter</li>
					<li>Code Mode
						<ul>
							<li onclick="modeSel(this)">javascript</li>
							<li onclick="modeSel(this)">html</li>
							<li onclick="modeSel(this)">php</li>
							<li onclick="modeSel(this)">css</li>
							<li onclick="modeSel(this)">perl</li>
							<li onclick="modeSel(this)">xml</li>
							<li onclick="modeSel(this)">json</li>
							<li onclick="modeSel(this)">mysql</li>
						</ul>
					</li>
					<li onclick="kbdHelp()">Command Guide</li>
<?php if (!isset($acetheme)): ?>
					<li>Theme
						<ul id="thmlst"></ul>
					</li>
<?php endif; ?>
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
<?php if (!isset($acetheme)): ?>
var themelist = ace.require("ace/ext/themelist");
var thmMnu = document.getElementById('thmlst');
var thmn, thm;
for (thmn in themelist.themes) {
	thm = themelist.themes[thmn];
	thmMnu.innerHTML += '<li title="'+thm.name+'" onclick="editor.setTheme(\''+thm.theme+'\');">'+thm.caption+'</li>';
}
<?php endif; ?>
var eData = document.getElementById('editBox');
var editor = ace.edit("editor");
editor.$blockScrolling = Infinity;
editor.setShowPrintMargin(false);
editor.getSession().setUseSoftTabs(false);
editor.getSession().setValue(eData.value);
<?php
$atheme = '';
if (isset($acetheme)) {
	if ($acetheme[0] == '/') {
		$acetheme = substr($acetheme, 1);
		echo "ace.config.set('themePath', 'js/ace');\n";
	}
	$atheme = $acetheme;
	echo "editor.setTheme(\"ace/theme/{$atheme}\")\n";
}
?>
//editor.setTheme("ace/theme/<?=$atheme?>");
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
editor.session.on('changeMode', function(e, session){
	// increase # errs shown by jslint
	session.$worker.send("changeOptions", [{maxerr: 9999}]);
});
editor.focus();
</script>
</body>
</html>
