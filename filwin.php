<?php
require_once 'functions.php';
$fref = $_GET['fref'];
$mtyp = FileMimeType($baseDir.$fref);
$fvurl = 'filvue.php?fref=' . urlencode($fref);
?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo $fref; ?></title>
<script type="text/javascript">
var resizeTime = null;
function viewFile() {
	document.getElementById('fvue').src='<?php echo $fvurl; ?>';
}
function viewAs(mTyp) {
	document.getElementById('fvue').src='<?php echo $fvurl; ?>'+'&mtyp='+mTyp;
}
function resizeFrame() {
	if (resizeTime) {
		clearTimeout(resizeTime);
		resizeTime = null;
	}
	var fv = document.getElementById('fvue');
	if (fv) {
		fv.height = window.innerHeight - fv.offsetTop - 12 + "px";
	}
	viewFile();
}
function expWin() {
	window.moveTo(0, 0);
	window.resizeTo(screen.availWidth, screen.availHeight);
}
function winResized() {
	if (resizeTime) clearTimeout(resizeTime);
	resizeTime=setTimeout(resizeFrame,200);
}
window.onresize = winResized;
</script>
<style>
iframe#fvue {clear:both;overflow:hidden;width:100%;margin-top:6px;border:1px solid #E0E0E0;box-sizing:border-box}
div.mtsel {float:right;}
img.wacti {float:right;margin-top:2px;margin-right:10px;}
</style>
</head>
<body style="overflow:hidden;height:100%" onload="resizeFrame()">
<b><?php echo $fref; ?></b> (<?php echo $mtyp; ?>)
<div class="mtsel">view as: <select onChange="viewAs(this.value);">
<option value ="">default</option>
<option value ="image/png">png</option>
<option value ="text/plain">text</option>
<option value ="0x">hex</option>
</select></div>
<img class="wacti" src="graphics/expwin.png" title="Expand window" alt="" onclick="expWin()" />
<iframe id="fvue" src=""></iframe>
</body>
</html>
