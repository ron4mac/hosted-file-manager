<?php

function checkForUpdate () {
	global $fmxVersion;
	$fv = substr($fmxVersion, 0, strpos($fmxVersion,' '));
	$getVersions = file_get_contents('http://roncrans.net/mydev/FMX/versions.php') or die ('ERROR');
	if ($getVersions) {
		$vers = explode("\n", trim($getVersions));
		sort($vers);
		foreach ($vers as $ver) {
			if ($ver > $fv) return array_pop($vers);
		}
	}
	return false;
}

function performUpdate ($which) {
	global $baseDir;
	$tmpf = $baseDir.'tmp/fmx_upd_'.time().'.zip';
	$newUpdate = file_get_contents('http://roncrans.net/mydev/FMX/fmx.'.$which.'.zip');
	$dlHandler = fopen($tmpf, 'w');
	if (!fwrite($dlHandler, $newUpdate)) { echo 'Could not save new update. Operation aborted.'; return; }
	fclose($dlHandler);
	$cmds = "cd ../;
mv fmx sv_fmx;
unzip -qq ${tmpf};";
	system($cmds,$rslt);
	if ($rslt !== 0) {
		echo 'Failed to unzip update.';
		return;
	}
}

?>