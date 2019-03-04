<?php
error_reporting(E_ALL);

$file = 'ggao.json';

if(file_exists($file)){
	$hh = unlink($file);
}
exit;