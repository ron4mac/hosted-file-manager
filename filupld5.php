<?php
require_once 'functions.php';
include 'cfg.php';
$uploadMaxFilesize = ini_get('upload_max_filesize');
$uploadMaxFilesizeBytes = return_bytes($uploadMaxFilesize);
$postMaxSize = ini_get('post_max_size');
$postMaxSizeBytes = return_bytes($postMaxSize);
$memoryLimit = ini_get('memory_limit');
$memoryLimitBytes = return_bytes($memoryLimit);
$maxChunkSize = min($uploadMaxFilesizeBytes,$postMaxSizeBytes,$memoryLimitBytes,67108864) - 1048576;
$fw = empty($_GET['o']);	// is a request for full popup window content
$done = $fw ? 'parent.opener.refreshFilst(); if (!errcnt) window.close();' : 'if (!errcnt) refreshFilst();';
?>
<?php if ($fw): ?>
<?php header('Cache-Control: no-cache'); ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" class="upld-body">
<head>
	<title>HTML5 Multi-file Upload</title>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
	<link rel="stylesheet" href="<?php echo $fontawsm; ?>">
<?php endif; ?>
	<link rel="stylesheet" type="text/css" href="css/fmx.css" />
	<link rel="stylesheet" type="text/css" href="uplodr/upload.css" />
	<script type="text/javascript">
		var fmx_appPath = '';
		var filesPath = sessionStorage.fmx_curD;
		var uploadMaxFilesize = <?php echo $uploadMaxFilesizeBytes; ?>;
		var h5_fup = {
			lang: {abortd:'-- aborted', noupld:'Could not upload', toobig:'File is larger than max size allowed.', notype:'Cannot upload a file of this type.'},
			// optional array of allowed mime types
			//ftypes: ['image/jpeg'],
			payload: {'fpath':sessionStorage.fmx_curD, 'oefile':'1'},
			done: function (errcnt) { parent.opener.refreshFilst(); if (!errcnt) window.close(); }
		};
		var h5uOptions = {
			maxchunksize: <?php echo $maxChunkSize; ?>,
			payload: {'fpath':sessionStorage.fmx_curD, 'oefile':'1'},
			doneFunc: function (okcnt, errcnt) { <?php echo $done; ?> }
		};
	</script>
	<script type="text/javascript" src="uplodr/upload<?=$jsver?>.js"></script>
<?php if ($fw): ?>
</head>
<body>
<?php endif; ?>
<!-- <?php echo $uploadMaxFilesize,' :: ',$postMaxSize,' :: ',$memoryLimit.'<br>'; ?> -->
<!-- <?php echo $uploadMaxFilesizeBytes,' :: ',$postMaxSizeBytes,' :: ',$memoryLimitBytes,' = ',$maxChunkSize; ?> -->
	<!-- <p style="color:red">Maximum file size: <?php echo $uploadMaxFilesize; ?></p> -->
	<input type="hidden" name="MAX_FILE_SIZE" id="MAX_FILE_SIZE" value="<?php echo $uploadMaxFilesizeBytes; ?>" />
	<div id="uplodr"></div>
	<script type="text/javascript">H5uSetup();</script>
<?php if ($fw): ?>
</body>
</html>
<?php endif; ?>
