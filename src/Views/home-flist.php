<?php
/**
* @package		hosted-file-manager
* @copyright	Copyright (C) 2022-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		4.0.0
*/
defined('EXEC_HFM') or die('Access denied');

//echo'<xmp>';var_dump($flist);echo'</xmp>';
foreach ($flist as $flear) {
	$fle = $flear[0];
	$fPth = "$rDir/{$fle}";
	$fs = $flear[1];	//lstat($fPth);
	if (!$fs) {
		echo '<tr><td>Can not stat '.$rDir.'/'.$fle.'</td></tr>';
		continue;
	}
	$lok = true;
	$isLnk = ($fs[2] & 0xA000) === 0xA000;
	$rlnk = '';
	if ($isLnk) {
		$fs = @stat($fPth);
		$lok = (bool)$fs;
		$fs = $fs ?: [0,0,0,0,0,0,0,0,0,0];
		$rlnk = readlink($fPth);
	}
	$afle = ($pDir ? "$pDir/" : '') . $fle;
	$efle = urlencode($afle);
	if ($fs[2] & 040000) {
		echo "<tr data-fref='$fle'>";
		$dt = date('M d, Y g:ia', $fs[9]);
		if (is_writable($fPth)) {
			echo '<td><input type="checkbox" class="fsel" name="files[]" value="'.$fle.'/" /></td>';
		} else {
			echo '<td>&nbsp;</td>';
		}
		echo '<td class="diricon" data-ctx="fldCtx"><i class="fa fa-folder" aria-hidden="true"></i></td>';
		echo '<td data-ctx="fldCtx">';
		if ($lok) { echo '<a href="index.php?dir='.$efle.'">'.$fle.'</a>'; }
		else { echo $fle; }
		echo ($isLnk ? " &rarr; $rlnk" : '').'</td>';
		echo '<td class="tpad">'.$dt.'</td>';
		echo '<td class="right tpad">--</td>';
		echo '<td class="tpad"><a href="#" data-act="finf"><i class="fa fa-info-circle" aria-hidden="true"></i></a></td>';
		echo '</tr>'."\n";
	} else {
		echo "<tr data-fref=\"$fle\">";
		$ufle = htmlspecialchars($fle);
		$fnp = explode('.',$ufle);
		$ufle = str_replace(' ','&nbsp;',$ufle);
		$flext = array_pop($fnp);
		$filedt = preg_match('/php|js|html|htm|pl|cgi|css|ini|xml|sql|txt|csv|htaccess/i', $flext);
		$imgedt = preg_match('/jpg|jpeg|png|gif|bmp/i', $flext);
		$sz = $fs[7];
		if ($sz > 1073741824) {$sz = sprintf('%.1f', ($sz / 1073741824)). 'g';}
		elseif ($sz > 1048575) {$sz = sprintf('%.1f', ($sz / 1048576)) . 'm';}
		elseif ($sz > 1023) {$sz = sprintf('%.1f', ($sz / 1024)) . 'k';}
		$dt = date('M d, Y g:ia', $fs[9]);
		if (is_writable($fPth)) {
			echo '<td><input type="checkbox" class="fsel" name="files[]" value="'.$fle.'" /></td>';
		} else {
			echo '<td>&nbsp;</td>';
		}
		if ($filedt) {
			echo '<td class="filedticon" data-ctx="filCtx" onclick="doFileAction(\'fedt\',this,event)"><i class="fa fa-file" aria-hidden="true"></td>';
		} elseif ($imgedt) {
			echo '<td class="imgedticon" data-ctx="filCtx" onclick="doFileAction(\'iedt\',this,event)"><i class="fa fa-file-image-o" aria-hidden="true"></i></td>';
		} else {
			echo '<td class="filicon" data-ctx="filCtx"><i class="fa fa-file" aria-hidden="true"></i></td>';
		}
		echo '<td data-ctx="filCtx">';
		if ($lok) { echo '<a href="#" data-act="fvue">'.$ufle.'</a>'; }
		else { echo $ufle; }
		echo ($isLnk ? " &rarr; $rlnk" : '').'</td>';
		echo '<td class="tpad">'.$dt.'</td>';
		echo '<td class="right tpad">'.$sz.'</td>';
		echo '<td class="tpad"><a href="#" data-act="finf"><i class="fa fa-info-circle" aria-hidden="true"></i></a></td>';
		echo '</tr>'."\n";
	}
}
