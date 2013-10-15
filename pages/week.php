<?php

	$title = Trad::T_WEEK;

	$manager = Manager::getInstance();

	$events = $manager->getWeek(time());

	$start = time();
	$end = time();
	while (date('W', $start) == date('W')) { $start -= 60*60*24; }
	while (date('W', $end) == date('W')) { $end += 60*60*24; }
	$start += 60*60*24;
	$end -= 60*60*24;

	$content = Manager::barTop()
		.'<article class="weeks">'
			.'<h1 class="h1-period">'
				.str_replace(
					array('%from%', '%to%'),
					array(Manager::date($start), Manager::date($end)),
					Trad::S_FROMTO_DAY
				)
			.'</h1>'
			.Manager::previewEvents($events)
		.'</article>'
		.Manager::barBottom();

?>