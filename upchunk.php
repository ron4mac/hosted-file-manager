<?php
error_reporting(-1);
require_once('functions.php');
include 'cfg.php';
$utmpdir = dirname($_SERVER['DOCUMENT_ROOT']).'/tmp/';

////////////////////////////////////////////////////////////////////
// THE FUNCTIONS
////////////////////////////////////////////////////////////////////

/**
 * Logging operation - to a file (upload_log.txt) and to the stdout
 * @param string $str - the logging string
 */
function _log($str) {
	global $dev_mode;
	if (!$dev_mode) return;
	// log to the output
	$log_str = date('d.m.Y').": {$str}\r\n";
	echo $log_str;
	// log to file
	if (($fp = fopen('upload_log.txt', 'a+')) !== false) {
		fputs($fp, $log_str);
		fclose($fp);
	}
}

/**
 * Delete a directory RECURSIVELY
 * @param string $dir - directory path
 * @link http://php.net/manual/en/function.rmdir.php
 */
function rrmdir($dir) {
	if (is_dir($dir)) {
		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (filetype($dir . "/" . $object) == "dir") {
					rrmdir($dir . "/" . $object); 
				} else {
					unlink($dir . "/" . $object);
				}
			}
		}
		reset($objects);
		rmdir($dir);
	}
}

/**
 * Check if all the parts exist, and 
 * gather all the parts of the file together
 * @param string $dir - the temporary directory holding all the parts of the file
 * @param string $fileName - the original file name
 * @param string $chunkSize - each chunk size (in bytes)
 * @param string $totalSize - original file size (in bytes)
 */
function createFileFromChunks($temp_dir, $fpath, $fileName, $chunkSize, $totalSize) {
	global $baseDir;
	// count all the parts of this file
	$total_files = 0;
	foreach(scandir($temp_dir) as $file) {
		if (stripos($file, $fileName) !== false) {
			$total_files++;
		}
	}

	// check that all the parts are present
	// the size of the last part is between chunkSize and 2*$chunkSize
	if ($total_files * $chunkSize >=  ($totalSize - $chunkSize + 1)) {

		// create the final destination file 
		if (($fp = fopen($baseDir.$fpath.$fileName, 'w')) !== false) {
			for ($i=1; $i<=$total_files; $i++) {
				fwrite($fp, file_get_contents($temp_dir.'/'.$fileName.'.part'.$i));
				_log('writing chunk '.$i);
			}
			fclose($fp);
		} else {
			_log('cannot create the destination file: '.$baseDir.' : '.$fpath.' : '.$fileName);
			return false;
		}

		// rename the temporary directory (to avoid access from other 
		// concurrent chunks uploads) and then delete it
		if (rename($temp_dir, $temp_dir.'_UNUSED')) {
			rrmdir($temp_dir.'_UNUSED');
		} else {
			rrmdir($temp_dir);
		}
	}

}


////////////////////////////////////////////////////////////////////
// THE SCRIPT
////////////////////////////////////////////////////////////////////

//check if request is GET and the requested chunk exists or not. this makes testChunks work
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['resumableChunkNumber'])) {

	$temp_dir = $utmpdir.$_GET['resumableIdentifier'];
	$chunk_file = $temp_dir.'/'.$_GET['resumableFilename'].'.part'.$_GET['resumableChunkNumber'];
	if (file_exists($chunk_file)) {
		header("HTTP/1.0 200 Ok");
	} else {
		header("HTTP/1.0 404 Not Found");
	}
	exit();
}

// loop through files and move the chunks to a temporarily created directory
if (!empty($_FILES)) foreach ($_FILES as $file) {

	// check the error status
	if ($file['error'] != 0) {
		_log('error '.$file['error'].' in file '.$_POST['resumableFilename']);
		continue;
	}

	// init the destination file (format <filename.ext>.part<#chunk>
	// the file is stored in a temporary directory
	$temp_dir = $utmpdir.$_POST['resumableIdentifier'];
	$dest_file = $temp_dir.'/'.$_POST['resumableFilename'].'.part'.$_POST['resumableChunkNumber'];

	// create the temporary directory
	if (!is_dir($temp_dir)) {
		mkdir($temp_dir, 0777, true);
	}

	// move the temporary file
	if (!move_uploaded_file($file['tmp_name'], $dest_file)) {
		_log('Error saving (move_uploaded_file) chunk '.$_POST['resumableChunkNumber'].' for file '.$_POST['resumableFilename']);
	} else {
		// check if all the parts present, and create the final destination file
		createFileFromChunks($temp_dir, $_POST['fpath'], $_POST['resumableFilename'], $_POST['resumableChunkSize'], $_POST['resumableTotalSize']);
	}
	exit();
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Chunky Resumable Uploader</title>
	<link rel="stylesheet" type="text/css" href="css/fmx.css" />
	<link rel="stylesheet" type="text/css" href="css/upload.css" />
	<script src="js/resumable<?=$jsver?>.js"></script>
</head>
<body>
<a href="#" id="browseButton">Select files</a>
<div id="dropArea">Or drop files here</div>
<div id="fprogress"></div>
<script>
var r = new Resumable({
		chunkSize: 2*1024*1024,
		target: 'upchunk.php',
		query: { fpath: sessionStorage.fmx_curD }
	}),
	err_count = 0;
  
r.assignBrowse(document.getElementById('browseButton'));
r.assignDrop(document.getElementById('dropArea'));

r.on('fileSuccess', function(file){
	var fpdv = document.getElementById('fprogress');
	fpdv.removeChild(file.fpp);
    //console.debug(file);
  });
r.on('fileProgress', function(file){
	var fp = file.progress();
	var p = Math.floor(file.progressW * fp);
	file.fpp.style.backgroundPosition = p + "px 0";
	if (fp==1) file.fpp.className = 'indeterm';
    //console.debug(file);
  });
r.on('fileAdded', function(file, event){
	var fpdv = document.getElementById('fprogress');
    var fp = document.createElement("P");
    fp.innerHTML = file.fileName;
    file.fpp = fp;
    fpdv.appendChild(fp);
    file.progressW = fp.offsetWidth;
    r.upload();
    console.debug(file, event);
  });
r.on('filesAdded', function(array){
    //console.debug(array);
  });
r.on('fileRetry', function(file){
    //console.debug(file);
  });
r.on('fileError', function(file, message){
	err_count++;
    //console.debug(file, message);
  });
r.on('uploadStart', function(){
    //console.debug();
  });
r.on('complete', function(){
	//document.write("=");
    //console.debug();
    parent.opener.refreshFilst(); if (!err_count) window.close();
  });
r.on('progress', function(){
    //console.debug();
  });
r.on('error', function(message, file){
    console.debug(message, file);
  });
r.on('pause', function(){
    //console.debug();
  });
r.on('cancel', function(){
    //console.debug();
  });
</script>
</body>
</html>