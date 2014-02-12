<?php
require_once('functions.php');
$fpath = escapeshellcmd($_GET['path']);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>HTML5 Multi-file Upload</title>
		<script src="js/jquery.min.js" type="text/javascript"></script>
		<script src="html5upload.js" type="text/javascript"></script>
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
		<style>
			#dropArea { border: 1px dashed #CCC; width: 100%; height: 30px; text-align: center; vertical-align: middle; }
			#dropArea.hover { background-color: #CCC; }
		</style>
	</head>
	<body style="font-family: 'Lucida Grande'; font-size: 11px;">
		<p style="color:red">Maximum file size: <?php echo ini_get('post_max_size') ?></p>
		<input type="hidden" name="MAX_FILE_SIZE" value="10" />
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
			<div id="server_response"></div>
		</div>
		<script type="text/javascript">
			var filesPath = "<?php echo $fpath; ?>";
			$(function() {
				$("#upload_field").html5_upload({
					url: function(number) { return "upload5.php"; },
					sendBoundary: window.FormData || $.browser.mozilla,
					onStart: function(event, total) { return confirm("You are trying to upload " + total + " files. Are you sure?"); },
					setName: function(text) { $("#progress_report_name").text(text); },
					//setStatus: function(text) { $("#progress_report_status").text(text); },
					setProgress: function(val) { $("#progress_report_bar").css('width', Math.ceil(val*100)+"%"); },
					setTotalProgress: function(val) { $("#totprogress_report_bar").css('width', Math.ceil(val*100)+"%"); },
					onFinishOne: function(event, response, name, number, total) { $("#server_response").text(response); },
					onFinish: function(event, total) { parent.opener.refreshFilst(); }
				});
			});
		</script>
	</body>
</html>
