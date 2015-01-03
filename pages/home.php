<?php

	$title = Trad::T_HOME;

	$manager = Manager::getInstance();

	$day = date('Ymd');
	$events = $manager->getDay(time());

	$content = Manager::barTop(true)
		.'<article class="days">'
			.'<h1 class="h1-period">'.Manager::date($day).'</h1>'
			.Manager::previewEvents($events, array($day))
		.'</article>'
		.Manager::barBottom(true);

?>