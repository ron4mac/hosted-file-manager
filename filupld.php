<?php
require_once 'functions.php';
include 'cfg.php';
$fw = empty($_GET['o']);	// is a request for full popup window content
$fst = $fw ? '' : ' target="submit-iframe"';
?>
<?php if ($fw): ?>
<?php header('Cache-Control: no-cache'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" class="upld-body">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<title>File Upload</title>
<link rel="stylesheet" type="text/css" href="css/fmx.css" />
<script src="<?=$jqlink?>"></script>
</head>
<body>
<?php endif; ?>
<div id="uplfrm">
<p style="color:red">Maximum upload size: <?php echo ini_get('post_max_size') ?></p>
<form name="upform" action="upload.php" method="post" enctype="multipart/form-data"<?php echo $fst; ?>>
<input type="hidden" id="up_fpath" name="fpath" value="">
<?php if ($fw): ?>
<input type="hidden" name="w" value="1">
<?php endif; ?>
<div id="files">
	<input type="file" name="user_file[]" id="upload_field" multiple="multiple" />
	<br /><br /><label><input type="checkbox" name="ovrok" value="on" style="margin-right:5px;vertical-align:text-top" />Overwrite same-named server files</label>
</div>
<hr />
<input type="button"  name="do_upload" value="Upload file(s)" onclick="chknsend()" style="float:right;margin-bottom:12px" />
</form>
</div>
<div id="uplmsg" style="display:none;color:#228">
	<p style="width:100%;text-align:center"><big>UPLOADING</big><br /><br />Do not close this window until the upload completes.<br />(be patient)</p>
<?php if (!$fw): ?>
	<iframe name="submit-iframe" id="rslt-frame" style="width:100%;height:80px;border:none"></iframe>
<?php endif; ?>
</div>
<script type="text/javascript">
	function appendFileSel(curelm) {
		$(curelm).unbind();
		$(curelm).after('<input type="file" name="user_file[]" multiple="multiple" />');
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
<?php if ($fw): ?>
</body>
</html>
<?php endif; ?>