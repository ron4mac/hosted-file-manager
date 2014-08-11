<?php
error_reporting(-1);
require_once('functions.php');

if ($_FILES['user_file'] && isset($_POST['fpath'])) {
	$msg = 'Ok';
	$fpath = $_POST['fpath'];		//file_put_contents('uplog.txt', $baseDir."$fpath\n", FILE_APPEND);	file_put_contents('uplog.txt', print_r($_FILES, true)."\n", FILE_APPEND);
	foreach ($_FILES['user_file']['error'] as $key => $error) {
		if ($error == UPLOAD_ERR_OK) {
			$tmp_name = $_FILES['user_file']['tmp_name'][$key];
			if (is_uploaded_file($tmp_name)) {
				$name = $_FILES['user_file']['name'][$key];
				if (!isset($_POST['oefile'])) {
					$name = preg_replace('#["*/:<>?\|]+#', '_', $name);
					$parts = pathinfo($name);
					$uniq = $parts['filename'];
					$ext = $parts['extension'] ? ".{$parts['extension']}" : '';
					$n = 1;
					while (file_exists($baseDir.$fpath.$uniq.$ext)) {
						$uniq = $parts['filename'] . '_' . $n++;
					}
					$name = $uniq.$ext;
				}
				move_uploaded_file($tmp_name, $baseDir.$fpath.$name);
			} else $msg .= 'failed to upload';
		} else $msg .= "Error: $error";
	}
	exit($msg);
}

?>