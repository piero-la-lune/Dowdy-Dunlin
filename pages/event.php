<?php

$manager = Manager::getInstance();

if (isset($_GET['id']) && $event = $manager->getEvent($_GET['id'])) {

	$display = 'text';
	if (isset($_POST['action']) && $_POST['action'] == 'save') {
		$ans = $manager->edit($_GET['id'], $_POST);
		if ($ans === true) {
			$this->addAlert(Trad::A_SUCCESS_EDIT, 'alert-success');
			$event = $manager->getEvent($_GET['id']);
		}
		else {
			$this->addAlert($ans);
			$display = 'form';
		}
	}
	if (isset($_POST['action']) && $_POST['action'] == 'delete') {
		$ans = $manager->delete($_GET['id']);
		$_SESSION['alert'] = array(
			'text' => Trad::A_SUCCESS_DELETE,
			'type' => 'alert-success'
		);
		header('Location: '.Url::parse('home'));
		exit;
	}

	$title = $event['title'];

	$comment = \Michelf\Markdown::defaultTransform($event['comment']);
	if ($event['duration'] != 0) {
		$date = str_replace(
			array('%day%', '%start%', '%end%'),
			array(
				Manager::date($event['day']),
				'<span class="span-hour">'.date('Hi', $event['day']).'</span>',
				'<span class="span-hour">'
					.date('Hi', $event['day']+$event['duration']).'</span>'
			),
			Trad::S_ON
		);
	}
	else {
		$date = Manager::date($event['day']);
	}

	$post = array(
		'title' => isset($_POST['title']) ?
			$_POST['title']:
			Text::unchars($event['title']),
		'comment' => isset($_POST['comment']) ?
			$_POST['comment']:
			$event['comment'],
		'day_start_day' => isset($_POST['day_start_day']) ?
			$_POST['day_start_day']:
			date('j', $event['day']),
		'day_start_month' => isset($_POST['day_start_month']) ?
			$_POST['day_start_month']:
			date('n', $event['day']),
		'day_start_year' => isset($_POST['day_start_year']) ?
			$_POST['day_start_year']:
			date('Y', $event['day']),
		'day_end_day' => isset($_POST['day_end_day']) ?
			$_POST['day_end_day']:
			date('j', $event['day']),
		'day_end_month' => isset($_POST['day_end_month']) ?
			$_POST['day_end_month']:
			date('n', $event['day']),
		'day_end_year' => isset($_POST['day_end_year']) ?
			$_POST['day_end_year']:
			date('Y', $event['day']),
		'hour_start_hour' => isset($_POST['hour_start_hour']) ?
			$_POST['hour_start_hour']:
			date('H', $event['day']),
		'hour_start_min' => isset($_POST['hour_start_min']) ?
			$_POST['hour_start_min']:
			date('i', $event['day']),
		'hour_end_hour' => isset($_POST['hour_end_hour']) ?
			$_POST['hour_end_hour']:
			date('H', $event['day']+$event['duration']),
		'hour_end_min' => isset($_POST['hour_end_min']) ?
			$_POST['hour_end_min']:
			date('i', $event['day']+$event['duration']),
		'tags' => isset($_POST['tags']) ?
			$_POST['tags'] : implode(',', $event['tags'])
	);

	$content = '

<div class="div-actions-top">
	<a href="#" class="edit" data-text="'.mb_strtolower(Trad::V_CANCEL).'">
		'.mb_strtolower(Trad::V_EDIT).'
	</a>
	<a href="#" class="delete">
		'.mb_strtolower(Trad::V_DELETE).'
	</a>
</div>

<article class="display-'.$display.'"">
	
	<section>
		<h1>'.$event['title'].'</h1>
		<p>'.$date.'</p>
		<p>'.$comment.'</p>
		<p>'.Manager::tagsList($event['tags'], false).'</p>
	</section>

	<form action="'.Url::parse('events/'.$_GET['id']).'" method="post">
		'.Manager::getForm($post, $manager->getTags()).'
		<p class="p-submit"><input type="submit" value="'.Trad::V_SAVE.'" /></p>
		<input type="hidden" name="action" class="npt-action" value="save" />
	</form>

</article>

	';

}
else {

	$load = 'error/404';

}


?>