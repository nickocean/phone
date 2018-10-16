<?php


function getData($data) {
	$file = fopen('data.txt', 'r+');
	foreach ($data as $key => $value) {
		fwrite($file, $value);
	}
	fclose($file);
}

getData($_GET);