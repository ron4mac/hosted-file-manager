<?php

function curly ($url,$agent) {
	// create curl resource
	$ch = curl_init();
	// set url
	curl_setopt($ch, CURLOPT_URL, $url);
	//return the transfer as a string
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, $agent);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	// $output contains the output string
	$output = curl_exec($ch);
	// close curl resource to free up system resources
	curl_close($ch);
	// return the data
	return $output;
}

function getUrlData ($url) {
	global $github_credential;	// optional and set in 'fmx.ini' file as <username>:<password>
	if ($github_credential) {
		$url = str_replace('//','//'.$github_credential.'@',$url);
	}
	$ua = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.75.14 (KHTML, like Gecko) Version/7.0.3 Safari/537.75.14';
	if (ini_get('allow_url_fopen')) {
		ini_set('user_agent', $ua);
		$rfc = file_get_contents($url);
		if (!$rfc) return curly($url, $ua);
		return $rfc;
	} else {
		return curly($url, $ua);
	}
}

function checkForUpdate () {
	global $fmxVersion;
	$fv = substr($fmxVersion, 0, strpos($fmxVersion,' '));
	// get release list from github (ignore errors in case of refused access)
	$fmx_releases = json_decode(@getUrlData('https://api.github.com/repos/ron4mac/hosted-file-manager/releases'));
	if (!$fmx_releases) { echo 'Release of FMX not found at Github'; exit(); }
	$fmx_ball = $fmx_releases[0]->zipball_url;
	$fmx_name = $fmx_releases[0]->name;
	$uver = $fmx_releases[0]->tag_name;
	if (preg_match('/\d+\.\d+\.\d+/',$uver,$m)) {
		if (version_compare($m[0], $fv) == 1)
		return $uver.'|'.$fmx_ball;
	}
	return false;
}

function performUpdate ($which) {
	global $baseDir;
	$tmpf = (sys_get_temp_dir()?sys_get_temp_dir():$baseDir.'tmp').'/fmx_upd_'.time().'.zip';
	$newUpdate = getUrlData($which);
	$dlHandler = fopen($tmpf, 'w');
	if (!fwrite($dlHandler, $newUpdate)) { echo 'Could not save new update. Operation aborted.'; return; }
	fclose($dlHandler);
	installUpdate($tmpf);
	unlink($tmpf);
}

function logIt ($msg) {
	file_put_contents('log.txt', "{$msg}\n", FILE_APPEND);
}

function installUpdate ($fpath) {	// NEED TO GET PATH TO INSTALL
	$f2p = array('.user.ini','fmx.ini');	//files to preserve if they already exist
	$zip = new ZipArchive;
	$res = $zip->open($fpath);
	if ($res === TRUE) {
		for ($i = 0; $i < $zip->numFiles; $i++ ) {
			$stat = $zip->statIndex( $i );
			//echo $stat['name'] . '<br />';
			list($bd,$fp) = explode('/', $stat['name'], 2);
			if (substr($fp, -1) == '/') {
				@mkdir($fp);
				//logIt("DIR: {$fp}");
			} elseif ($fp) {
				if (in_array($fp, $f2p) && file_exists($fp)) continue;
				$fc = $zip->getFromIndex($i);
				file_put_contents($fp, $fc);
				//logIt("PUT: {$fp}");
			}
		}
		$zip->close();
	} else {
		echo 'failed, code:' . $res;
	}
}

?>