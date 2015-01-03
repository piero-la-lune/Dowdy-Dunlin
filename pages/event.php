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
	if ($event['day_start'] == $event['day_end']) {
		$date = str_replace('%day%', Manager::date($event['day_start']), Trad::S_ON);
		if ($event['time_start']) {
			$date .= ' '.str_replace(
				array('%from%', '%to%'),
				array(
					'<span class="span-hour">'.$event['time_start'].'</span>',
					'<span class="span-hour">'.$event['time_end'].'</span>'
				),
				Trad::S_FROMTO_HOUR
			);
		}
	}
	else {
		if ($event['time_start'] && $event['time_end']) {
			$date = str_replace(
				array('%day_start%', '%time_start%', '%day_end%', '%time_end%'),
				array(
					Manager::date($event['day_start']),
					'<span class="span-hour">'.$event['time_start'].'</span>',
					Manager::date($event['day_end']),
					'<span class="span-hour">'.$event['time_end'].'</span>'
				),
				Trad::S_FROMTO_DAY_HOUR
			);
		}
		else {
			$date = str_replace(
				array('%from%', '%to%'),
				array(
					Manager::date($event['day_start']),
					Manager::date($event['day_end'])
				),
				Trad::S_FROMTO_DAY
			);
		}
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
			substr($event['day_start'], 6, 2),
		'day_start_month' => isset($_POST['day_start_month']) ?
			$_POST['day_start_month']:
			substr($event['day_start'], 4, 2),
		'day_start_year' => isset($_POST['day_start_year']) ?
			$_POST['day_start_year']:
			substr($event['day_start'], 0, 4),
		'day_end_day' => isset($_POST['day_end_day']) ?
			$_POST['day_end_day']:
			substr($event['day_end'], 6, 2),
		'day_end_month' => isset($_POST['day_end_month']) ?
			$_POST['day_end_month']:
			substr($event['day_end'], 4, 2),
		'day_end_year' => isset($_POST['day_end_year']) ?
			$_POST['day_end_year']:
			substr($event['day_end'], 0, 4),
		'hour_start_hour' => isset($_POST['hour_start_hour']) ?
			$_POST['hour_start_hour']:
			substr($event['time_start'], 0, 2),
		'hour_start_min' => isset($_POST['hour_start_min']) ?
			$_POST['hour_start_min']:
			substr($event['time_start'], 2, 2),
		'hour_end_hour' => isset($_POST['hour_end_hour']) ?
			$_POST['hour_end_hour']:
			substr($event['time_end'], 0, 2),
		'hour_end_min' => isset($_POST['hour_end_min']) ?
			$_POST['hour_end_min']:
			substr($event['time_end'], 2, 2),
		'tags' => isset($_POST['tags']) ?
			$_POST['tags'] : implode(',', $event['tags']),
		'caldav' => isset($_POST['caldav']) ? $_POST['caldav'] :
			(empty($event['caldav']) ? 'non' : 'oui'),
		'caldav_enabled' => isset($config['caldav'])
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