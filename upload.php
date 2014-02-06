<?php
require_once('functions.php');
/*
$rmtuser = getenv('REMOTE_USER');
$cooknam = 'fil_vew' . ($rmtuser ? "_$rmtuser" : '');
$cook = $_COOKIE[$cooknam];
if (!$cook) { exit; }
$baseDir = convert_uudecode($cook).'/';
*/
if ($_FILES['user_file'] && isset($_POST['fpath'])) {
	$msg = '';
	$fpath = $_POST['fpath'];
	foreach ($_FILES['user_file']['error'] as $key => $error) {
		if ($error == UPLOAD_ERR_OK) {
			$tmp_name = $_FILES['user_file']['tmp_name'][$key];
			if (is_uploaded_file($tmp_name)) {
				$name = $_FILES['user_file']['name'][$key];
				move_uploaded_file($tmp_name, $baseDir.$fpath.$name);
				}
			else $msg .= 'failed to upload';
			}
		elseif ($error!=4) $msg .= "Error: $error";
		}
	if ($msg) exit($msg);
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<title>Upload Complete</title>
<script src="/js/jq/jquery-latest.min.js" type="text/javascript"></script>
<script type="text/javascript">
	$(function() {
		parent.opener.refreshFilst();
		//setTimeout("window.close()",3000);
	});
</script>
</head>
<body>
	<p style="width:100%;text-align:center"><br /><br /><big>UPLOADING COMPLETE</big></p>
</body>
</html>