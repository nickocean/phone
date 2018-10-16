<?php


function getData($data) {
	$file = fopen('data.txt', 'r+');
	foreach ($data as $value) {
		fwrite($file, $value);
	}
}

getData($_GET);