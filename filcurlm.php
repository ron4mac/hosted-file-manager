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
		<div class="ffld"><label>URL: <input type="text" name="url" id="curlurl" style="width:100%;box-sizing:border-box" value="<?=$curl_url?>" required /></label></div>
		<div class="ffld">
			<label>user: <input type="text" name="user" style="width:30%" value="" /></label>
			<label>pass: <input type="text" name="pass" style="width:30%" value="" /></label>
		</div>
		<br /><hr /><input type="button" name="do_upload" value="<?=$btnttl?>" onclick="chknsend()" style="float:right;margin-bottom:12px" /><img id="curlspin" src="graphics/spinner.gif" style="float:right;display:none" />
	</form>
</div>
<script type="text/javascript">
<?php if ($isTo): ?>
	var slctd = _rj.qs('.fsel:checked');
	_rj.id('curlurl').value = slctd.closest('[data-fref]').dataset.fref;
<?php endif; ?>
	_rj.id('up_fpath').value = sessionStorage.fmx_curD;
	function chknsend () {
		let frm = document.upform;
		let wurl = frm.url.value.trim();
		if (!wurl) { alert("Please enter a valid url"); return; }
		let parms = {
			act: '<?=$fmxact?>',
			path: curDir,
			url: wurl
			};
		let userpass = frm.user.value.trim();
		if (userpass) parms.user = userpass;
		userpass = frm.pass.value.trim();
		if (userpass) parms.pass = userpass;
		postAndRefresh(parms, ()=>{_rj.id('curlspin').style.display = 'none'});
		frm.do_upload.disabled = true;
		_rj.id('curlspin').style.display = 'inline-block';
	}
</script>
