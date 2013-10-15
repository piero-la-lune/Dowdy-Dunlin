<?php

	$title = Trad::T_HOME;

	$manager = Manager::getInstance();

	$events = $manager->getDay(time());

	$content = Manager::barTop(true)
		.'<article class="days">'
			.'<h1 class="h1-period">'.Manager::date(time()).'</h1>'
			.Manager::previewEvents($events)
		.'</article>'
		.Manager::barBottom(true);

?>