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
			$h1 = '<h1 class="h1-period">'.Manager::date(date('Ymd', $t)).'</h1>';
			$days = array(date('Ymd', $t));
		}
		if ($_POST['page'] == 'week') {
			$interval = new DateInterval('P'.abs($n).'W');
			$date = new DateTime();
			if ($n > 0) { $date->add($interval); }
			else { $date->sub($interval); }
			$t = $date->getTimestamp();
			$events = $manager->getWeek($t);
			$days = array();
			$start = $t;
			$end = $t;
			while (date('W', $start) == date('W', $t)) {
				$days[date('Ymd', $start)] = true;
				$start -= 60*60*24;
			}
			while (date('W', $end) == date('W', $t)) {
				$days[date('Ymd', $end)] = true;
				$end += 60*60*24;
			}
			$start += 60*60*24;
			$end -= 60*60*24;
			$days = array_keys($days);
			sort($days);
			$h1 = '<h1 class="h1-period">'
				.str_replace(
					array('%from%', '%to%'),
					array(Manager::date(date('Ymd', $start)), Manager::date(date('Ymd', $end))),
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
				.Manager::date(date('Ymd', $t))
			.'</h1>';
			$days = array();
			$month = date('Ym');
			$day = strtotime($month.'01');
			while(date('Ym', $day) == $month) {
				$days[] = date('Ymd', $day);
				$day += 3600*24;
			}
		}
		die(json_encode(array(
			'status' => 'success',
			'html' => $h1.Manager::previewEvents($events, $days)
		)));
	}
}

die(json_encode(array('status' => 'error')));

?>