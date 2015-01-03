<?php

if (!isset($config)) {
	exit;
}

function strict_lower($a, $b) {
	$ea = explode('.', $a);
	$eb = explode('.', $b);
	for ($i=0; $i < count($ea); $i++) { 
		if (!isset($eb[$i])) { $eb[$i] = 0; }
		$na = intval($ea[$i]);
		$nb = intval($eb[$i]);
		if ($na > $nb) { return false; }
		if ($na < $nb) { return true; }
	}
	return false;
}

if (strict_lower($config['version'], '0.2')) {

	$events = Text::unhash(get_file(FILE_EVENTS));
	foreach ($events as $k => $e) {
		if ($e['duration'] != 0) {
			$events[$k]['day_start'] = date('Ymd', $e['day']);
			$events[$k]['day_end'] = date('Ymd', $e['day']);
			$events[$k]['time_start'] = date('Hi', $e['day']);
			$events[$k]['time_end'] = date('Hi', $e['day']+$e['duration']);
		}
		else {
			$events[$k]['day_start'] = date('Ymd', $e['day']);
			$events[$k]['day_end'] = date('Ymd', $e['day']);
			$events[$k]['time_start'] = null;
			$events[$k]['time_end'] = null;
		}
		unset($events[$k]['day']);
		unset($events[$k]['duration']);
	}
	update_file(FILE_EVENTS, Text::hash($events));

}

if (strict_lower($config['version'], '0.3')) {

	$events = Text::unhash(get_file(FILE_EVENTS));
	foreach ($events as $k => $e) {
		$events[$k]['caldav'] = '';
	}
	update_file(FILE_EVENTS, Text::hash($events));

}

$settings = new Settings();
if ($config['url_rewriting']) { $settings->url_rewriting(); }
$settings->save();

header('Content-Type: text/html; charset=utf-8');
die('Mise à jour effectuée avec succès ! Raffraichissez cette page pour accéder à Dowdy Dunlin.');

?>