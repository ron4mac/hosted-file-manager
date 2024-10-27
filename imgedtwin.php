<?php
//file_put_contents('IMGED.LOG',print_r($_POST,true).print_r($_FILES,true),FILE_APPEND);

require_once 'functions.php';
include 'cfg.php';

if (!empty($_FILES['croppedImage'])) {
	try {
		$upf = $_FILES['croppedImage'];
		if ($upf['error'] == UPLOAD_ERR_OK) {
			$tmp_name = $upf['tmp_name'];
			if (is_uploaded_file($tmp_name)) {
				if (!move_uploaded_file($tmp_name, $baseDir.$_POST['fpath'].$_POST['fname'])) {
					throw new Exception('Error: failed to place file');
				}
			} else {
				throw new Exception('Error: failed to upload');
			}
		} else {
			throw new Exception($upld_err_txt[$upf['error']], $upf['error']);
		}
	} catch (Exception $e) {
		header('HTTP/1.0 406 '.$e->getMessage(), true, 406);
	}
	exit();
}

$fref = urldecode($_POST['fref']);
$frefp = dirname($fref).'/';
$fname = basename($fref);
$fnamwe = pathinfo($fname, PATHINFO_FILENAME);
$mtype = FileMimeType($baseDir.$fref);
$imageSize = getimagesize($baseDir.$fref);
$iurl = 'filproxy.php?f='.urlencode($fref);
$appB = '';
header('Cache-Control: no-cache');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Image Edit :: <?php echo $fref?></title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" href="<?php echo $fontawsm; ?>" />
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.0/cropper.min.css" />
	<link rel="stylesheet" href="//rjcrans.net/rjlibs/dlog/dialog.css" />
	<link rel="stylesheet" href="css/fmxui.css" />
	<style>
		html, body { height:100%;margin:0; }
		#container { height:100vh;display:flex;flex-flow:column; }
		.content { display:flex;height:100%; }
		.lft20 { margin-left:1.5em; }
		.edtui { margin-bottom:10px; }
		#sbbtns { float:right; }
		#spinner { display:none;float:right; }
		.tsize { width:4em; }
		#target { /*width:100%;*/ max-width:100%;max-height:100%;min-width:60px;/*display:block;margin:auto;*/ }
		.toolbar { background-color:#E0E0FF;padding:8px;border:1px solid #BBB; }
		.panel { background-color:#BBB; }
		.panel ul { list-style-type:none;line-height:1.3em;padding: 0 1em;}
		.panel ul li { margin-bottom:6px;height:100% }
		.panel input {width:100%;box-sizing:border-box;}
		.editor { flex:100%;box-sizing:border-box;width:100%; }
		.eeditor { width:100%;box-sizing:border-box; }
		#snding { display:none; }
	</style>
	<script src="//cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.0/cropper.min.js"></script>
	<script src="//rjcrans.net/rjlibs/dlog/dialog.js" type="text/javascript"></script>
	<script src="//rjcrans.net/rjlibs/cmmn/common.js" type="text/javascript"></script>
	<script src="js/fmxui<?php echo $jsver; ?>.js" type="text/javascript"></script>
	<script>
		var imgfb = "<?php echo $frefp; ?>";
		var imgfn = "<?php echo $fname; ?>";
		var imgfnwe = "<?php echo $fnamwe; ?>";
		var mtype = "<?php echo $mtype; ?>";
		var curSx = 1;
		var curSy = 1;
		function setAspect (elem) {
			let ration = elem.value.split(':');
			let ratio = ration[1] ? ration[0]/ration[1] : 0;
			cropper.setAspectRatio(ratio);
		}
		function setScale (elem) {
			let sp = elem.value;
			let sx = curSx<0 ? -sp : sp;
			let sy = curSy<0 ? -sp : sp;
			cropper.scale(sx, sy);
			curSx = sx;
			curSy = sy;
			updImgVals();
		}
		function setScaleX (elem) {
			let sp = elem.value;
			cropper.scaleX(sp);
			updImgVals();
		}
		function setScaleY (elem) {
			let sp = elem.value;
			cropper.scaleY(sp);
			updImgVals();
		}
		function flip (v) {
			if (v) {
				curSy *= -1;
				cropper.scaleY(curSy);
			} else {
				curSx *= -1;
				cropper.scaleX(curSx);
			}
			updImgVals();
		}
		function setCnvVal (elem, prp) {
			cropper.setCanvasData({[prp]:elem.value*1});
			updImgVals();
		}
		function setDatVal (elem, prp) {
			cropper.setData({[prp]:elem.value*1});
		}
		function updateValD (e) {
			cropX.value = Math.round(e.detail.x);
			cropY.value = Math.round(e.detail.y);
			cropW.value = Math.round(e.detail.width);
			cropH.value = Math.round(e.detail.height);
		}
		function upSpinner (show) {
			let csss = document.getElementById("snding");
			csss.style.display = show ? "inline-block" : "none";
		}
		const saveToServer = (rslt, fd=null) => {
			upSpinner(true);
			if (!fd) {
				fd = new FormData();
				fd.append('fname', rslt);
				fd.append('imime', mtype);
			}
			let mt = fd.get('imime');
			cropper.getCroppedCanvas().toBlob((blob) => {

				fd.append('fpath', imgfb);
				fd.append('croppedImage', blob);

				fetch('imgedtwin.php', {method:'POST', body:fd})
				.then(resp => { if (!resp.ok) throw new Error(`HTTP ${resp.status}`); return resp.text() })
				.then(data => {
					upSpinner(false);
					if (data) alert(data);
				})
				.catch(err => alert('Failure: '+err));

			}, mt ?? mtype);
		};
		function saveAs (e) {
			rjOpenDlg(e,saveAsDlg,{
				fattrs:{['input[value="'+mtype+'"]|checked']:'', 'input[name="fname"]|value':imgfn},
				cb: saveToServer,
				class:'XXXXXX'
			});
		}
		const doDownload = (rslt, fd=null) => {
			let mt = fd.get('imime');
			let a = document.createElement('a');
			let result = cropper.getCroppedCanvas();
			a.href = result.toDataURL(mt);
			//a.download = imgfnwe + "." + mt.split("/")[1];
			a.download = fd.get('fname');
			a.click();
		};
		function download (e) {
			rjOpenDlg(e,dnldAsDlg,{
				fattrs:{['input[value="'+mtype+'"]|checked']:'', 'input[name="fname"]|value':imgfn},
				cb: doDownload,
				class:'XXXXXX'
			});
		}
		function updImgVals () {
			let imgd = cropper.getImageData();
			imgW.value = Math.round(imgd.naturalWidth);
			imgH.value = Math.round(imgd.naturalHeight);
		//	imgW.value = Math.round(imgd.width);
		//	imgH.value = Math.round(imgd.height);
			sclX.value = imgd.scaleX;
			sclY.value = imgd.scaleY;
			let cnvd = cropper.getCanvasData();
			cnvW.value = Math.round(cnvd.naturalWidth);
			cnvH.value = Math.round(cnvd.naturalHeight);
			cnvW.value = Math.round(cnvd.width);
			cnvH.value = Math.round(cnvd.height);
		}
		var saveAsDlg = {
			id: 1,
			title: 'Save image ...',
			cselect: '#saveAsDlog',
			modal: true,
			buttons: {
				cancel: {type: 'reset'},
				save: {}
			}
		};
		var dnldAsDlg = {
			id: 2,
			title: 'Download image ...',
			cselect: '#saveAsDlog',
			modal: true,
			buttons: {
				cancel: {type: 'reset'},
				download: {}
			}
		};
	</script>
</head>
<body>
<div id="container">
<div class="toolbar">
	<label>Constraint:</label>&nbsp;<select id="s-aspect" onchange="setAspect(this)">
		<option value="0">none</option>
		<option value="4:3">4:3</option>
		<option value="3:4">3:4</option>
		<option value="7:5">7:5</option>
		<option value="5:7">5:7</option>
		<option value="16:9">16:9</option>
		<option value="1:1">Square</option>
	</select>
	<button onclick="cropper.crop()">Crop start</button>
	<button onclick="cropper.clear()">Crop stop</button>
	<button onclick="cropper.rotate(-45);updImgVals()">Rotate Left</button>
	<button onclick="cropper.rotate(45);updImgVals()">Rotate Right</button>
	<button onclick="flip(true)">Flip V</button>
	<button onclick="flip(false)">Flip H</button>
	<select id="s-scale" onchange="setScale(this)">
		<option value="1">Scale 100%</option>
		<option value=".75">Scale 75%</option>
		<option value=".5">Scale 50%</option>
		<option value=".25">Scale 25%</option>
	</select>
	<button onclick="download(event)">Download</button>
	<button onclick="saveAs()">Save as ...</button>
	<button onclick="saveToServer(imgfn)">Save</button>
	<i id="snding" class="fa fa-spinner fa-pulse"></i>
</div>
<div class="content">
	<div class="panel" id="panel">
		<ul>
			<li>Canvas width<br><input type="number" step="1" id="cnvW" onchange="setCnvVal(this,'width')" /></li>
			<li>Canvas height<br><input type="number" step="1" id="cnvH" onchange="setCnvVal(this,'height')" /></li>
			<li>Image width<br><input type="number" step="1" id="imgW" onchange="" /></li>
			<li>Image height<br><input type="number" step="1" id="imgH" onchange="" /></li>
			<li>Scale X<br><input type="number" max="1" min="0" step="0.01" id="sclX" onchange="setScaleX(this)" /></li>
			<li>Scale Y<br><input type="number" max="1" min="0" step="0.01" id="sclY" onchange="setScaleY(this)" /></li>
			<li>Crop x pos<br><input type="number" step="1" id="crpx" onchange="setDatVal(this,'x')" /></li>
			<li>Crop y pos<br><input type="number" step="1" id="crpy" onchange="setDatVal(this,'y')" /></li>
			<li>Crop width<br><input type="number" step="1" id="crpw" onchange="setDatVal(this,'width')" /></li>
			<li>Crop height<br><input type="number" step="1" id="crph" onchange="setDatVal(this,'height')" /></li>
		<!--	<li>Arribute 1<br><input type="number" step="1" id="a1" onchange="" /></li>
			<li>Arribute 1<br><input type="number" step="1" id="a2" onchange="" /></li> -->
		</ul>
	</div>
	<div class="editor">
		<img src="<?php echo $iurl; ?>" id="target" />
	</div>
</div>
<script>
var valp = document.getElementById('panel');
if (valp) {
	valp.height = window.innerHeight - valp.offsetTop - 12 + "px";
}
const image = document.getElementById('target');
const cropper = new Cropper(image, {
	autoCrop: false,
	crop: function(e) { updateValD(e); },
	ready: function() { updImgVals(); }
});
var cnvW = document.getElementById("cnvW");
var cnvH = document.getElementById("cnvH");
var imgW = document.getElementById("imgW");
var imgH = document.getElementById("imgH");
var sclX = document.getElementById("sclX");
var sclY = document.getElementById("sclY");
var cropX = document.getElementById("crpx");
var cropY = document.getElementById("crpy");
var cropW = document.getElementById("crpw");
var cropH = document.getElementById("crph");
</script>
<div style="display:none">
	<div id="saveAsDlog">
		<div class="ffld">
			<label>As file: <input type="text" name="fname" /></label>
		</div>
		<div class="ffld">
			<label><input type="radio" name="imime" value="image/png" />&nbsp;PNG</label>
			<label><input type="radio" name="imime" value="image/jpeg" />&nbsp;JPEG</label>
		</div>
	</div>
</div>
</div>
</body>
</html>
