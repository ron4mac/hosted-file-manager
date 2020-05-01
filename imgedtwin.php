<?php
//file_put_contents('IMGED.LOG',print_r($_POST,true).print_r($_FILES,true),FILE_APPEND);

require_once 'functions.php';
include 'cfg.php';

if (!empty($_FILES['croppedImage'])) {
	$upf = $_FILES['croppedImage'];
	if ($upf['error'] == UPLOAD_ERR_OK) {
		$tmp_name = $upf['tmp_name'];
		if (is_uploaded_file($tmp_name)) {
			if (!move_uploaded_file($tmp_name, $baseDir.$_POST['fpath'])) {
				echo '<p class="failure">Error: failed to place file</p>';
				//file_put_contents('IMGED.LOG','Error: failed to place file',FILE_APPEND);
			}
		} else {
			echo '<p class="failure">Error: failed to upload</p>';
			//file_put_contents('IMGED.LOG','Error: failed to upload',FILE_APPEND);
		}
	} else {
		echo'<p class="failure">File: '.htmlspecialchars($_POST['fpath']).'<br>Error: '.$upld_err_txt[$upf['error']].'</p>';
		//file_put_contents('IMGED.LOG','File: '.htmlspecialchars($_POST['fpath']).' Error: '.$upld_err_txt[$upf['error']],FILE_APPEND);
	}
	exit();
}

$fref = urldecode($_POST['fref']);
$mtype = FileMimeType($baseDir.$fref);
$imageSize = getimagesize($baseDir.$fref);
$iurl = 'filproxy.php?f='.urlencode($fref);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Image Edit :: <?php echo $fref?></title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" href="<?php echo $fontawsm; ?>" />
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.min.css" />
	<style>
		.content { display:flex; }
		.lft20 { margin-left:1.5em; }
		.edtui { margin-bottom:10px; }
		#sbbtns { float:right; }
		#spinner { display:none;float:right; }
		.tsize { width:4em; }
		#target { /*width:100%;*/ max-width:100%;max-height:100%;min-width:60px;/*display:block;margin:auto;*/ }
		.toolbar { background-color:#E0E0FF;padding:8px;border:1px solid #BBB; }
		.panel { background-color:#BBB; }
		.panel ul { list-style-type:none;line-height:1.3em;padding: 0 1em;}
		.panel ul li { margin-bottom:6px; }
		.editor { flex:100%;box-sizing:border-box; }
		#snding { display:none; }
	</style>
	<script src="<?=$jqlink?>"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.min.js"></script>
	<script>
		var imgfb = "<?php echo dirname($fref).'/'; ?>";
		var imgfn = "<?php echo basename($fref); ?>";
		var mtype = "<?php echo $mtype; ?>";
		function setAspect (elem) {
			let ration = elem.value.split(':');
			let ratio = ration[1] ? ration[0]/ration[1] : 0;
			cropper.setAspectRatio(ratio);
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
		function save2srvr (thfn) {
			cropper.getCroppedCanvas().toBlob((blob) => {
			  const formData = new FormData();
			
			formData.append('fpath', imgfb+thfn);
			
			  // Pass the image file name as the third parameter if necessary.
			  formData.append('croppedImage', blob/*, 'example.png' */);
			$('#snding').show();
			  // Use `jQuery.ajax` method for example
			  $.ajax('imgedtwin.php', {
			    method: 'POST',
			    data: formData,
			    processData: false,
			    contentType: false,
			    success(data, textStatus, jqXHR) {
			      console.log('Upload success');
			      	$('#snding').hide();
			    },
			    error(jqXHR, textStatus, errorThrown) {
			      console.log('Upload error');
			    },
			  });
			}, mtype);
		}
		function saveAs () {
			let asn = prompt("Save as:", imgfn);
			if (asn) {
				save2srvr(asn);
			}
		}
		function download () {
           let a = document.createElement('a');
           let result = cropper.getCroppedCanvas();
            a.href = result.toDataURL(mtype);
            a.download = imgfn;
            document.body.appendChild(a);
            a.click();
		}
	</script>
</head>
<body>
<div class="toolbar">
	<label>Constraint:</label>&nbsp;<select id="aspect" onchange="setAspect(this)">
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
	<button onclick="cropper.rotate(-45)">Rotate Left</button>
	<button onclick="cropper.rotate(45)">Rotate Right</button>
	<button onclick="cropper.scale(.75,.75)">Scale .75</button>
	<button onclick="cropper.scale(.5,.5)">Scale .5</button>
	<button onclick="download()">Download</button>
	<button onclick="saveAs()">Save as ...</button>
	<button onclick="save2srvr(imgfn)">Save</button>
	<i id="snding" class="fa fa-circle-o-notch fa-spin"></i>
</div>
<div class="content">
	<div class="panel">
		<ul>
			<li>Crop x pos<br><input type="number" step="1" id="crpx" onchange="setDatVal(this,'x')" /></li>
			<li>Crop y pos<br><input type="number" step="1" id="crpy" onchange="setDatVal(this,'y')" /></li>
			<li>Crop width<br><input type="number" step="1" id="crpw" onchange="setDatVal(this,'width')" /></li>
			<li>Crop height<br><input type="number" step="1" id="crph" onchange="setDatVal(this,'height')" /></li>
			<li>Arribute 1<br><input type="number" step="1" id="a1" onchange="" /></li>
			<li>Arribute 1<br><input type="number" step="1" id="a2" onchange="" /></li>
			<li>Arribute 1<br><input type="number" step="1" id="a3" onchange="" /></li>
			<li>Arribute 1<br><input type="number" step="1" id="a4" onchange="" /></li>
			<li>Arribute 1<br><input type="number" step="1" id="a5" onchange="" /></li>
			<li>Arribute 1<br><input type="number" step="1" id="a6" onchange="" /></li>
		</ul>
	</div>
	<div class="editor">
		<img src="<?php echo $iurl; ?>" id="target" />
	</div>
</div>
<script>
const image = document.getElementById('target');
const cropper = new Cropper(image, {
				autoCrop: false,
				crop: function (e) { updateValD(e); }
		});
var cropX = document.getElementById("crpx");
var cropY = document.getElementById("crpy");
var cropW = document.getElementById("crpw");
var cropH = document.getElementById("crph");
</script>
</body>
</html>