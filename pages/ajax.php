<?php

if (isset($_POST['action']) && isset($_POST['page'])) {

	$manager = Manager::getInstance();

	if (isset($_POST['action']) && $_POST['action'] == 'changeNo'
		&& isset($_POST['no'])
	) {
		$n = intval($_POST['no']);
		$events = array();
		$h1 = '';
		if ($_POST['page'] == 'home') {
			$interval = new DateInterval('P'.abs($n).'D');
			$date = new DateTime();
			if ($n > 0) { $date->add($interval); }
			else { $date->sub($interval); }
			$t = $date->getTimestamp();
			$events = $manager->getDay($t);
			$h1 = '<h1 class="h1-period">'.Manager::date($t).'</h1>';
		}
		if ($_POST['page'] == 'week') {
			$interval = new DateInterval('P'.abs($n).'W');
			$date = new DateTime();
			if ($n > 0) { $date->add($interval); }
			else { $date->sub($interval); }
			$t = $date->getTimestamp();
			$events = $manager->getWeek($t);
			$start = $t;
			$end = $t;
			while (date('W', $start) == date('W', $t)) { $start -= 60*60*24; }
			while (date('W', $end) == date('W', $t)) { $end += 60*60*24; }
			$start += 60*60*24;
			$end -= 60*60*24;
			$h1 = '<h1 class="h1-period">'
				.str_replace(
					array('%from%', '%to%'),
					array(Manager::date($start), Manager::date($end)),
					Trad::S_FROMTO_DAY
				)
			.'</h1>';
		}
		if ($_POST['page'] == 'month') {
			$interval = new DateInterval('P'.abs($n).'M');
			$date = new DateTime();
			if ($n > 0) { $date->add($interval); }
			else { $date->sub($interval); }
			$t = $date->getTimestamp();
			$events = $manager->getMonth($t);
			$h1 = '<h1 class="h1-period">'
				.Manager::date($t)
			.'</h1>';
		}
		die(json_encode(array(
			'status' => 'success',
			'html' => $h1.Manager::previewEvents($events)
		)));
	}
}

die(json_encode(array('status' => 'error')));

?>