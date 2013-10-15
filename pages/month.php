<?php

	$title = Trad::T_MONTH;

	$manager = Manager::getInstance();

	$events = $manager->getMonth(time());

	$content = Manager::barTop()
		.'<article class="months">'
			.'<h1 class="h1-period">'
				.Manager::date(time())
			.'</h1>'
			.Manager::previewEvents($events)
		.'</article>'
		.Manager::barBottom();

?>