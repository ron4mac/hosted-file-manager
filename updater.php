<?php

function checkForUpdate () {
	global $fmxVersion;
	//echo $fmxVersion;
	$fv = substr($fmxVersion, 0, strpos($fmxVersion,' '));
	//echo "$fv\n";
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
	//echo $which;
	$utim = time();
	//echo "Downloading New Update\n";
	$newUpdate = file_get_contents('http://roncrans.net/mydev/FMX/fmx.'.$which.'.zip');
	$dlHandler = fopen($baseDir.'tmp/fmx_upd_'.$utim.'.zip', 'w');
	if (!fwrite($dlHandler, $newUpdate)) { echo 'Could not save new update. Operation aborted.'; return; }
	fclose($dlHandler);
	//echo 'Update Downloaded And Saved';
	$cmds = "cd ../;
mv fmx sv_fmx;
unzip -qq ${baseDir}tmp/fmx_upd_${utim}.zip;";
	system($cmds,$rslt);
	if ($rslt !== 0) {
		echo 'Failed to unzip update.';
		return;
	}
}

?>