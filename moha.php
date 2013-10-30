<?php
/**
 * Qualification project submission from PHPUG Finland Champion
 *
 * Copyright 2013 S H Mohanjith (email: moha@mohanjith.net)
 */

if ( isset($argv) && is_array($argv) && count($argv) > 1 ) {
	$csv = $argv[1];
} else {
	die("Usage: moha.php example.csv\n");
}

date_default_timezone_set('Europe/Helsinki');

if ( ( $handle = fopen($csv, "r") ) !== FALSE ) {
	$loh = 0;
	$soh = 1000;
	$longest = array();
	$shortest = array();
	while ( ( $data = fgetcsv($handle, 4096, ";") ) !== FALSE ) {
		unset($data[7]);
		$duration = $data[4];
		$hw = parse_duration($duration);
		if ($hw > $loh) {
			$longest = $data;
			$loh = $hw;
		}
		if ($soh > $hw) {
			$shortest = $data;
			$soh = $hw;
		}
	}
} else {
	die("File not found\n");
}

echo "{$longest[0]}. {$longest[1]}, open {$loh} hours per week\n";
echo "{$shortest[0]}. {$shortest[1]}, open {$soh} hours per week\n";

function parse_duration($dur) {
	$_week = array(
		'Ma' => 1,
		'Ti' => 2,
		'Ke' => 3,
		'To' => 4,
		'Pe' => 5,
		'La' => 6,
		'Su' => 7,
	);

	$hours_per_week = 0;

	$days = explode(',', $dur);

	foreach ($days as $day) {
		$intervals = explode('ja', $day);
		
		foreach ($intervals as $interval) {
			$interval = trim($interval);
			if (preg_match('/[a-z]{2} [0-9]{2}:[0-9]{2}\-[0-9]{2}:[0-9]{2}/i', $interval) > 0) {
				list($wod, $time) = explode(' ', $interval);
			} else if (preg_match('/[a-z]{2}-[a-z]{2} [0-9]{2}:[0-9]{2}\-[0-9]{2}:[0-9]{2}/i', $interval) > 0) {
				list($wod, $time) = explode(' ', $interval); 
			} else if (preg_match('/[0-9]{2}:[0-9]{2}\-[0-9]{2}:[0-9]{2}/i', $interval) > 0) {
				$time = $interval;
			}

			list($start, $end) = explode('-', $time);
			$istart = strtotime($start);
			$iend = strtotime($end);

			$hours = ($iend-$istart)/3600;

			if (preg_match('/[a-z]{2}-[a-z]{2}/i', $wod) > 0) {
				list($wstart, $wend) = explode('-', $wod);
				$dc = $_week[$wend]-$_week[$wstart]+1;
				$hours_per_week += ($hours*$dc);
			} else {
				$hours_per_week += $hours;
			}
		}
	}
	return $hours_per_week;
}