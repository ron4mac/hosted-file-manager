<?php
require_once('functions.php');
$gitdir = $baseDir . urldecode($_GET['dir']);
chdir($gitdir);
$msg = ''; $rslt = '';
if (isset($_POST['setuser'])) {
	$rslt = `git config --local user.name {$_POST['uname']}`;
	$rslt = `git config --local user.email {$_POST['uemail']}`;
}
if (isset($_POST['act'])) {
	switch ($_POST['act']) {
		case 'rev':
			$rslt = `git checkout {$_POST['f']}`;
			break;
		case 'del':
			$rslt = `git rm -r {$_POST['f']}`;
			break;
	}
}

if (isset($_POST['commit'])) {
	$rslt .= `git add .`;
	$rslt .= `git commit -a -m "{$_POST['cmmsg']}"`;
}

if (isset($_POST['pull'])) $rslt .= `git pull --rebase`;

if (isset($_POST['push'])) {
	while (true) {
		$cfgf = file_get_contents('.git/config');
		if (!$cfgf) { $msg = 'config file is missing'; break; }
		$ncfg = $cfgf;
		$ptrn = '#\[remote "([^"]+)"\][^\[]+\t+url = https://#';
		if (!preg_match($ptrn, $ncfg, $mtchs)) { $msg = 'pattern not found'; break; }
		//var_dump($mtchs);
		$remote = $mtchs[1];
		$brchsel = trim($_POST['brchsel']);
		$ncfg = preg_replace('#url = https://#', 'url = https://'.$_POST['user'].':'.$_POST['pass'].'@', $ncfg, 1);
		file_put_contents('.git/config', $ncfg);
		if (isset($_POST['force'])) {
			$rslt .= `git push --force {$remote} {$brchsel}`;
		} else {
			$rslt .= `git push {$remote} {$brchsel}`;
		}
		file_put_contents('.git/config', $cfgf);
		break;
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



function statusAction ()
{
	$stats = explode("\x00", `git status -z`);
	$html = '';
	foreach ($stats as $stat) {
		if (!$stat) continue;
		$m = substr($stat, 0, 2);
		$f = substr($stat, 3);
	//	list($m, $f, $ff) = explode(' ', $stat.'', 3);
		switch ($m) {
			case ' M':
				$html .= '<a href="javascript:postAct({act:\'rev\', f: \''.urlencode($f).'\'})">revert</a> '.$f.'<br />';
				break;
			case '??':
				$html .= '<a href="?act=del&f='.urlencode($f).'">delete</a> '.$f.'<br />';
				break;
			default:
				$html .= bin2hex($m) . " $f<br />";
				break;
		}
	}
	return $html;
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US" xml:lang="en-US">
<head>
	<title>Gitter [ GitHub Sync ]></title>
	<meta charset="UTF-8">
	<meta http-equiv="Content-Language" content="en" />
	<meta name="google" content="notranslate">
	<script type="text/javascript">
	function postAct (params)
	{
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
	</script>
	<style>
		/*.syncform {line-height:1.5em;}*/
		.userform {line-height:1.5em; border:1px dotted #AAA; background-color:#FEE; padding:6px;}
		.cmmsg {width:40em;}
		.rslt {border:1px solid #CCC; background-color:#FEF; padding:0 6px}
		.stat {border:1px solid #CCC; background-color:#FFE; padding:0 6px 10px 6px}
		.cnfg {border:1px solid #CCC; background-color:#EFF; padding:0 6px}
		.remo {border:1px solid #CCC; background-color:#F0F0F0; padding:0 6px; line-height:1.5em;}
	</style>
</head>
<body>
	<a href="https://www.atlassian.com/git/tutorials" target="_blank" style="float:right">Tutor</a>
	<?php if (!$uname): ?>
	<form name="gituser" class="userform" method="post">
		User Name: <input type="text" id="uname" name="uname" value="ron4mac" />
		<br />Email Addr: <input type="text" id="uemail" name="uemail" value="ron@rjconline.net" />
		<br /><input type="submit" name="setuser" value="Set User Config" />
	</form>
	<?php endif; ?>
	<?php if ($msg) { echo $msg.'<br />'; } ?>
	<form name="gitsync" class="syncform" method="post">
	Branch: <select name="brchsel"><?php echo $opts; ?></select>
	<?php if ($rslt): ?>
	<br /><br />Result
	<div class="rslt">
		<xmp><?php echo $rslt; ?></xmp>
	</div>
	<?php else: ?>
	<br />
	<?php endif; ?>
	<br />Status
	<div class="stat">
		<xmp><?php echo `git fetch origin; git status`; ?></xmp>
		<?php echo statusAction(); ?>
	</div>
	<input type="submit" name="commit" value="Commit" /> &nbsp;&nbsp;Msg: <input type="text" id="cmmsg" name="cmmsg" class="cmmsg" />
	<br /><br />Remote
	<div class="remo">
		<input type="submit" name="pull" value="Pull" />
		<div class="push">
			User: <input type="text" id="user" name="user" />
			<br />Password: <input type="text" id="pass" name="pass" />
			<br /><input type="submit" name="push" value="Push" /> &nbsp;&nbsp;&nbsp;<input type="checkbox" name="force" />-Force!
		</div>
	</div>
	</form>
	<br />Config
	<div class="cnfg">
		<xmp><?php echo `git config -l`; ?></xmp>
	</div>
</body>
</html>
