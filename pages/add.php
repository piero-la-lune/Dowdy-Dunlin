<?php

	$manager = Manager::getInstance();

	if (isset($_POST['action']) && $_POST['action'] == 'add') {
		$ans = $manager->add($_POST);
		if ($ans === true) {
			$_SESSION['alert'] = array(
				'text' => Trad::A_SUCCESS_ADD,
				'type' => 'alert-success'
			);
			header('Location: '.Url::parse('events/'.$manager->lastInserted()));
			exit;
		}
		else {
			$this->addAlert($ans);
		}
	}

	$title = Trad::T_ADD;

	$post = array(
		'title' => isset($_POST['title']) ? $_POST['title'] : '',
		'comment' => isset($_POST['comment']) ? $_POST['comment'] : '',
		'day_start_day' =>
			isset($_POST['day_start_day']) ? $_POST['day_start_day'] : '',
		'day_start_month' =>
			isset($_POST['day_start_month']) ? $_POST['day_start_month'] : date('n'),
		'day_start_year' =>
			isset($_POST['day_start_year']) ? $_POST['day_start_year'] : date('Y'),
		'day_end_day' =>
			isset($_POST['day_end_day']) ? $_POST['day_end_day'] : '',
		'day_end_month' =>
			isset($_POST['day_end_month']) ? $_POST['day_end_month'] : date('n'),
		'day_end_year' =>
			isset($_POST['day_end_year']) ? $_POST['day_end_year'] : date('Y'),
		'hour_start_hour' =>
			isset($_POST['hour_start_hour']) ? $_POST['hour_start_hour'] : '',
		'hour_start_min' =>
			isset($_POST['hour_start_min']) ? $_POST['hour_start_min'] : '',
		'hour_end_hour' =>
			isset($_POST['hour_end_hour']) ? $_POST['hour_end_hour'] : '',
		'hour_end_min' =>
			isset($_POST['hour_end_min']) ? $_POST['hour_end_min'] : '',
		'tags' => isset($_POST['tags']) ? $_POST['tags'] : '',
		'caldav' => isset($_POST['caldav']) ? $_POST['caldav'] :
			(isset($config['caldav']) ? 'oui' : 'non'),
		'caldav_enabled' => isset($config['caldav'])
	);

	$content = ''

.'<form action="'.Url::parse('add').'" method="post">'
	.Manager::getForm($post, $manager->getTags())
	.'<p class="p-submit"><input type="submit" value="'.Trad::V_ADD.'" /></p>'
	.'<input type="hidden" name="action" value="add" />'
.'</form>'

;



?>