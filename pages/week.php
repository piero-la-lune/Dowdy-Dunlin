<?php

	$title = Trad::T_WEEK;

	$manager = Manager::getInstance();

	$events = $manager->getWeek(time());

	$start = time();
	$end = time();
	$days = array();
	while (date('W', $start) == date('W')) {
		$days[date('Ymd', $start)] = true;
		$start -= 60*60*24;
	}
	while (date('W', $end) == date('W')) {
		$days[date('Ymd', $end)] = true;
		$end += 60*60*24;
	}
	$start += 60*60*24;
	$end -= 60*60*24;
	$days = array_keys($days);
	sort($days);

	$content = Manager::barTop()
		.'<article class="weeks">'
			.'<h1 class="h1-period">'
				.str_replace(
					array('%from%', '%to%'),
					array(Manager::date(date('Ymd', $start)), Manager::date(date('Ymd', $end))),
					Trad::S_FROMTO_DAY
				)
			.'</h1>'
			.Manager::previewEvents($events, $days)
		.'</article>'
		.Manager::barBottom();

?>