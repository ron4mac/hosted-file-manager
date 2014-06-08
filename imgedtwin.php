<?php
//echo'<pre>';var_dump($_POST);echo'</pre>';
require_once('functions.php');
$fref = urldecode($_POST['fref']);
if (!$fref) {
	if (!isset($_POST['imgfil'])) { die('Error: no image'); }
	$fref = $_POST['imgfil'];
	require_once('imager.php');
	$imgObj = new ImageCR($baseDir.$fref);

	if (isset($_POST['rotdeg'])) {
		$imgObj->rotate($_POST['rotdeg']);
	}
	if (isset($_POST['reszit'])) {
		$meth = 'sampled';
		$imgObj->copySample($_POST['x'], $_POST['y'], $_POST['reszw'], $_POST['reszh'], $_POST['w'], $_POST['h']);
	} else {
		$meth = 'copied';
		$imgObj->copy($_POST['x'], $_POST['y'], $_POST['w'], $_POST['h']);
	}

	if (isset($_POST['asfile']) && trim($_POST['asfile']) != '') {
		$fref = $_POST['asfile'];
	}
	$imgObj->saveToFile($baseDir.$fref);
}
$iurl = 'filproxy.php?f='.urlencode($fref);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Image Edit :: <?php echo $fref?></title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" href="css/Jcrop.min.css" type="text/css" />
	<style>
		.content { padding:12px; }
		.lft20 { margin-left:1.5em; }
		.edtui { margin-bottom:10px; }
		#sbbtns { float:right; }
		#spinner { display:none;float:right; }
		.tsize { width:4em; }
		#target { /*width:100%;*/ max-width:100%;max-height:100%;/*display:block;margin:auto;*/ }
	</style>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="js/jqcropper.js"></script>
	<script src="js/Jcrop.min.js"></script>
</head>
<body>
<script type="text/javascript">
var img_rsiz = false;
jQuery(function($){

	var jcrop_api,
		ratw = 1,
		rath = 1;

	$('#target').Jcrop({
		//trueSize: [400,300],
		bgOpacity: 0.3,
		onChange: showCoords,
		onSelect: function(c) { showCoords(c); updateCoords(c); },
		onRelease: clearCoords
	},function(){
		jcrop_api = this;
		var img = document.getElementById('target');
		this.setOptions({trueSize: [img.naturalWidth,img.naturalHeight]});
	});

	$('#target').on('complete','img',function(e){
		alert('done');
		//jcrop_api.setSelect([x1,y1,x2,y2]);
	});

	$('#coords').on('change','input',function(e){
		var x1 = $('#x1').val(),
			x2 = $('#x2').val(),
			y1 = $('#y1').val(),
			y2 = $('#y2').val();
		jcrop_api.setSelect([x1,y1,x2,y2]);
	});

	$('#aspect').change(function(e){
		var ratio = $(this).val().split(':');
		if (ratio[1]) {
			ratw = ratio[0]; rath = ratio[1];
			jcrop_api.setOptions({aspectRatio: +ratio[0]/+ratio[1]});
		} else {
			ratw = 1; rath = 1;
			jcrop_api.setOptions({aspectRatio: 0});
		}
	});

	$('#reszit').change(function(e){
		img_rsiz = $(this).prop('checked');
	});

	$('#reszw').change(function(e){
		$('#reszh').val(aAdjust($(this).val(),$('#reszh').val(),rath,ratw));
	}).keyup(function(e){
		$('#reszh').val(aAdjust($(this).val(),$('#reszh').val(),rath,ratw));
	});

	$('#reszh').change(function(e){
		$('#reszw').val(aAdjust($(this).val(),$('#reszw').val(),ratw,rath));
	}).keyup(function(e){
		$('#reszw').val(aAdjust($(this).val(),$('#reszw').val(),ratw,rath));
	});

	$('#rotdeg').change(function(e){
		var deg = $(this).val();
		jcrop_api.rotate(deg);
	}).keyup(function(e){
		var deg = $(this).val();
		jcrop_api.rotate(deg);
	});

});

// calc value for aspec compensation
function aAdjust(rto, cur, rn, rd)
{
	if (cur && (rn/rd == 1)) {
		return rto;
	} else if (rto) {
		return Math.round(rto * rn / rd);
	} else return "";
}

// Simple event handler, called from onChange and onSelect
// event handlers, as per the Jcrop invocation above
function showCoords(c)
{
	$('#x1').val(c.x);
	$('#y1').val(c.y);
	$('#x2').val(c.x2);
	$('#y2').val(c.y2);
	$('#w').val(c.w);
	$('#h').val(c.h);
	if (!img_rsiz) {
		$('#reszw').val(Math.round(c.w));
		$('#reszh').val(Math.round(c.h));
	}
};

function clearCoords()
{
	$('#coords input').val('');
	$('#reszw').val('');
	$('#reszh').val('');
};

function updateCoords(c)
{
	$('#fx').val(c.x);
	$('#fy').val(c.y);
	$('#fw').val(c.w);
	$('#fh').val(c.h);
};

function checkCoords()
{
	$('#sbbtns').hide();
	$('#spinner').show();
	return true;
	if (parseInt($('#fw').val())) return true;
	alert('Please select a crop region then press submit.');
	return false;
};

function saveAsFile()
{
	var fps = $('#imgfil').val().split('/');
	var newf = prompt("Save file as: ", fps.pop());
	if (!newf) { return false; }
	fps.push(newf);
	$('#asfile').val(fps.join('/'));
	return true;
}


</script>
<div class="content">
	<div class="edtui">
		<form action="" method="post" onsubmit="return checkCoords();">
			<label>Constraint: </label>
			<select id="aspect">
				<option value="0">none</option>
				<option value="4:3">4:3</option>
				<option value="3:4">3:4</option>
				<option value="7:5">7:5</option>
				<option value="5:7">5:7</option>
				<option value="16:9">16:9</option>
			</select>
			<label class="lft20">Rotate: </label>
			<input type="number" id="rotdeg" name="rotdeg" class="tsize" />
			<input type="hidden" id="fx" name="x" />
			<input type="hidden" id="fy" name="y" />
			<input type="hidden" id="fw" name="w" />
			<input type="hidden" id="fh" name="h" />
			<input type="hidden" id="dw" name="dw" />
			<input type="hidden" id="dh" name="dh" />
			<input type="hidden" id="imgfil" name="imgfil" value="<?php echo $fref; ?>" />
			<input type="hidden" id="asfile" name="asfile" value="" />
			<input type="checkbox" id="reszit" name="reszit" value="resz" class="lft20" /><label>Resize Image</label>
			<label class="lft20">w:&nbsp;</label><input type="text" id="reszw" name="reszw" class="tsize" />
			<label>h:&nbsp;</label><input type="text" id="reszh" name="reszh" class="tsize" />
			<span class="lft20">FILE NAME</span>
			<img id="spinner" src="graphics/spinner.gif" />
			<div id="sbbtns">
				<input type="submit" value="Save as ..." class="btn btn-large btn-inverse" onclick="return saveAsFile();" />
				<input type="submit" value="Save Image Changes" class="btn btn-large btn-inverse" />
			</div>
		</form>
	</div>
	<img src="<?php echo $iurl; ?>" id="target" />
</div>