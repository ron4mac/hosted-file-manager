<?php
require_once('functions.php');
$uploadMaxFilesize = ini_get('upload_max_filesize');
$uploadMaxFilesizeBytes = return_bytes($uploadMaxFilesize);
?>
<style>
<?php readfile('css/upload.inc.css'); ?>
</style>
<div style="margin:12px">
<p style="color:red;margin-top:0">Maximum file size: <?php echo $uploadMaxFilesize; ?></p>
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
</div>
<script type="text/javascript">
var filesPath = sessionStorage.fmx_curD;
var uploadMaxFilesize = <?php echo $uploadMaxFilesizeBytes; ?>;
// optional array of allowed mime types
//var fup_ftypes = ['image/jpeg'];
var fup_payload = {'fpath':sessionStorage.fmx_curD, 'oefile':'1'};
function fup_done() { refreshFilst(); }
<?php readfile('js/upload5d.js'); ?>
</script>
