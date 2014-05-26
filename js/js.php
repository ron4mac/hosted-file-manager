<?php
	$vers = '.min';
	header("Content-type: text/javascript"); 
	readfile('jqModal'.$vers.'.js'); echo"\n";
	readfile('fmx'.$vers.'.js'); echo"\n";
	readfile('fmxui'.$vers.'.js'); echo"\n";
