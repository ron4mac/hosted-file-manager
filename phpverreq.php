<?php

//-------------------------------------------------------------------
// Name:		minVersion.php
// Desc:		Determines minimum PHP function to run script
// Author:		Alex Roxon (http://phpduck.com/)
// Version:		1.0.0
//-------------------------------------------------------------------
// Copyright 2010 Alex Roxon, PHPDuck.com
// All Rights Reserved
//-------------------------------------------------------------------

require_once('functions.php');
$dir2chk = $baseDir . urldecode($_GET['dir']);

// So the script doesn't time out on large operations
set_time_limit( 300 );

// For page execution time
$Start = microtime(true);

// Filetypes to check
$FileTypes = array('php', 'php3', 'php4', 'php5', 'phtml');

// DO NOT EDIT BEYOND THIS POINT!

// Files array
$Files = array();

// Found functions
$Found = array();

// Other functions
$Others = array();

// Current highest
$Highest = '0';

// Get function/method table
$Functions = unserialize(file_get_contents('phpfuncs.ary'));


// Retrieve list of PHP files
RetrieveFiles($dir2chk);

// Let's loop through the files
foreach( $Files as $File ) {
	if( file_exists( $File ) ) {
		//echo '@ -> '.$File.'<br />';
		$Contents = file_get_contents( $File );

		// Let's grab the PHP code
		while( preg_match( '/\<\?php/i', $Contents ) === 1 ) {
			$Code = substr( $Contents, stripos( $Contents, '<?php' ), strlen( $Contents ) );
//			$Code = substr( $Code, 0, stripos( $Code, '>' ) + 2 );
			$Code = substr( $Code, 0, (strpos( $Code, '?>' ) ?: (strlen($Code)-2)) + 2 );
			
			// Let's test the functions
			preg_match_all( '/([a-zA-Z0-9\_]+)\s?\(.*?\;/', $Code, $Funcs1 );	//var_dump($Code,$Funcs1);	echo'111<br /><br /><br />';
//			preg_match_all( '/([a-z0-9\_]+[\s])\(.*?\;/', $Code, $Funcs2 );
			preg_match_all( '/([a-zA-Z0-9\_]+::[a-zA-Z0-9\_]+)\s?\(.*?\;/', $Code, $Funcs2 );	//var_dump($Funcs2);
			preg_match_all( '/new ([a-zA-Z0-9\_]+)\(.*?\;/', $Code, $Funcs3 );	//echo'<pre>';var_dump($Funcs3);echo'</pre>';
			if ($Funcs3) foreach($Funcs3[1] as $k=>$f) { $Funcs3[1][$k] .= '::__construct'; }
			$Funcs = array_merge( $Funcs1[1], $Funcs2[1], $Funcs3[1] );

			// Let's check if we have the function in our xml files.
			foreach( $Funcs as $Function ) {
				if( isset( $Found[ $Function ] ) ) {
					$Found[ $Function ][ 'occurences' ]++;
				} else if ( isset( $Functions[ $Function ] ) ) {
					$vers = explode(',', $Functions[ $Function ]);
					foreach ($vers as $k=>$ver) {
						if( preg_match('/^[0-9\.]+$/', $ver) ) {
							$Found[ $Function ] = array(
								'occurences'	=> 1,
								'version'		=> $Functions[ $Function ],
							);
						//	if ($k===0) echo $ver.' '.version_compare($ver, $Highest).'<br />';
							// Ammend the highest current version?
							if( ($k===0) && (version_compare($ver, $Highest)==1) ) {
								$Highest = $ver;
							}
						} else {
							$Found[ $Function ] = array(
								'occurences'	=> 1,
								'extension'		=> $Functions[ $Function ],
							);
						}
					}
				} else if (!isset($Others[$Function])) {
					$Others[$Function] = $Function;
				}
			}

			// Replace the code and continue
			$Contents = str_replace( $Code, null, $Contents);
		}
	}
}

// Retrieve files function
function RetrieveFiles( $Dir = "." ) {
	global $FileTypes;
	global $Files;

	// Open the directory
	if ( $handle = opendir( $Dir ) ) {
		while ( ($file = readdir( $handle ) ) !== false ) {

			// If we have a proper files
			if ( $file != '.' && $file != '..' ) {
				
				if( $Dir == '.' ) $Dir = null;
				$FileType = substr( $file, strrpos( $file, "." ) + 1, strlen( $file ) );

				// If we have a php files
				if( in_array( strtolower( $FileType ), $FileTypes ) ) {
					$Files[] = $Dir . $file;
				} elseif ( filetype( $Dir . $file ) == 'dir' ) {
					$Folder = $Dir . $file;
					if( substr( $Folder, -1 ) !== '/' ) $Folder .= '/';

					// If we've found a child folder, let's retrieve the files in there.
					RetrieveFiles( $Folder );
				}
			}
		}
	}
    closedir($handle);
}

// Format results function
function Format_Results() {
	global $Found, $Others;

	$Return = null;
	foreach( $Found as $Function => $Vars) {
		$Return .= '<a href="http://php.net/' . $Function . '" target="_new">' . $Function . '</a> (' . $Vars['occurences'] . ' occurences) - ';
		if( isset( $Vars['version'] ) ) {
			$Return .= 'PHP ' . $Vars['version'];
		} elseif( isset( $Vars['extension'] ) ) {
			$Return .= $Vars['extension'];
		}
		$Return .= '<br />';
	}
	sort($Others);
	foreach ($Others as $o) {
		$Return .= '<br />' . $o;
	}
	return $Return;
}

// Output extensions
function Output_Extensions() {
	global $Found;

	$Done = array();
	
	$Return = null;
	foreach( $Found as $Function => $Vars) {
		if( isset( $Vars['extension'] ) && ! in_array( $Vars['extension'], $Done ) ) {
			$Return .= '<a href="http://php.net/' . $Function . '" target="_new">' . $Function . "</a> - " .$Vars['extension'] . "<br />";
			$Done[] = $Vars['extension'];
		}
	}
	if( $Return != null ) {
		$Return = "<div style='font-size: 13px; margin-top: -5px; margin-bottom: 20px;'>\n<h2>PHP Extensions required:</h2>\n" . $Return . "</div>";
	}

	return $Return;
}

$End = microtime(true);
$Execution = $End - $Start;

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<style type="text/css">
h2 {
	font-size: 16px;
	font-family: arial;
	margin: 0;
}
div {
	font-size: 11px;
}
div a {
	color: #446b84;
	text-decoration: none;
	border-bottom: 1px #446b84 dotted;
}
div a:hover {
	text-decoration: underline;
	border: 0;
}

#results {
	margin-top: 8px;
}

#results a {
	color: red;
	text-decoration: none;
	border: 0;
}

</style>
<script type="text/javascript">
var Show = false;

function Toggle() {
	if( Show == false ) {
		document.getElementById('dis').style.height = 'auto';
		document.getElementById('show').innerHTML = 'Hide Results';
		Show = true;
	} else {
		document.getElementById('dis').style.height = '16px';
		document.getElementById('show').innerHTML = 'Show Results';
		Show = false;
	}
	return false;
}
</script>
<title> minVersion.php Result - PHPDuck.com </title>
<meta name="Author" content="PHPDuck.com">
</head>
<body style="font-size: 15px; font-family: arial;">
	<h1 style="font-family: arial; font-size: 18px;">PHPDuck.com minVersion.php Results</h1>
	<div style='font-size: 11px;'>Results took <?php echo round($Execution, 3); ?> seconds to render.</div>
	<table><tr><td>The minimum PHP version you need installed to run the PHP files in this directory is:</td>
	<td style='float: left; font-weight: bold; font-size: 40px; color: green;'><?php echo $Highest; ?></td></tr></table>
	<br />
	
	<?php echo Output_Extensions(); ?>

	<div style='width: 700px; border: 1px #8a979f solid; background-color: #e3e5e6; height: 16px; padding: 4px; overflow: hidden;' id='dis'>
		<a href="#" id="show" onclick='return Toggle();'>Show Results</a>
		<div id="results">
			<?php echo Format_Results(); ?>
		</div>
	</div>
	<br /><br />
	<div style='font-size: 10px;'>&copy; Copyright 2010 <a href="http://phpduck.com/">PHP Duck</a>. All Rights Reserved.</div>
</body>
</html>
