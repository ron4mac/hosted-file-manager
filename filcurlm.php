<?php
require_once 'functions.php';
$isTo = isset($_GET['t']) && $_GET['t'];
$curl_msg = $isTo ? 'Send a file to a web location' : 'Get a file from a web location';
$btnttl = $isTo ? 'Send file' : 'Get file';
$fmxact = $isTo ? 'crlp' : 'crlg';
$curl_url = '';
?>
<div id="uplfrm" style="margin:12px">
	<p><?=$curl_msg?></p>
	<form id="upform" name="upform" method="post">
		<input type="hidden" id="up_fpath" name="up_fpath" value="" />
		<label>URL: </label><input type="text" name="url" id="curlurl" style="width:100%;box-sizing:border-box" value="<?=$curl_url?>" />
		<label>user: </label><input type="text" name="user" style="width:30%" value="" />
		<label>pass: </label><input type="text" name="pass" style="width:30%" value="" />
		<br /><hr /><input type="button" name="do_upload" value="<?=$btnttl?>" onclick="chknsend()" style="float:right;margin-bottom:12px" /><img id="curlspin" src="graphics/spinner.gif" style="float:right;display:none" />
	</form>
</div>
<script type="text/javascript">
	$(function() {
<?php if ($isTo): ?>
		var slctd = $(".fsel:checked");
		$('#curlurl').val($(slctd[0]).parents('tr').attr('data-fref'));
<?php endif; ?>
		$('#up_fpath').val(sessionStorage.fmx_curD);
	});
	function chknsend () {
		var frm = document.upform;
		var wurl = frm.url.value.trim();
		if (!wurl) { alert("Please enter a valid url"); return; }
		var parms = {
			act: '<?=$fmxact?>',
			path: curDir,
			url: wurl
			};
		var userpass = frm.user.value.trim();
		if (userpass) parms.user = userpass;
		userpass = frm.pass.value.trim();
		if (userpass) parms.pass = userpass;
		postAndRefresh(parms);
		frm.do_upload.disabled = true;
		$('#curlspin').show();
	}
</script>
