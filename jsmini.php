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
		<label>Minifier: </label>
		<select name="CC[whch]" onchange="minisel(this)">
			<option value="G" selected>Google</option>
			<option value="T">Terser</option>
		</select>
		<div class="G">
			<label>ECMA Level: </label>
			<select name="CC[ecma]">
				<option value="" selected>&lt;default&gt;</option>
				<option value="ECMASCRIPT_2021">2021</option>
				<option value="ECMASCRIPT_2020">2020</option>
				<option value="ECMASCRIPT_2019">2019</option>
				<option value="ECMASCRIPT_2018">2018</option>
				<option value="ECMASCRIPT_2017">2017</option>
				<option value="ECMASCRIPT_2016">2016</option>
				<option value="ECMASCRIPT_2015">2015</option>
				<option value="ECMASCRIPT5">5</option>
				<option value="ECMASCRIPT3">3</option>
				<option value="STABLE">stable</option>
			</select>
		</div>
		<div class="T" style="display:none">
			<label>ECMA Level: </label>
			<select name="CC[ecmat]">
				<option value="" selected>&lt;default&gt;</option>
				<option value="2021">2021</option>
				<option value="2020">2020</option>
				<option value="2019">2019</option>
				<option value="2018">2018</option>
				<option value="2017">2017</option>
				<option value="2016">2016</option>
				<option value="2015">2015</option>
				<option value="5">5</option>
				<option value="3">3</option>
			</select>
			<br><input type="checkbox" id="rmcons" name="CC[rmcons]" value="1" checked /> <label for="rmcons">Remove console messages</label>
			<br><input type="checkbox" id="rmjsdoc" name="CC[rmjsdoc]" value="1" checked /> <label for="rmjsdoc">Remove JsDoc/License</label>
		</div>
		<label>Save as: </label><input type="text" name="tofile" style="width:50%" value="<?=$fnm?>" required />
		<br><br><hr><input type="button" name="do_upload" value="<?=$btnttl?>" onclick="chknsend()" style="float:right;margin-bottom:12px" /><img id="curlspin" src="graphics/spinner.gif" style="float:right;display:none" />
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
	function minisel (elm) {
		const sel = elm.value;
		for (const w of ['G','T']) {
			const dv = elm.parentElement.querySelector('.'+w);
			if (w==sel) dv.style.display = 'block';
			else dv.style.display = 'none';
		}
	}
</script>
