<?php
require_once('functions.php');
/*
$rmtuser = getenv('REMOTE_USER');
$cooknam = 'fil_vew' . ($rmtuser ? "_$rmtuser" : '');
$cook = $_COOKIE[$cooknam];
if (!$cook) { exit; }
*/
$fpath = escapeshellcmd($_GET['path']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<title>File Upload</title>
<script src="/js/jq/jquery-latest.min.js" type="text/javascript"></script>
<script type="text/javascript">
	function appendFileSel(curelm) {
		$(curelm).unbind();
		$(curelm).after('<input type="file" name="user_file[]" />');
		$(curelm).next().change( function() { appendFileSel(this); });
	}
	$(function() {
		$('#upload_field').change( function() { appendFileSel(this); });
	});
	function chknsend () {
		document.upform.submit();
		$('#uplfrm').hide();
		$('#uplmsg').show();
	}
</script>
</head>
<body>
<div id="uplfrm">
<p>Maximum upload size: <?php echo ini_get('post_max_size') ?></p>
<form name="upform" action="upload.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="fpath" value="<?php echo $fpath; ?>">
<div id="files">
	<input type="file" name="user_file[]" id="upload_field" />
	<br /><br /><label><input type="checkbox" name="ovrok" value="on" />Overwrite same-named server files</label>
</div>
<hr />
<input type="button"  name="do_upload" value="Upload file(s)" onclick="chknsend()" />
</form>
</div>
<div id="uplmsg" style="display:none;color:red">
	<p style="width:100%;text-align:center"><big>UPLOADING</big><br /><br />Do not close this window until the upload completes.<br />(be patient)</p>
</div>
</body>
</html>