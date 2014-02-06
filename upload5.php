<?php
require_once('functions.php');

if ($_FILES['user_file'] && isset($_POST['fpath'])) {
	$msg = 'Ok';
	$fpath = $_POST['fpath'];
	foreach ($_FILES['user_file']['error'] as $key => $error) {
		if ($error == UPLOAD_ERR_OK) {
			$tmp_name = $_FILES['user_file']['tmp_name'][$key];
			if (is_uploaded_file($tmp_name)) {
				$name = $_FILES['user_file']['name'][$key];
				move_uploaded_file($tmp_name, $baseDir.$fpath.$name);
				}
			else $msg .= 'failed to upload';
			}
		else $msg .= "Error: $error";
		}
	exit($msg);
	}

if(isset($_GET['upload'],$_GET['path']) && $_GET['upload'] === 'true'){
	print_r($_FILES);exit;
//	$headers = getallheaders();
	$errmsg = 'header problem';
	if(
		// basic checks
/*		isset(
			$headers['Content-Type'],
			$headers['Content-Length'],
			$headers['X-File-Size'],
			$headers['X-File-Name']
		) &&
		$headers['Content-Type'] === 'multipart/form-data' &&
		$headers['Content-Length'] === $headers['X-File-Size']
*/
		isset(
			$_SERVER['CONTENT_TYPE'],
			$_SERVER['CONTENT_LENGTH'],
			$_SERVER['HTTP_X_FILE_SIZE'],
			$_SERVER['HTTP_X_FILE_NAME']
		) &&
		preg_match('|^multipart/form-data|',$_SERVER['CONTENT_TYPE']) &&
		(($_SERVER['CONTENT_LENGTH'] - $_SERVER['HTTP_X_FILE_SIZE']) < 300)
	){
		$errmsg = 'file save problem';
		// create the object and assign property
		$file = new stdClass;
//		$file->name = basename($headers['X-File-Name']);
//		$file->size = $headers['X-File-Size'];
		$file->name = basename($_SERVER['HTTP_X_FILE_NAME']);
		$file->size = $_SERVER['HTTP_X_FILE_SIZE'];
		//$file->content = file_get_contents("php://input");
		$file->content = $HTTP_RAW_POST_DATA;

		// if everything is ok, save the file somewhere
		$path = $_GET['path'];
		$errmsg = 'file_put problem';
		echo $baseDir.$path.$file->name;echo strlen($file->content);
		if(file_put_contents($baseDir.$path.$file->name, $file->content)) {
			exit('OK');
			}
	}

	// if there is an error this will be the output instead of "OK"
	exit('Error: '.$errmsg);
}
if(isset($_GET['path'])) {
	$locparms = '&path='.$_GET['path'];
	}
define('PRGWID',400);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>HTML5 Multiple File Upload With Progress Bar</title>
<style type="text/css">
* {
	font-family: Verdana, Helvetica, sans-serif;
	font-size: 8pt;
}
div {
	margin-top: 4px;
}
.progress {
	width: <?php echo PRGWID; ?>px;
	border: 1px solid #BBB;
	background-color: #FFF;
	padding: 0;
}
.progress span {
	display: block;
	width: 0px;
	height: 10px;
	background-color: #BBF;
}
#from {
	position: absolute;
	top: 120px;
}
</style>
<script type="text/javascript" src="sendFile.js"></script>
<script type="text/javascript">
	onload = function() {

		function size(bytes) {	// simple function to show a friendly size
			var i = 0;
			while (1023 < bytes){
				bytes /= 1024;
				++i;
			};
			return i ? bytes.toFixed(2) + ["", " Kb", " Mb", " Gb", " Tb"][i] : bytes + " bytes";
		};

		// create elements
		var input = document.body.appendChild(document.createElement("input")),
			//mxm = document.body.appendChild(document.createElement("div"));
			divs = document.body.appendChild(document.createElement("div")),
			sub = document.body.appendChild(document.createElement("div")).appendChild(document.createElement("span")),
			divt = document.body.appendChild(document.createElement("div")),
			bar = document.body.appendChild(document.createElement("div")).appendChild(document.createElement("span"));
		//mxm.innerHTML = "<?php echo ini_get('post_max_size') ?>";

		// set input type as file
		input.setAttribute("type", "file");

		// enable multiple selection (note: it does not work with direct input.multiple = true assignment)
		input.setAttribute("multiple", "true");

		// auto upload on files change
		input.addEventListener("change", function() {

			// disable the input
			input.setAttribute("disabled", "true");

			sendMultipleFiles({

				// list of files to upload
				files: input.files,

				// location params
				location: "<?php echo $locparms ?>",

				// clear the container 
				onloadstart: function() {
					divt.innerHTML = "Init upload ... ";
					sub.style.width = "0px";
				//	sub.style.width = bar.style.width = "0px";
				},

				// do something during upload ...
				onprogress: function(rpe) {
					divs.innerHTML = "Uploading: " + this.file.fileName + "<br />Sent: " + size(rpe.loaded) + " of " + size(rpe.total);
					divt.innerHTML = "<br />Total Sent: " + size(this.sent + rpe.loaded) + " of " + size(this.total);
					sub.style.width = ((rpe.loaded * <?php echo PRGWID; ?> / rpe.total) >> 0) + "px";
					bar.style.width = (((this.sent + rpe.loaded) * <?php echo PRGWID; ?> / this.total) >> 0) + "px";
				},

				// fired when last file has been uploaded
				onload: function(rpe, xhr) {
					divt.innerHTML += ["",
						"Server Response: " + xhr.responseText
					].join("<br />");
					sub.style.width = bar.style.width = "<?php echo PRGWID; ?>px";
					// enable the input again
					input.removeAttribute("disabled");
					parent.opener.refreshFilst();
				},

				// if something is wrong ... (from native instance or because of size)
				onerror: function() {
					divs.innerHTML = "The file " + this.file.fileName + " is too big [" + size(this.file.fileSize) + "]";
					// enable the input again
					input.removeAttribute("disabled");
				}
			});
		}, false);

		sub.parentNode.className = bar.parentNode.className = "progress";
	};
</script>
</head>
<body>
</body>
</html>