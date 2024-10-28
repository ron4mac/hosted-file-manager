<?php
define('MYCOOKIE','fmximgs');

$self = htmlentities($_SERVER['PHP_SELF']);

if (isset($_GET['sd'])) {
	$imgsbase = htmlentities($_GET['sd']);
	setcookie(MYCOOKIE, base64_encode($imgsbase));
} else {
	$imgsbase = base64_decode($_COOKIE[MYCOOKIE]);
}

$finfo = finfo_open(FILEINFO_MIME);
if (!$finfo) die('Opening fileinfo database failed');

$basDir = dirname($_SERVER['DOCUMENT_ROOT']);
$drsl = strlen($basDir);	// + 1;

$cdir = empty($_GET['d']) ? '' : (htmlentities($_GET['d']) . '/');
$dirts = $basDir. $imgsbase . $cdir;	//. '/';

$html = <<<EOT
<div class="bckb b-blu" onclick="backc('#aaeeff')"> </div>
<div class="bckb b-grn" onclick="backc('#99ff99')"> </div>
<div class="bckb b-red" onclick="backc('#ff2244')"> </div>
<div class="bckb b-blk" onclick="backc('#000000')"> </div>
<div class="bckb b-wht" onclick="backc('#ffffff')"> </div>
<div class="bckb b-trn" onclick="backc()"> </div>
EOT;

$html .= '<nav>';
if ($cdir) {
	$href = htmlentities($_SERVER['SCRIPT_URL']);
	$html .= '<a href="'.$href.'">'.basename($imgsbase).'</a>';
	$href .= '?d=';
	$folds = explode('/', $cdir);
	array_pop($folds);
	$curD = array_pop($folds);
	foreach ($folds as $fold) {
		$href .= $fold;
		$html .= '/<a href="'.$href.'">'.$fold.'</a>';
		$href .= '/';
	}
	$html .= "/$curD";
} else {
	$html .= basename($imgsbase);
}
$html .= '</nav>';

$dirsl = [];
$imgsl = [];

$files = scandir($dirts);
foreach ($files as $file) {
	if ($file[0]=='.') continue;
	$fpath = $dirts.$file;
	if (is_dir($fpath)) {
		$dirsl[] = $file;
		continue;
	}
	$iurl = 'filproxy.php?f='.urlencode(substr($fpath, $drsl));
	$mtyp = substr(finfo_file($finfo,$fpath),0,6);
	switch ($mtyp) {
	case 'image/':
		$imgsl[$file] = $iurl;
		break;
	case 'video/':
		$imgsl[$file] = [$iurl, 'css/video.svg'];
		break;
	case 'audio/':
		// fancybox doesn't directly handle audio so force an iframe
		$imgsl[$file] = ['javascript:;" data-type="iframe" data-src="'.$iurl, 'css/audio.svg'];
	//	$imgsl[$file] = [$iurl, 'css/audio.svg'];
		break;
	}
}

if ($dirsl) {
	$html .= '<div class="folds">';
	natsort($dirsl);
	foreach ($dirsl as $adir) {
		$html .= '<a href="'.$self.'?d='.urlencode($cdir.$adir).'"><div class="fold"><span>'.$adir.'</span>';
		$html .= '<img src="css/folder.svg" /></div></a>';
	}
	$html .= '</div>';
}
if ($imgsl) {
	$html .= '<div class="imgs">';
	ksort($imgsl, SORT_NATURAL | SORT_FLAG_CASE);
	foreach ($imgsl as $file=>$aimg) {
		if (is_array($aimg)) {
			$html .= '<div class="mbox mdya"><p>'.$file.'</p>';
			$html .= '<a data-fancybox="gallery" href="'.$aimg[0].'"><img src="'.$aimg[1].'" /></a></div>';
		} else {
			$html .= '<div class="mbox"><p>'.$file.'</p>';
			$html .= '<a data-fancybox="gallery" data-caption="'.htmlspecialchars($file).'" href="'.$aimg.'"><img class="aimg" src="'.$aimg.'" /></a></div>';
		}
	}
	$html .= '</div>';
}

$html .= '<br style="clear:both" />';
//$svgobj = simplexml_load_file($dirts.'rain.svg');
//echo'<xmp>';var_dump($svgobj);echo'</xmp>';
?>
<!DOCTYPE html>
<html>
<head>
<!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css" /> -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.css" />
<style>
nav {font-size:larger;}
.bckb {
	float:right;
	width:24px;
	height:24px;
	border:1px solid #AAAAAA;
	margin-left:4px;
	cursor:pointer;
}
.b-blu {background-color:#aaeeff;}
.b-grn {background-color:#99ff99;}
.b-red {background-color:#ff2244;}
.b-blk {background-color:#000000;}
.b-wht {background-color:#ffffff;}
.b-trn {background-image:url(css/back1.png);}

.folds, .imgs {
	clear:both;
	margin-top:1rem;
}
.fold {
	float:left;
	position:relative;
	margin:0 16px 16px 0;
	padding:0 8px;
	border:2px solid #CCEEFF
}
.fold img {height:96px;}
.fold span {
	position:absolute;
	top:50%;
	left:50%;
	transform:translate(-50%,-50%);
}
.mbox {
	float:left;
	margin:0 16px 16px 0;
	padding: 0 8px;
	border:1px solid #EEEEEE;
	text-align:center;
}
.mdya {
	background-image:url(css/avback.png);
	background-color:lightcyan;
}
.mbox img {height:96px;}
.aimg {
	background-image:url(css/back1.png);
}
.fancybox-slide--iframe .fancybox-content {
	min-height : 400px;
	max-width  : 80%;
	max-height : 80%;
	margin: 0;
	background-color: transparent;
}
.fancybox-caption {
	position: relative;
	font-size: large;
}
</style>
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
<!-- <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script> -->
<!-- <script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js"></script> -->
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.umd.js"></script>
<script src="js/echo.min.js"></script>
<script>
Fancybox.defaults.infinite = 0;
Fancybox.defaults.showClass = false;
Fancybox.defaults.hideClass = false;
Fancybox.defaults.autoFocus = false;
Fancybox.Plugins.Thumbs.defaults.autoStart = false;
Fancybox.Plugins.Toolbar.defaults.display = ["zoom","slideshow","fullscreen","download","close"];

function backc (colr) {
	var elms = document.getElementsByClassName("aimg");
	if (colr) {
		for (var i = 0; i < elms.length; i++) {
			elms[i].style.backgroundColor = colr;
			elms[i].style.backgroundImage = "none";
		}
	} else {
		for (var i = 0; i < elms.length; i++) {
			elms[i].style.backgroundColor = "none";
			elms[i].style.backgroundImage = "url(css/back1.png)";
		}
	}
}
</script>
</head>
<body>
<?php echo $html; ?>
<?php //echo'<xmp>';var_dump($GLOBALS);echo'</xmp>'; ?>
<script>
	echo.init({
		offset: 200,
		throttle: 250,
		debounce: false
	});
</script>
</body>
</html>
