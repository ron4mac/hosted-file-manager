<?php
require_once('functions.php');
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
		#totprogress { width: 100%; height: 8px; border: 1px solid #BBB; border-radius: 3px; background: #eee url("css/progress_t.png") 100% 0 no-repeat }
		#fprogress p { display: block; padding: 2px 5px; margin: 2px 0; border: 1px solid #BBB; border-radius: 3px; font-size: 0.9em; background: #eee url("css/progress_f.png") 100% 0 no-repeat; }
		#fprogress p.indeterm { background: #efefef url("css/indeterm.gif") repeat-x top; }
		#fprogress p.success { background: #0C0 none 0 0 no-repeat; }
		#fprogress p.failure { background: #F99 none 0 0 no-repeat; }
		img.abortX { float: right; margin-top: -2px; cursor: pointer; }
	</style>
</head>
<body style="font-family: 'Lucida Grande'; font-size: 11px;">
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
		<div>Files left: <span id="count">0</span></div>
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
	</script>
	<script src="js/upload5d.js" type="text/javascript"></script>
</body>
</html>
