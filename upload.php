<?php
error_reporting(-1);
require_once 'functions.php';

function rearrange ($arr)
{
	$new = [];
	foreach ($arr as $key => $all) {
		foreach ($all as $i => $val) {
			$new[$i][$key] = $val;
		}
	}
	return $new;
}

//$l = print_r($_POST, true).print_r($_FILES, true);	//.print_r($upld, true);
//if (!empty($_FILES['user_file'])) $l .= print_r(rearrange($_FILES['user_file']), true);
//file_put_contents('LOG.txt', $l, FILE_APPEND);

$fpath = $_POST['fpath'];

if (empty($_FILES['user_file'])) {
	// process html5/js uploads iincluding chunked
	require_once 'uplodr/upload.php';
	$upld = new Up_Load(['target_dir'=>$baseDir.$fpath]);
} else {
	// process standard/legacy uploads
	echo '<style>
	body {background-color:#E7FFFF;}
	h4 {text-align:center;padding:6px;border:1px solid #999;margin:2px 0;background-color:#FFD;}
	.success {color:#292;}
	.failure {color:#F00;}
</style>';
	$errcnt = 0;
	foreach ($_FILES['user_file']['error'] as $key => $error) {
		if ($error == UPLOAD_ERR_OK) {
			$tmp_name = $_FILES['user_file']['tmp_name'][$key];
			if (is_uploaded_file($tmp_name)) {
				$name = $_FILES['user_file']['name'][$key];
				move_uploaded_file($tmp_name, $baseDir.$fpath.$name);
			} else echo '<p class="failure">Error: failed to upload</p>';
		} elseif ($error !== 4 || $key == 0) {
			echo'<p class="failure">File: '.htmlspecialchars($_FILES['user_file']['name'][$key]).'<br>Error: '.$upld_err_txt[$error].'</p>';
			$errcnt++;
		}
	}

//	echo '<h4 class="failure">=== PSEUDO PROCESSED ===</h4>';
	echo '<h4 class="success">Upload Complete</h4>';
	if (empty($_POST['w'])) {
		echo '<script>window.parent.refreshFilst();</script>';
	} else {
		echo '<script>parent.opener.refreshFilst();</script>';
	}
}