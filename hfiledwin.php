<?php
/**
* @package		fmx
* @copyright	Copyright (C) 2022-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		3.5.8
*/
require_once 'functions.php';
include 'cfg.php';

$popd = isset($_GET['t']) ? false : true;

if (isset($_POST['savef'])) {
	$fref = $_POST['savef'];
	if (!$fref) exit(0);
	$fpath = $baseDir . $fref;
	$fcon = $_POST['fcontent'];
	$rslt = file_put_contents($fpath, str_replace("\r\n","\n",$fcon));
	if ($rslt === FALSE) {
		echo 'FAILED TO SAVE :O(';
		exit(1);
	}
	exit(0);
}

$fref = $fref ?? $_GET['fref'];
$fpath = $baseDir . $fref;
if (!file_exists($fpath)) {
	echo 'FILE DOES NOT EXIST: '.$fpath;
	exit();
}
$htm = $htmh = $htmt = null;
if ($fpath) {
	$ptn = '/(.+<body[^>]*>)(.+)(<\/body.+)/s';
	$htdoc = file_get_contents($fpath);
	if (preg_match($ptn, $htdoc, $match) === 1) {
		$htmh = $match[1];
		$htmt = $match[3];
		$htm = $match[2];
	} else {
		$htm = $htdoc;
	}
}
//$fcon = file_get_contents($fpath);

header('Cache-Control: no-cache');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://cdn.tiny.cloud/1/cwovex9jfbk6xb24te45yzl87haxb62jemm763pcgeegltt8/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<!-- <script src="htmled/tinymce/tinymce.min.js" referrerpolicy="origin"></script> -->
<script>
	var baseURL = '<?=dirname($_SERVER['PHP_SELF'])?>';
	var f_path = '<?=dirname($fref).'/'?>';
	var f_name = '<?=basename($fref)?>';
	var d_head = null;
	var d_tail = null;
</script>
<script src="htmled/editiny.js"></script>
</head>
<body>
	<form method="POST">
		<textarea id="the_html" name="the_html"><?=$htm?></textarea>
		<input type="hidden" id="the_fnam" name="the_fnam" value="" />
	</form>
	<input type="file" id="floader" onchange="loadFile(this)" style="display:none" accept="text/html,text/plain" />
</body>
</html>

<?php
__halt_compiler();

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
var subb;
ace.require("ace/ext/language_tools");
function pop(url, h1, w1) {
	var h2 = (screen.height-h1)/2;
	var w2 = (screen.width-w1)/2;
	var wcon="toolbar=no,status=no,location=no,menubar=no,resizable=0,scrollbars=1,width="+w1+",height="+h1+",left="+w2+",top="+h2;
	return open(url, "", wcon);
}
window.addEventListener("beforeunload", function (e) {
	if (!editor.session.getUndoManager().hasUndo() || subb == 'saveclose') return;
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
function saveFile () {
	eData.value = editor.session.getValue();
	let eForm = document.forms.sform;
	let pData = new FormData(eForm);
	pData.append('savef','saveonly');
	fetch(eForm.action, {method: 'POST', body: pData})
	.then(rslt => {if (rslt.ok) return rslt.text()})
	.then(resp => {
		if (!resp) {
			if (subb == 'saveclose') {
				window.close();
			} else {
			//	alert ('File successfully saved');
				editor.session.getUndoManager().reset();
				document.getElementById('dirty').style.display = 'none';
			}
		} else console.log(resp);
	})
	.catch(err => console.log(err));
	return false;
}
</script>
<style>
html, body {width:100%;height:100%;margin:0;padding:0;}
form {position:relative:height:33px;}
#dirty {display:none;height:16px;vertical-align:middle;margin-right:4px;}
div.cntrl {float:left;margin-right:10px;}
div.cntrlr {float:right;margin-right:10px;}
.sbutton {border:1px solid #633;cursor:pointer;margin:0;}
#editor {position:absolute;top:33px;right:0;bottom:0;left:0;}
.message{padding:16px 7px;border:1px solid #ddd;background-color:#fff}
.message.ok{border-color:green;color:green}
.message.error{border-color:red;color:red}
.message.alert{border-color:orange;color:orange}
p.message{position:absolute;top:0;right:6px;left:6px;text-align:center;z-index:900;transition:all .5s;margin:0;padding:.5em;}
p.message.done{opacity:0;padding:0;margin:0;height:0};
</style>
</head>
<body>
	<form action="<?php echo array_pop($scrptFilPrts); ?>" method="post" name="sform" onsubmit="return saveFile()">
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
					<li onclick="editor.getSession().setUseWrapMode(true);">Soft Wrap</li>
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
<?php if ($popd): ?>
			<div class="cntrlr">
				<button type="submit" name="savef" value="saveclose" title="Save changes and close" onclick="subb = this.value">Save & Close</button>
			</div>
<?php endif; ?>
			<div class="cntrlr">
				<img id="dirty" src="css/dirty.png">
				<button type="submit" name="savef" value="saveonly" title="Save changes" onclick="subb = this.value">Save</button>
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
editor.commands.addCommand({
	name: 'softWrap',
	bindKey: {win: 'Ctrl-\\', mac: 'Command-\\'},
	exec: function(editor) {
		editor.getSession().setUseWrapMode(true);
	}
});
editor.on('change', (e) => {
	let dss = editor.session.getUndoManager().hasUndo() ? 'inline' : 'none';
	document.getElementById('dirty').style.display = dss;
});
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
	// increase # errs shown by jslint and show unused items
	session.$worker.send("changeOptions", [{maxerr: 9999, unused: true}]);
});
editor.focus();
const msgp = document.getElementsByClassName("message")[0];
if (msgp) setTimeout(function(){msgp.className += " done";}, 3000);
</script>
</body>
</html>
