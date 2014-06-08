<?php
require_once('functions.php');
$uploadMaxFilesize = ini_get('upload_max_filesize');
$uploadMaxFilesizeBytes = return_bytes($uploadMaxFilesize);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" class="upld-body">
<head>
	<title>HTML5 Multi-file Upload</title>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
	<link rel="stylesheet" type="text/css" href="css/fmx.css" />
	<style>
<?php readfile('css/upload.inc.css'); ?>
	</style>
</head>
<body>
	<p style="color:red">Maximum file size: <?php echo $uploadMaxFilesize; ?></p>
	<input type="hidden" name="MAX_FILE_SIZE" id="MAX_FILE_SIZE" value="<?php echo $uploadMaxFilesizeBytes; ?>" />
	<input type="file" multiple="multiple" id="upload_field" />
	&nbsp;<br />
	<div id="dropArea">Or drop files here</div>
	&nbsp;<br />
	<div id="progress_report" style="position:relative">
		<div id="progress_report_name"></div>
		<div id="progress_report_status" style="font-style: italic;"></div>
		<div id="totprogress">
			<div id="progress_report_bar" style="background-color: blue; width: 0; height: 100%;"></div>
		</div>
		<div>Files in queue: <span id="qCount">0</span></div>
		<div id="fprogress"></div>
		<div id="server_response"></div>
	</div>
	<script type="text/javascript">
		var filesPath = sessionStorage.fmx_curD;
		var uploadMaxFilesize = <?php echo $uploadMaxFilesizeBytes; ?>;
		// optional array of allowed mime types
		//var fup_ftypes = ['image/jpeg'];
		var fup_payload = {'fpath':sessionStorage.fmx_curD, 'oefile':'1'};
		function fup_done() { parent.opener.refreshFilst(); }
<?php readfile('js/upload5d.js'); ?>
	</script>
</body>
</html>
