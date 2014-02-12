<?php
require_once('functions.php');
$fpath = escapeshellcmd($_GET['path']);
$uploadMaxFilesize = ini_get('upload_max_filesize');
$uploadMaxFilesizeBytes = return_bytes($uploadMaxFilesize);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>HTML5 Multi-file Upload</title>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
	<style>
		#dropArea { border: 1px dashed #CCC; width: 100%; padding: 1em 0; text-align: center; border-radius: 5px }
		#dropArea.hover { background-color: #DFD; border: 1px solid #999; box-shadow: inset 2px 2px 3px #999 }
	</style>
</head>
<body style="font-family: 'Lucida Grande'; font-size: 11px;">
	<p style="color:red">Maximum file size: <?php echo $uploadMaxFilesize; ?></p>
	<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $uploadMaxFilesizeBytes; ?>" />
	<input type="file" multiple="multiple" id="upload_field" />
	&nbsp;<br />
	<div id="dropArea">Or drop files here</div>
	&nbsp;<br />
	<div id="progress_report">
		<div id="progress_report_name"></div>
		<div id="progress_report_status" style="font-style: italic;"></div>
		<div id="progress_report_bar_container" style="width: 100%; height: 8px; border: 1px solid #BBB;">
			<div id="progress_report_bar" style="background-color: blue; width: 0; height: 100%;"></div>
		</div>
		<span><br />Total progress:</span>
		<div id="totprogress_report_bar_container" style="width: 100%; height: 8px; border: 1px solid #BBB;">
			<div id="totprogress_report_bar" style="background-color: blue; width: 0; height: 100%;"></div>
		</div>
		<div>Files left: <span id="count">0</span></div>
		<div id="result"></div>
		<canvas width="404" height="20"></canvas>
		<div id="server_response"></div>
	</div>
	<script type="text/javascript">
		var filesPath = "<?php echo $fpath; ?>";
		var uploadMaxFilesize = <?php echo $uploadMaxFilesizeBytes; ?>;
	</script>
	<script src="js/upload5.js" type="text/javascript"></script>
</body>
</html>
