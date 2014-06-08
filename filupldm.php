<?php
require_once('functions.php');
?>
<div id="uplfrm" style="margin:12px">
<p style="color:red">Maximum upload size: <?php echo ini_get('post_max_size') ?></p>
<form id="upform" name="upform" action="upload.php" method="post" enctype="multipart/form-data" target="submit-iframe">
<input type="hidden" id="up_fpath" name="fpath" value="">
<div id="files">
	<input type="file" name="user_file[]" id="upload_field" multiple="multiple" />
	<br /><br /><label><input type="checkbox" name="ovrok" value="on" style="margin-right:5px;vertical-align:text-top" />Overwrite same-named server files</label>
</div>
<hr />
<input type="button"  name="do_upload" value="Upload file(s)" onclick="chknsend()" style="float:right;margin-bottom:12px" />
</form>
</div>
<div id="uplmsg" style="display:none;color:red">
	<p style="width:100%;text-align:center"><big>UPLOADING</big><br /><br />Do not close this window until the upload completes.<br />(be patient)</p>
</div>
<iframe name="submit-iframe" style="display:none"></iframe>
<script type="text/javascript">
	function appendFileSel(curelm) {
		$(curelm).unbind();
		$(curelm).after('<input type="file" name="user_file[]" />');
		$(curelm).next().change( function() { appendFileSel(this); });
	}
	$(function() {
		$('#up_fpath').val(sessionStorage.fmx_curD);
		$('#upload_field').change( function() { if (!this.hasOwnProperty('multiple')) {appendFileSel(this);} });
	});
	function chknsend () {
		document.upform.submit();
		$('#uplfrm').hide();
		$('#uplmsg').show();
	}
</script>
