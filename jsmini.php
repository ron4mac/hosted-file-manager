<?php
require_once 'functions.php';
$fn = isset($_GET['f']) ? $_GET['f'] : '&gt;?&lt;';
$pinf = pathinfo($fn);
$fnm = $pinf['filename'].'.min.'.$pinf['extension'];
$curl_msg = "Minimize javascript file <em>$fn</em>";
$btnttl = 'Submit';
?>
<div id="uplfrm" style="margin:12px">
	<p><?=$curl_msg?></p>
	<form id="upform" name="upform" method="post" style="line-height:2em">
		<input type="hidden" name="act" value="jsmm" />
		<input type="hidden" name="up_fn" value="<?=$fn?>" />
		<input type="hidden" name="CC[output_format]" value="json" />
		<label>Level: </label>
		<select name="CC[compilation_level]">
			<option value="WHITESPACE_ONLY">Whitespace Only</option>
			<option value="SIMPLE_OPTIMIZATIONS" selected>Simple Optimizations</option>
			<option value="ADVANCED_OPTIMIZATIONS">Advanced Optimizations</option>
		</select>
		<br><label>Output: </label>
		<select name="CC[language_out]">
			<option value="ECMASCRIPT_2015">ECMASCRIPT 2015</option>
			<option value="ECMASCRIPT_2016">ECMASCRIPT 2016</option>
			<option value="ECMASCRIPT_2017">ECMASCRIPT 2017</option>
			<option value="ECMASCRIPT_2018">ECMASCRIPT 2018</option>
			<option value="ECMASCRIPT_2019">ECMASCRIPT 2019</option>
			<option value="ECMASCRIPT_2020" selected>ECMASCRIPT 2020</option>
			<option value="ECMASCRIPT_2021">ECMASCRIPT 2021</option>
			<option value="STABLE">STABLE</option>
			<option value="ECMASCRIPT_NEXT">NEXT</option>
		</select>
		<br><label>Save as: </label><input type="text" name="tofile" style="width:50%" value="<?=$fnm?>" required />
		<br><input type="checkbox" id="rmjsdoc" name="rmjsdoc" value="1" checked /> <label for="rmjsdoc">Remove JsDoc/License</label>
		<br /><hr /><input type="button" name="do_upload" value="<?=$btnttl?>" onclick="chknsend()" style="float:right;margin-bottom:12px" /><img id="curlspin" src="graphics/spinner.gif" style="float:right;display:none" />
	</form>
</div>
<script type="text/javascript">
	var slctd = document.querySelector('.fsel:checked');
	function chknsend () {
		let frm = document.upform;
		let fnm = frm.tofile.value.trim();
		if (!fnm) { alert('Please enter a valid file name to save as'); return; }
		let fData = new FormData(document.forms.upform);
		fData.append('path', curDir);
		fetch(fmx_AJ, {method:'POST', body: fData})
		.then(resp => resp.text())
		.then(txt => {
			if (txt) {
				let dlg = _rj.id('uplfrm').closest('dialog');
				dlg.close();
				alert(txt);
			} else refreshFilst();
		})
		.catch(err => alert(err));

		frm.do_upload.disabled = true;
		_rj.id('curlspin').style.display = 'inline-block';
	}
</script>
