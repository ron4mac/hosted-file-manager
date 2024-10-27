<?php
// require PHP 7
if (PHP_VERSION_ID < 70000) die('PHP version 7+ required');

// Developer mode
$dev_mode = false;
$jsver = $dev_mode ? '' : '.min';
//$jqlink = '//ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery'.$jsver.'.js';

// the version of ace editor to be used
//$aceBase = '//cdnjs.cloudflare.com/ajax/libs/ace/1.4.13/';
$aceBase = '//cdnjs.cloudflare.com/ajax/libs/ace/1.12.2/';
// Specify the theme to use with Ace editor
// If theme name starts with /, it is a local theme file in js/ace/
//$acetheme = '/rjcode';
// Other wise it is a theme included in the Ace package
// If no theme is entered here, the editor will provide a dropdown selection list
//   - hover over the list name to see the string that should be entered below
$acetheme = 'sqlserver';

// link to font awesome
$fontawsm = '//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css';