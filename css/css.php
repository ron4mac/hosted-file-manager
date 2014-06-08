<?php
//package the css files for one access
header("Content-type: text/css"); 
readfile('jqModal.css'); echo"\n";
readfile('fmx.css'); echo"\n";
readfile('fmxui.css'); echo"\n";
readfile('nav.css'); echo"\n";
readfile('context.css'); echo"\n";
