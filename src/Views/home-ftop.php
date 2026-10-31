<?php
/**
* @package		hosted-file-manager
* @copyright	Copyright (C) 2022-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		4.0.0
*/
defined('EXEC_HFM') or die('Access denied');
?>
<table id="ftbl">
	<thead>
	<tr>
	<td><input type="checkbox" id="checkAll" onchange="allSelect(this)" /></td>
	<td><?=$parntBut?></td>
<?php
	$mlab = '<a href="#" onclick="refreshFilstO(\'m\')">Last Modified</a>';
	$nlab = '<a href="#" onclick="refreshFilstO(\'n\')">Name</a>';
	$slab = '<a href="#" onclick="refreshFilstO(\'s\')">Size</a>';
	$so_asc = '<i class="fa fa-arrow-down"></i>';
	$so_dsc = '<i class="fa fa-arrow-up"></i>';
	if ($srtBy[0] == 'n') {
		if ($srtBy == 'nd') {
			$nsrtd = $so_dsc;
			$nsrtc = 'n';
		} else {
			$nsrtd = $so_asc;
			$nsrtc = 'nd';
		}
		$nlab = 'Name <a href="#" onclick="refreshFilstO(\''.$nsrtc.'\')">'.$nsrtd.'</a> ';
	} elseif ($srtBy[0] == 'm') {
		if ($srtBy == 'md') {
			$msrtd = $so_dsc;
			$msrtc = 'm';
		} else {
			$msrtd = $so_asc;
			$msrtc = 'md';
		}
		$mlab = 'Last Modified <a href="#" onclick="refreshFilstO(\''.$msrtc.'\')">'.$msrtd.'</a> ';
	} elseif ($srtBy[0] == 's') {
		if ($srtBy == 'sd') {
			$ssrtd = $so_dsc;
			$ssrtc = 's';
		} else {
			$ssrtd = $so_asc;
			$ssrtc = 'sd';
		}
		$slab = 'Size <a href="#" onclick="refreshFilstO(\''.$ssrtc.'\')">'.$ssrtd.'</a> ';
	}
?>
	<th class='left'><?=$nlab?></th>
	<th class='left tpad'><?=$mlab?></th>
	<th class='right tpad'><?=$slab?></th>
	<th class='left tpad'>Description</th>
	</tr>
	</thead>
	<tbody>
