<?php

# Events model :
#	title => escaped (string)
#	comment => (string)
#	day => (int) // timestamp
#	duration => (int) // seconds
#	tags => (array)

class Manager {

	private static $instance;
	protected $events = array();
	protected $tags = array();
	protected $last_inserted;

	public static $months = array(
		1 => Trad::W_JANUARY,
		2 => Trad::W_FEBRUARY,
		3 => Trad::W_MARCH,
		4 => Trad::W_APRIL,
		5 => Trad::W_MAY,
		6 => Trad::W_JUNE,
		7 => Trad::W_JULY,
		8 => Trad::W_AUGUST,
		9 => Trad::W_SEPTEMBER,
		10 => Trad::W_OCTOBER,
		11 => Trad::W_NOVEMBER,
		12 => Trad::W_DECEMBER
	);

	public function __construct() {
		global $config;
		$this->events = Text::unhash(get_file(FILE_EVENTS));
		$this->tags = Text::unhash(get_file(FILE_TAGS));
	}

	public static function getInstance($project = NULL) {
		if (!isset(self::$instance)) {
			self::$instance = new Manager();
		}
		return self::$instance;
	}

	protected function save() {
		update_file(FILE_EVENTS, Text::hash($this->events));
		update_file(FILE_TAGS, Text::hash($this->tags));
	}

	public function getDay($day) {
		$events = array();
		foreach ($this->events as $id => $e) {
			if (date('Ymd', $e['day']) == date('Ymd', $day)) {
				$events[$id] = $e;
			}
		}
		uasort($events, array($this, 'compare'));
		return $events;
	}
	public function getWeek($day) {
		$events = array();
		foreach ($this->events as $id => $e) {
/*			if (date('YW', $e['day']) == date('YW', $day)) {
				$e['title'] = date('YW', $e['day']);
				$events[$id] = $e;
			}*/
			if (($e['day'] - $day) < 7*24*3600
				&& date('W', $e['day']) == date('W', $day)
			) {
				$e['title'] = date('YW', $e['day']);
				$events[$id] = $e;
			}
		}
		uasort($events, array($this, 'compare'));
		return $events;
	}
	public function getMonth($day) {
		$events = array();
		foreach ($this->events as $id => $e) {
			if (date('Ym', $e['day']) == date('Ym', $day)) {
				$events[$id] = $e;
			}
		}
		uasort($events, array($this, 'compare'));
		return $events;
	}

	public function get() {
		$events = array();
		foreach ($this->events as $id => $e) {
			$events[$id] = $e;
		}
		uasort($events, array($this, 'compare'));
		return $events;
	}

	public function compare($a, $b) {
		if ($a['day'] < $b['day']) { return -1; }
		if ($a['day'] == $b['day']) { return 0; }
		return 1;
	}

	public function getEvent($id) {
		if (isset($this->events[$id])) { return $this->events[$id]; }
		return false;
	}

	public function lastInserted() { return $this->last_inserted; }

	protected function checkPost($post) {
		if (!isset($post['title'])
			|| !isset($post['comment'])
			|| !isset($post['day_start_day'])
			|| !isset($post['day_start_month'])
			|| !isset($post['day_start_year'])
			|| !isset($post['day_end_day'])
			|| !isset($post['day_end_month'])
			|| !isset($post['day_end_year'])
			|| !isset($post['hour_start_hour'])
			|| !isset($post['hour_start_min'])
			|| !isset($post['hour_end_hour'])
			|| !isset($post['hour_end_min'])
			|| !isset($post['tags'])
		) {
			return Trad::A_ERROR_FORM;
		}
		if (empty($post['title'])) {
			return Trad::A_ERROR_EMPTY_TITLE;
		}

		if (!array_key_exists($post['day_start_month'], self::$months)
			|| !array_key_exists($post['day_end_month'], self::$months)
		) {
			return Trad::A_ERROR_FORM;
		}
		if (empty($post['day_start_year'])) {
			$post['day_start_year'] = date('Y');
		}
		if (empty($post['day_start_day'])) {
			$post['day_start_day'] = date('d');
		}
		if (empty($post['day_end_year'])) {
			$post['day_end_year'] = $post['day_start_year'];
		}
		if (empty($post['day_end_day'])) {
			$post['day_end_day'] = $post['day_start_day'];
		}
		$tags = array();
		foreach (explode(',', $post['tags']) as $t) {
			$t = Text::purge($t);
			if (!empty($t)) { $tags[] = $t; }
		}
		$post['tags'] = $tags;
		return $post;
	}

	public function add($post) {
		$post = $this->checkPost($post);
		if (!is_array($post)) { return $post; }

		$day_start = new DateTime();
		$day_start->setDate(
			intval($post['day_start_year']),
			intval($post['day_start_month']),
			intval($post['day_start_day'])
		);
		$day_start->setTime(
			intval($post['hour_start_hour']),
			intval($post['hour_start_min'])
		);
		$day_start2 = clone $day_start;
		$day_start2->setTime(
			intval($post['hour_end_hour']),
			intval($post['hour_end_min'])
		);

		$duration = $day_start2->getTimestamp()-$day_start->getTimestamp();
		if ($duration < 0
			|| $day_start->format('Ymd') != $day_start2->format('Ymd')
		) {
			$duration = 0;
		}

		$day_end = new DateTime();
		$day_end->setDate(
			intval($post['day_end_year']),
			intval($post['day_end_month']),
			intval($post['day_end_day'])
		);
		$day_end->setTime(
			intval($post['hour_end_hour']),
			intval($post['hour_end_min'])
		);
		if ($day_end->getTimestamp() < $day_start->getTimestamp()) {
			$day_end = clone $day_start;
		}

		$this->last_inserted = null;

		$one_day = new DateInterval('P1D');
		while ($day_start->getTimestamp() <= $day_end->getTimestamp()) {
			$id = Text::randomKey(32);
			if (!$this->last_inserted) {
				$this->last_inserted = $id;
			}
			$this->events[$id] = array(
				'title' => Text::chars($post['title']),
				'comment' => $post['comment'],
				'day' => $day_start->getTimestamp(),
				'duration' => $duration,
				'tags' => $post['tags']
			);
			$this->addTags($id, $post['tags']);
			$day_start->add($one_day);
		}

		$this->save();
		return true;
	}

	public function edit($id, $post) {
		$post = $this->checkPost($post);
		if (!is_array($post)) { return $post; }

		$day = new DateTime();
		$day->setDate(
			intval($post['day_start_year']),
			intval($post['day_start_month']),
			intval($post['day_start_day'])
		);
		$day->setTime(
			intval($post['hour_start_hour']),
			intval($post['hour_start_min'])
		);
		$day2 = clone $day;
		$day2->setTime(
			intval($post['hour_end_hour']),
			intval($post['hour_end_min'])
		);

		$duration = $day2->getTimestamp()-$day->getTimestamp();
		if ($duration < 0 || $day->format('Ymd') != $day2->format('Ymd')) {
			$duration = 0;
		}

		$this->addTags(
			$id,
			array_diff($post['tags'], $this->events[$id]['tags'])
		);
		$this->removeTags(
			$id,
			array_diff($this->events[$id]['tags'], $post['tags'])
		);

		$this->events[$id] = array(
			'title' => Text::chars($post['title']),
			'comment' => $post['comment'],
			'day' => $day->getTimestamp(),
			'duration' => $duration,
			'tags' => $post['tags']
		);

		$this->save();
		return true;
	}

	public function delete($id) {
		$this->removeTags($id, $this->events[$id]['tags']);
		unset($this->events[$id]);
		$this->save();
		return true;
	}

	public function getTags() {
		return array_keys($this->tags);
	}
	public function addTags($id, $tags) {
		foreach ($tags as $t) {
			$this->tags[$t][] = $id;
		}
	}
	public function removeTags($id, $tags) {
		foreach ($tags as $t) {
			$key = array_search($id, $this->tags[$t]);
			if ($key !== false) {
				array_splice($this->tags[$t], $key, 1);
				if (empty($this->tags[$t])) { unset($this->tags[$t]); }
			}
		}
	}

	public static function previewEvents($events) {
		$html = '';
		$date = null;
		$evt = array();
		foreach ($events as $id => $e) {
			if (date('Ymd', $e['day']) != date('Ymd', $date)) {
				$html .= self::previewList($evt, $date);
				$evt = array($id => $e);
				$date = $e['day'];
			}
			else {
				$evt[$id] = $e;
			}
		}
		return $html.self::previewList($evt, $date);
	}
	protected static function previewList($events, $date) {
		if ($date == null) { return ''; }
		$class = 'div-day';
		if (date('Ymd', $date) < date('Ymd')) {
			$class .= ' done';
		}
		$html = '<div class="'.$class.'">'.self::date($date).'</div>';
		foreach ($events as $id => $e) {
			$html .= self::preview($id, $e);
		}
		return $html;
	}
	protected static function preview($id, $event) {
		global $config;
		$tags = Manager::tagsList($event['tags'], false);
		if (!empty($tags)) { $tags = '<p>'.$tags.'</p>'; }
		$class = 'div-event';
		if ($event['duration'] != 0) {
			$end = $event['day']+$event['duration'];
			if ($end < time()) {
				$class .= ' done';
			}
			$hour = str_replace(
				array('%start%', '%end%'),
				array(date('Hi', $event['day']), date('Hi', $end)),
				Trad::S_PERIOD
			);
		}
		else {
			if (date('Ymd', $event['day']) < date('Ymd')) {
				$class .= ' done';
			}
			$hour = Trad::S_ALL_DAY;
		}
		
		return ''
.'<div class="'.$class.'" id="event-'.$id.'">'
	.'<h2>'
		.'['.$hour.'] '
		.'<a href="'.Url::parse('events/'.$id).'">'
			.$event['title']
		.'</a>'
	.'</h2>'
	.\Michelf\Markdown::defaultTransform($event['comment'])
	.$tags
.'</div>';
	}

	public static function tagsList($tags, $empty = true) {
		$html = '';
		foreach ($tags as $t) {
			$html .= '<a href="'.Url::parse('tags/'.$t).'" class="tag">'.$t.'</a>';
		}
		if ($empty && empty($html)) { $html = '<i>'.Trad::S_EMPTY_TAGS.'</i>'; }
		return $html;
	}

	public static function getForm($post, $tags) {
		$list = '';
		sort($tags);
		foreach ($tags as $t) {
			$list .= '<span class="visible">'.$t.'</span>';
		}
		return ''
.'<label for="title">'.Trad::F_TITLE.'</label>'
.'<input type="text" name="title" id="title" value="'.Text::chars($post['title']).'" />'
.'<label for="comment">'.Trad::F_COMMENT.'</label>'
.'<textarea name="comment" id="comment">'.Text::chars($post['comment']).'</textarea>'
.'<label for="day_start_day">'.Trad::F_DAY.'</label>'
.str_replace(
	array('%from%', '%to%'),
	array(
		Manager::pickDate('day_start', $post),
		Manager::pickDate('day_end', $post)
	),
	Trad::S_FROMTO_DAY
)
.'<label for="hour_start_hour">'.Trad::F_HOUR.'</label>'
.str_replace(
	array('%from%', '%to%'),
	array(
		Manager::pickHour('hour_start', $post),
		Manager::pickHour('hour_end', $post)
	),
	Trad::S_FROMTO_HOUR
)
.'<label for="addTag">'.Trad::F_TAGS.'</label>'
.'<div class="editTags">'
	.'<span></span>'
	.'<input type="text" name="addTag" id="addTag" placeholder="'.Trad::F_ADD.'" />'
	.'<input type="hidden" name="tags" id="tags" value="'.Text::chars($post['tags']).'" />'
.'</div>'
.'<div class="pick-tag">'.$list.'</div>';
	}

	public static function pickDate($id, $post) {
		return '<span class="pickDay">'
.'<input type="text" pattern="\d*" name="'.$id.'_day" id="'.$id.'_day"'
	.' value="'.Text::chars($post[$id.'_day']).'" class="day"/>'
.'<select name="'.$id.'_month" id="'.$id.'_month" class="month">'
	.Text::options(self::$months, $post[$id.'_month'])
.'</select>'
.'<input type="text" pattern="\d*" name="'.$id.'_year" id="'.$id.'_year"'
	.' value="'.Text::chars($post[$id.'_year']).'" class="year" />'
		.'</span>';
	}

	public static function pickHour($id, $post) {
		return '<span class="pickHour">'
.'<input type="text" pattern="\d*" name="'.$id.'_hour" id="'.$id.'_hour"'
	.' value="'.Text::chars($post[$id.'_hour']).'" class="hour" />'
.'<input type="text" pattern="\d*" name="'.$id.'_min" id="'.$id.'_min"'
	.' value="'.Text::chars($post[$id.'_min']).'" class="min" />'
		.'</span>';
	}

	public static function barTop($calendar = false) {
		$c = '';
		if ($calendar) {
			$c = '<a href="#" class="a-calendar">
				'.mb_strtolower(Trad::W_CALENDAR).'
			</a>';
		}
		return '
			<div class="div-actions-top">
				<a href="#" class="previous">
					'.mb_strtolower(Trad::W_PREVIOUS).'
				</a>
				<a href="#" class="next">
					'.mb_strtolower(Trad::W_NEXT).'
				</a>
				'.$c.'
				<!--<a href="#" class="show-done">
					'.mb_strtolower(Trad::V_SHOW_DONE).'
				</a>-->
			</div>
		';
	}
	public static function barBottom($calendar = false) {
		$c = '';
		if ($calendar) {
			$c = '<a href="#" class="a-calendar">
				'.mb_strtolower(Trad::W_CALENDAR).'
			</a>';
		}
		return '
			<div class="div-actions-bottom">
				<a href="#" class="previous">
					'.mb_strtolower(Trad::W_PREVIOUS).'
				</a>
				<a href="#" class="next">
					'.mb_strtolower(Trad::W_NEXT).'
				</a>
				'.$c.'
				<!--<a href="#" class="show-done">
					'.mb_strtolower(Trad::V_SHOW_DONE).'
				</a>-->
			</div>
		';
	}

	public static function date($date) {
		return ''
			.'<span class="span-day">'.date('d', $date).'</span>'
			.' '
			.'<span class="span-month">'
				.(Manager::$months[date('n', $date)])
			.'</span>'
			.' '
			.'<span class="span-year">'.date('Y', $date).'</span>'
		;
	}

}

?>