<?php

define('GH_PAT', 'GH_PAT');

require_once 'functions.php';
$gitdir = $baseDir . urldecode($_GET['dir']);
chdir($gitdir);

$cookT = time()+86400*256;		//cookie to expire in 256 days

$gh_auth = isset($_COOKIE[GH_PAT]) ? $_COOKIE[GH_PAT] : null;
if ($gh_auth) setcookie(GH_PAT, $gh_auth, $cookT);	//refresh the cookie

function getUrl ($wc=false)
{
	global $gh_auth, $msg;

	$cfgf = file_get_contents('.git/config');
	if (!$cfgf) { $msg = 'config file is missing'; return; }
	$lins = explode("\n", $cfgf);
	foreach ($lins as $lin) {
		if (preg_match('#^\surl = (.*)$#', $lin, $mtch)) {
			if (!$wc) return $mtch[1];
			return str_replace('://', '://'.urlencode(trim($_POST['user'])).':'.urlencode(trim($gh_auth)).'@', $mtch[1]);
		}
	}
	$msg = 'pattern not found';
}

$msg = ''; $rslt = '';

if (isset($_GET['dnld'])) {
	$brch = `git rev-parse --abbrev-ref HEAD`;
	if (!($std = sys_get_temp_dir())) {
		header($_SERVER["SERVER_PROTOCOL"]." 404 No temporary directory");
		exit;
	}
	$temp_file = tempnam($std, 'GIT');
	$rslt = `git archive --format zip -o {$temp_file} {$brch}`;
	if (file_exists($temp_file)) {
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.basename($gitdir).'.zip');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($temp_file));
		@ob_clean();
		@flush();
		readfile($temp_file);
		@unlink($temp_file);
		exit;
	}
	header($_SERVER["SERVER_PROTOCOL"]." 404 No acrhive created");
	exit;
}

if (isset($_POST['setuser'])) {
	$rslt = `git config --local user.name {$_POST['uname']}`;
	$rslt = `git config --local user.email {$_POST['uemail']}`;
}

if (isset($_POST['act'])) {
	$file = $_POST['f'];
	switch ($_POST['act']) {
		case 'rev':
			$rslt = `git checkout {$file}`;
			break;
		case 'del':
			$rslt = `git rm -r {$file}`;
			@unlink($file);
			break;
		case 'dif':
			$rslt = str_replace('xmp>','.xmp>',`git diff -w {$file}` ?? '[ NO DIFFERENCES ]');
			break;
		case 'und':
			$rslt = `git reset HEAD~1`;
			break;
		case 'uns':
			$rslt = `git restore --staged {$file}`;
			break;
		case 'spat':
			setcookie(GH_PAT, $file, $cookT);
			break;
	}
}

if (isset($_POST['commit'])) {
	if (isset($_POST['COS'])) {
		foreach ($_POST['ftc'] as $ftc) {
			$rslt .= `git add "{$ftc}"`;
		}
		$rslt .= `git commit -m "{$_POST['cmmsg']}"`;
	} else {
		$rslt .= `git add .`;
		$rslt .= `git commit -a -m "{$_POST['cmmsg']}"`;
	}
}

if (isset($_POST['pull'])) {
	$remote = getUrl(!empty($_POST['user']));
	if ($remote) $rslt .= `git pull --rebase {$remote}`;
}

if (isset($_POST['push'])) {
	$remote = getUrl(true);
	$brchsel = trim($_POST['brchsel']);
	if ($remote && $brchsel) {
		if (isset($_POST['force'])) {
			$rslt .= `git push --force {$remote} {$brchsel}`;
		} else {
			$rslt .= `git push {$remote} {$brchsel}`;
		}
	}
}

$uname = `git config --local user.name`;
$brch = `git branch`;

if ($brch) {
	$bchs = explode("\n", trim($brch));
	$opts = '';
	foreach ($bchs as $bch) {
		$opts .= '<option value="'.substr($bch,2).'"'.($bch[0]=='*'?' selected':'');
		$opts .= '>'.$bch.'</option>';
	}
} else {
	$opts = '<option value="master">master</option>';
}

function ckBox ($v, $n=null)
{
	return '<input type="checkbox"'.($n ? (' name="'.$n.'"') : '').' value="'.$v.'"> ';
}

function statusAction ()
{
	$stats = explode("\x00", `git status -z` ?: '');
	$html = '';
	foreach ($stats as $stat) {
		if (!$stat) continue;
		$m = substr($stat, 0, 2);
		$f = substr($stat, 3);
		switch ($m) {
			case ' M':
				$actdif = ['revert',' <a href="javascript:postAct({act:\'dif\', f: \''.urlencode($f).'\'})">diff</a>'];
			case ' D':
				if ($m==' D') $actdif = ['restore',''];
				$html .= ckBox($f, 'ftc[]').'<a class="fact" href="javascript:postAct({act:\'rev\', f: \''.urlencode($f).'\'})">'.$actdif[0].'</a> '.$f.$actdif[1].'<br>';
				break;
			case 'M ':
				$html .= $f.' <a href="javascript:postAct({act:\'uns\', f: \''.urlencode($f).'\'})">unstage</a><br>';
				break;
			case '??':
				$html .= ckBox($f, 'ftc[]').'<a href="javascript:postAct({act:\'del\', f: \''.urlencode($f).'\'})">delete</a> '.$f.'<br>';
				break;
			default:
				$html .= bin2hex($m) . " $f<br>";
				break;
		}
	}
	return $html ?: '<a href="javascript:postAct({act:\'und\', f: \' \'})">Undo Last Commit</a><br>';
}
header('Cache-Control: no-cache');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US" xml:lang="en-US">
<head>
	<title>Gitter [ GitHub Sync ]></title>
	<meta charset="UTF-8">
	<meta http-equiv="Content-Language" content="en">
	<meta name="google" content="notranslate">
	<script type="text/javascript">
	function postAct (params) {
		var form = document.createElement("form");
		form.setAttribute("method", "post");
		for (var key in params) {
			if (params.hasOwnProperty(key)) {
				var hiddenField = document.createElement("input");
				hiddenField.setAttribute("type", "hidden");
				hiddenField.setAttribute("name", key);
				hiddenField.setAttribute("value", params[key]);
				form.appendChild(hiddenField);
			}
		}
		document.body.appendChild(form);
		form.submit();
	}
	function doDnld () {
		var dlframe = document.createElement("iframe");
		// set source to desired file
		dlframe.src = "gitter.php?<?=$_SERVER['QUERY_STRING']?>&dnld=1";
		// This makes the IFRAME invisible to the user.
		dlframe.style.display = "none";
		// Add the IFRAME to the page.  This will trigger the download
		document.body.appendChild(dlframe);
		//console.log(data); //alert("Data Loaded: " + data);
		return false;
	}
	function getsetpat () {
		var pat = prompt("Github Personal Access Token", "");
		if (pat) postAct({act:'spat', f:pat});
		return false;
	}
	function chkCommit (evt) {
		let ftcs = document.querySelectorAll('input[name="ftc[]"]:checked');
		if (ftcs.length && !document.forms.gitsync.COS.checked) {
			alert("Need to indicate a partial commit");
			evt.preventDefault();
			evt.stopPropagation();
		}
	}
	</script>
	<style>
		/*.syncform {line-height:1.5em;}*/
		.userform {line-height:1.5em; border:1px dotted #AAA; background-color:#FEE; padding:6px;}
		.fact {display: inline-block; width: 3rem; text-align: right;}
		.cmmsg {width:40em;}
		.rslt {border:1px solid #CCC; background-color:#FEF; padding:0 6px;}
		.stat {border:1px solid #CCC; background-color:#FFE; padding:0 6px 10px 6px;}
		.cnfg {border:1px solid #CCC; background-color:#EFF; padding:0 6px;}
		.remo {border:1px solid #CCC; background-color:#F0F0F0; padding:0 6px; line-height:1.5em;}
		xmp {margin:4px 0;}
	</style>
</head>
<body>
	<a href="https://www.atlassian.com/git/tutorials" target="_blank" style="float:right">Tutor</a>
	<?php if (!$uname): ?>
	<form name="gituser" class="userform" method="post">
		User Name: <input type="text" id="uname" name="uname" value="ron4mac">
		<br>Email Addr: <input type="text" id="uemail" name="uemail" value="ron@rjconline.net">
		<br><input type="submit" name="setuser" value="Set User Config">
	</form>
	<?php endif; ?>
	<?php if ($msg) { echo $msg.'<br>'; } ?>
	<form name="gitsync" class="syncform" method="post">
	Branch: <select name="brchsel"><?php echo $opts; ?></select>
	<button onclick="return doDnld()" title="Download the archive zip from Github">Download</button>
	<?php if ($rslt): ?>
	<br><br>Result
	<div class="rslt">
		<xmp><?php echo $rslt; ?></xmp>
	</div>
	<?php else: ?>
	<br>
	<?php endif; ?>
	<br>Status
	<div class="stat">
		<xmp><?php echo `git fetch origin; git status`; ?></xmp>
		<?php $sact = statusAction(); echo $sact; ?>
	</div>
	<?php if($sact): ?>
	<input type="submit" name="commit" value="Commit" onclick="chkCommit(event)"> &nbsp;&nbsp;<?=ckBox('1', 'COS')?> Only selected &nbsp;&nbsp;Msg: <input type="text" id="cmmsg" name="cmmsg" class="cmmsg"><br>
	<?php endif; ?>
	<br>Remote
	<div class="remo">
		<input type="submit" name="pull" value="Pull">
		<div class="push">
			User: <input type="text" id="user" name="user">
			<?php if (!$gh_auth): ?>
			<br><a href="#" onclick="return getsetpat();" style="background-color:coral;padding:4px">Set your Personal Access Token</a>
			<?php endif; ?>
			<br><input type="submit" name="push" value="Push"> &nbsp;&nbsp;&nbsp;<input type="checkbox" name="force">-Force!
		</div>
	</div>
	</form>
	<br>Config
	<div class="cnfg">
		<xmp><?php echo `git config -l`; ?></xmp>
	</div>
</body>
</html>
