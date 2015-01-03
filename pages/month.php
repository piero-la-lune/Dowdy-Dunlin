<?php

	$title = Trad::T_MONTH;

	$manager = Manager::getInstance();

	$events = $manager->getMonth(time());

	$days = array();
	$month = date('Ym');
	$day = strtotime($month.'01');
	while(date('Ym', $day) == $month) {
		$days[] = date('Ymd', $day);
		$day += 3600*24;
	}

	$content = Manager::barTop()
		.'<article class="months">'
			.'<h1 class="h1-period">'
				.Manager::date(date('Ymd'))
			.'</h1>'
			.Manager::previewEvents($events, $days)
		.'</article>'
		.Manager::barBottom();

?>