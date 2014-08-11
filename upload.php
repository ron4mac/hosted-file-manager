<?php
error_reporting(-1);
require_once('functions.php');
$error_types = array(
	1=>'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
	'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
	'The uploaded file was only partially uploaded.',
	'No file was uploaded.',
	6=>'Missing a temporary folder.',
	'Failed to write file to disk.',
	'A PHP extension stopped the file upload.'
	);
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
			else $msg .= 'Error: failed to upload';
			}
		else $msg .= 'Error: '.$error_types[$error];
		}
//	if ($msg) exit($msg);
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<title>Upload Complete</title>
<script type="text/javascript">
<?php if ($msg): ?>
	alert("<?php echo $msg; ?>");
<?php else: ?>
	parent.opener.refreshFilst();
<?php endif; ?>
</script>
</head>
<body>
	<p style="width:100%;text-align:center"><br /><br /><big>UPLOADING COMPLETE</big></p>
</body>
</html>