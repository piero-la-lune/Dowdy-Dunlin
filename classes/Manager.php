<?php

# Events model :
#	title => escaped (string)
#	comment => (string)
#	day_start => (string) // AAAAMMJJ
#	day_end => (string) // AAAAMMJJ
#	time_start => (string) // HHMM
#	time_end => (string) // HHMM
#	tags => (array)
#	caldav => (string)

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
		$day = date('Ymd', $day);
		$events = array();
		foreach ($this->events as $id => $e) {
			if ($e['day_start'] <= $day && $e['day_end'] >= $day) {
				$events[$id] = $e;
			}
		}
		uasort($events, array($this, 'compare'));
		return $events;
	}
	public function getWeek($day) {
		$aweek = 7*24*3600;
		$week = (int)date('W', $day);
		$events = array();
		foreach ($this->events as $id => $e) {
			$dst = strtotime($e['day_start']);
			$det = strtotime($e['day_end']);
			if ((int)date('W', $dst) <= $week
				&& (int)date('W', $det) >= $week
				&& ($dst - $aweek) <= $day
				&& ($det + $aweek) >= $day
			) {
				$events[$id] = $e;
			}
		}
		uasort($events, array($this, 'compare'));
		return $events;
	}
	public function getMonth($day) {
		$events = array();
		$month = date('Ym', $day);
		foreach ($this->events as $id => $e) {
			if (substr($e['day_start'], 0, -2) <= $month
				&& substr($e['day_end'], 0, -2) >= $month
			) {
				$events[$id] = $e;
			}
		}
		uasort($events, array($this, 'compare'));
		return $events;
	}

	public function compare($a, $b) {
		if ($a['day_start'] < $b['day_start']) { return -1; }
		elseif ($a['day_start'] == $b['day_start']) {
			if ($a['time_start'] < $b['time_start']) { return -1; }
			elseif ($a['time_start'] == $b['time_start']) { return 0; }
			return 1;
		}
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
		$syear = $post['day_start_year'];
		if (empty($syear)) { $syear = date('Y'); }
		while (strlen($syear) < 4) { $syear = '0'.$syear; }
		$smonth = $post['day_start_month'];
		if (strlen($smonth) < 2) { $smonth = '0'.$smonth; }
		$sday = $post['day_start_day'];
		if (empty($sday)) { $sday = date('d'); }
		if (strlen($sday) < 2) { $sday = '0'.$sday; }
		if (!checkdate($smonth, $sday, $syear)) {
			return Trad::A_ERROR_DAY_START;
		}
		$post['day_start'] = $syear.$smonth.$sday;
		$eyear = $post['day_end_year'];
		if (empty($eyear)) { $eyear = date('Y'); }
		while (strlen($eyear) < 4) { $eyear = '0'.$eyear; }
		$emonth = $post['day_end_month'];
		if (strlen($emonth) < 2) { $emonth = '0'.$emonth; }
		$eday = $post['day_end_day'];
		if (empty($eday)) {
			$eday = $sday;
			$emonth = $smonth;
			$eyear = $syear;
		}
		if (strlen($eday) < 2) { $eday = '0'.$eday; }
		if (!checkdate($emonth, $eday, $eyear)) {
			return Trad::A_ERROR_DAY_END;
		}
		$post['day_end'] = $eyear.$emonth.$eday;
		if ($post['day_start'] > $post['day_end']) {
			return Trad::A_ERROR_DAYS;
		}
		$shour = (int)$post['hour_start_hour'];
		$smin = (int)$post['hour_start_min'];
		$ehour = (int)$post['hour_end_hour'];
		$emin = (int)$post['hour_end_min'];
		if ((!$shour && !$smin) || (!$ehour && !$emin)) {
			$post['time_start'] = null;
			$post['time_end'] = null;
		}
		else {
			if ($shour < 0 || $shour > 23 || $smin < 0 || $smin > 59) {
				return Trad::A_ERROR_TIME_START;
			}
			if ($ehour < 0 || $ehour > 23 || $emin < 0 || $emin > 59) {
				return Trad::A_ERROR_TIME_END;
			}
			if ($shour < 10) { $shour = '0'.$shour; }
			if ($smin < 10) { $smin = '0'.$smin; }
			$post['time_start'] = $shour.$smin;
			if ($ehour < 10) { $ehour = '0'.$ehour; }
			if ($emin < 10) { $emin = '0'.$emin; }
			$post['time_end'] = $ehour.$emin;
			if ($post['day_start'] == $post['day_end']
				&& $post['time_end'] < $post['time_start']
			) {
				return Trad::A_ERROR_TIMES;
			}
		}
		if (!isset($post['caldav'])) {
			$post['do_caldav'] = false;
		}
		else {
			$post['do_caldav'] = ($post['caldav'] == 'oui');
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
		$id = Text::randomKey(32);
		global $config;
		$caldav = '';
		if (isset($config['caldav']) && $post['do_caldav']) {
			$client = new CalDAVClient(
				$config['caldav']['url'],
				$config['caldav']['user'],
				$config['caldav']['pass'],
				$config['caldav']['calendar']
			);
			$rep = $client->add_vevent($id, $post);
			if ($rep !== false) {
				$caldav = $rep;
			}
			else {
				return Trad::A_ERROR_CALDAV_ADD;
			}
		}
		$this->events[$id] = array(
			'title' => Text::chars($post['title']),
			'comment' => $post['comment'],
			'day_start' => $post['day_start'],
			'day_end' => $post['day_end'],
			'time_start' => $post['time_start'],
			'time_end' => $post['time_end'],
			'tags' => $post['tags'],
			'caldav' => $caldav
		);
		$this->addTags($id, $post['tags']);
		$this->last_inserted = $id;
		$this->save();
		return true;
	}

	public function addVEvent($url, $e) {
		if (!isset($e->DTSTART) || !isset($e->DTEND) || !isset($e->SUMMARY)) {
			# Malformed event, we ignore it
			return false;
		}
		if (isset($e->RRULE) || isset($e->DURATION)) {
			# This is a repeated event, we ignore it
			return false;
		}
		$start = (string)$e->DTSTART;
		$sa = explode('T', $start);
		$end = (string)$e->DTEND;
		$ea = explode('T', $end);
		if (strlen($sa[0]) != 8 || strlen($ea[0]) != 8
			|| !checkdate(substr($sa[0], 4, 2), substr($sa[0], 6, 2), substr($sa[0], 0, 4))
			|| !checkdate(substr($ea[0], 4, 2), substr($ea[0], 6, 2), substr($ea[0], 0, 4))
		) {
			return false;
		}
		if (!isset($sa[1]) || !isset($ea[1])) {
			$day_start = $sa[0];
			$day_end = date('Ymd', strtotime($ea[0])-3600*24);
			$time_start = null;
			$time_end = null;
		}
		else {
			if (strlen($sa[1]) != 6 || strlen($ea[1]) != 6
				|| (int)substr($sa[1], 0, 2) < 0 || (int)substr($sa[1], 0, 2) > 23
				|| (int)substr($ea[1], 0, 2) < 0 || (int)substr($ea[1], 0, 2) > 23
				|| (int)substr($sa[1], 2, 2) < 0 || (int)substr($sa[1], 2, 2) > 59
				|| (int)substr($ea[1], 2, 2) < 0 || (int)substr($ea[1], 2, 2) > 59
			) {
				return false;
			}
			$day_start = $sa[0];
			$day_end = $ea[0];
			$time_start = substr($sa[1], 0, 4);
			$time_end = substr($ea[1], 0, 4);
		}
		$tags = array();
		foreach (explode(',', (string)$e->CATEGORIES) as $t) {
			$t = Text::purge($t);
			if (!empty($t)) { $tags[] = $t; }
		}
		$id = Text::randomKey(32);
		$this->events[$id] = array(
			'title' => Text::chars((string)$e->SUMMARY),
			'comment' => (string)$e->DESCRIPTION,
			'day_start' => $day_start,
			'day_end' => $day_end,
			'time_start' => $time_start,
			'time_end' => $time_end,
			'tags' => $tags,
			'caldav' => $url
		);
		$this->addTags($id, $tags);
		$this->last_inserted = $id;
		$this->save();
		return true;
	}

	public function edit($id, $post) {
		if (!isset($this->events[$id])) {
			return Trad::A_ERROR_NO_EVENT;
		}
		$post = $this->checkPost($post);
		if (!is_array($post)) { return $post; }
		global $config;
		$caldav = $this->events[$id]['caldav'];
		if (isset($config['caldav'])) {
			$client = new CalDAVClient(
				$config['caldav']['url'],
				$config['caldav']['user'],
				$config['caldav']['pass'],
				$config['caldav']['calendar']
			);
			if ($post['do_caldav']) {
				if (!empty($caldav)) {
					if (!$client->update_vevent($caldav, $post)) {
						return Trad::A_ERROR_CALDAV_UPDATE;
					}
				}
				else {
					$rep = $client->add_vevent($id, $post);
					if ($rep !== false) {
						$caldav = $rep;
					}
					else {
						return Trad::A_ERROR_CALDAV_ADD;
					}
				}
			}
			elseif (!empty($this->events[$id]['caldav'])) {
				if (!$client->delete_vevent($this->events[$id]['caldav'])) {
					return Trad::A_ERROR_CALDAV_DELETE;
				}
				$caldav = '';
			}
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
			'day_start' => $post['day_start'],
			'day_end' => $post['day_end'],
			'time_start' => $post['time_start'],
			'time_end' => $post['time_end'],
			'tags' => $post['tags'],
			'caldav' => $caldav
		);
		$this->save();
		return true;
	}

	public function delete($id) {
		if (!isset($this->events[$id])) {
			return Trad::A_ERROR_NO_EVENT;
		}
		if (!empty($this->events[$id]['caldav'])) {
			global $config;
			$client = new CalDAVClient(
				$config['caldav']['url'],
				$config['caldav']['user'],
				$config['caldav']['pass'],
				$config['caldav']['calendar']
			);
			if (!$client->delete_vevent($this->events[$id]['caldav'])) {
				return Trad::A_ERROR_CALDAV_DELETE;
			}
		}
		$this->removeTags($id, $this->events[$id]['tags']);
		unset($this->events[$id]);
		$this->save();
		return true;
	}

	public function deleteVEvents() {
		foreach ($this->events as $id => $e) {
			if (!empty($e['caldav'])) {
				$this->removeTags($id, $e['tags']);
				unset($this->events[$id]);
			}
		}
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

	public static function previewEvents($events, $days) {
		$html = '';
		foreach ($days as $day) {
			$day_html = '';
			foreach ($events as $id => $e) {
				if ($e['day_start'] <= $day && $e['day_end'] >= $day) {
					$day_html .= self::preview($id, $e, $day);
				}
			}
			if (!empty($day_html)) {
				$day_html = '<div class="div-day">'.self::date($day).'</div>'
					.$day_html;
			}
			$html .= $day_html;
		}
		return $html;
	}
	protected static function preview($id, $event, $day) {
		global $config;
		$tags = Manager::tagsList($event['tags'], false);
		if (!empty($tags)) { $tags = '<p>'.$tags.'</p>'; }
		# $class = 'div-event';
		$hour = array('', '');
		if ($event['time_start'] && $day == $event['day_start']) {
			$hour[0] = $event['time_start'];
		}
		if ($event['time_end'] && $day == $event['day_end']) {
			$hour[1] = $event['time_end'];
		}
		if ($hour != array('', '')) {
			$hour = '['.str_replace(
				array('%start%', '%end%'),
				array($hour[0], $hour[1]),
				Trad::S_PERIOD
			).'] ';
		}
		else { $hour = ''; }
		return ''
.'<div class="div-event" id="event-'.$id.'">'
	.'<h2>'
		.$hour
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
		$caldav = '';
		if ($post['caldav_enabled']) {
			$caldav = '<label for="caldav">'.Trad::F_CALDAV.'</label>'
				.'<select id="caldav" name="caldav">'
				.Text::options(array(
					'oui' => Trad::F_EXTERN,
					'non' => Trad::F_INTERN
				), $post['caldav'])
				.'</select>';
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
.'<div class="pick-tag">'.$list.'</div>'
.$caldav;
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
			.'<span class="span-day">'.(int)substr($date, 6, 2).'</span>'
			.' '
			.'<span class="span-month">'
				.(Manager::$months[(int)substr($date, 4, 2)])
			.'</span>'
			.' '
			.'<span class="span-year">'.(int)substr($date, 0, 4).'</span>'
		;
	}

}

?>