<?php
require_once('functions.php');
include 'cfg.php';
$uploadMaxFilesize = ini_get('upload_max_filesize');
$uploadMaxFilesizeBytes = return_bytes($uploadMaxFilesize);
?>
<link rel="stylesheet" type="text/css" href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/css/upload.css" />
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
	Files in queue: <span id="qCount">0</span><div class="acti" id="qstop"><img src="css/stop.png" title="stop queue" onclick="fupQctrl.stop()" /></div><div class="acti" id="qgocan"><img src="css/play-green.png" title="resume queue" onclick="fupQctrl.go()" /><img src="css/cross.png" title="cancel queue" onclick="fupQctrl.cancel()" /></div>
	<div id="fprogress"></div>
	<div id="server_response"></div>
</div>
</div>
<script type="text/javascript">
var filesPath = sessionStorage.fmx_curD;
var uploadMaxFilesize = <?php echo $uploadMaxFilesizeBytes; ?>;
var h5_fup = {
	lang: {abortd:'-- aborted', noupld:'Could not upload', toobig:'File is larger than max size allowed.', notype:'Cannot upload a file of this type.'},
	// optional array of allowed mime types
	//ftypes: ['image/jpeg'],
	payload: {'fpath':sessionStorage.fmx_curD, 'oefile':'1'},
	done: function (errcnt) { refreshFilst(); }
};
<?php readfile('js/upload5d'.$jsver.'.js'); ?>
</script>
