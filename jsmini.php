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
		<input type="hidden" name="up_fpath" value="" />
		<input type="hidden" name="CC[output_info]" value="compiled_code" />
		<input type="hidden" name="CC[output_format]" value="json" />
		<input type="hidden" name="CC[language_out]" value="ECMASCRIPT_2015" />
		<label>Level: </label>
		<select name="CC[compilation_level]">
			<option value="WHITESPACE_ONLY">Whitespace Only</option>
			<option value="SIMPLE_OPTIMIZATIONS" selected>Simple Optimizations</option>
			<option value="ADVANCED_OPTIMIZATIONS">Advanced Optimizations</option>
		</select>
		<br><label>Save as: </label><input type="text" name="tofile" style="width:50%" value="<?=$fnm?>" />
		<br /><hr /><input type="button" name="do_upload" value="<?=$btnttl?>" onclick="chknsend()" style="float:right;margin-bottom:12px" /><img id="curlspin" src="graphics/spinner.gif" style="float:right;display:none" />
	</form>
</div>
<script type="text/javascript">
	$(function() {
		var slctd = $(".fsel:checked");
		$('#curlurl').val($(slctd[0]).parents('tr').attr('data-fref'));
		$('#up_fpath').val(sessionStorage.fmx_curD);
	});
	function chknsend () {
		var frm = document.upform;
		var fnm = frm.tofile.value.trim();
		if (!fnm) { alert("Please enter a valid file name to save as"); return; }
		let fData = new FormData(document.forms.upform);
		fData.append('path', curDir);
		fetch(fmx_AJ, {method:'POST', body: fData})
		.then(resp => resp.text())
		.then(txt => { if (txt) {alert(txt);$('#upload').jqmHide();} else refreshFilst(); })
		.catch(err => alert(err));

		frm.do_upload.disabled = true;
		$('#curlspin').show();
	}
</script>
