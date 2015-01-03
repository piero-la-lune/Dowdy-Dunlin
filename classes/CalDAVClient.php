<?php


class CalDAVClient {

	protected $host = '';
	protected $path = '';
	protected $calendar = '';
	protected $curl_settings = array(
		CURLOPT_HTTPAUTH => CURLAUTH_DIGEST,
		CURLOPT_SSL_VERIFYPEER => 0,
		CURLOPT_USERAGENT => 'Dowdy Dunlin',
		CURLOPT_RETURNTRANSFER => 1,
		CURLINFO_HEADER_OUT => 1
	);

	public function __construct($url, $user, $pass, $calendar = '') {
		if (preg_match('#((.+)://([^/]+))(/.*)#', $url, $m) === 1) {
			$this->host = $m[1];
			$this->path = $m[4];
		}
		else {
			# Should not happen, but just in case...
			$this->host = $url;
			$this->path = '';
		}
		$this->curl_settings[CURLOPT_USERPWD] = $user.':'.$pass;
		$this->calendar = $calendar;
	}

	public function propFind($path, $elm, $depth = 0) {
		# Create XML body
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->formatOutput = true;
		$root = $dom->createElementNS('DAV:', 'd:propfind');
		$root->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:cs', 'http://calendarserver.org/ns/');
		$root->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:c', 'urn:ietf:params:xml:ns:caldav');
		$prop = $dom->createElement('d:prop');
		foreach($elm as $e) {
			$prop->appendChild($dom->createElement($e));
		}
		$dom->appendChild($root)->appendChild($prop);
		$xml = $dom->saveXML();
		# Add headers and CURL settings
		$settings = array(
			CURLOPT_CUSTOMREQUEST => 'PROPFIND',
			CURLOPT_HTTPHEADER => array(
				'Depth: '.$depth,
				'Content-Type: application/xml',
			),
			CURLOPT_URL => $this->host.$path,
			CURLOPT_POSTFIELDS => $xml
		);
		# Do the request
		$ch = curl_init();
		curl_setopt_array($ch, $this->curl_settings);
		curl_setopt_array($ch, $settings);
		return $this->parse_multistatus($ch);
	}
	public function report($path, $elm, $depth = 1) {
		# Create XML body
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->formatOutput = true;
		$root = $dom->createElementNS('urn:ietf:params:xml:ns:caldav', 'c:calendar-query');
		$root->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:d', 'DAV:');
		$prop = $dom->createElement('d:prop');
		foreach($elm as $e) {
			$prop->appendChild($dom->createElement($e));
		}
		$dom->appendChild($root)->appendChild($prop);
		$filter = $dom->createElement('c:filter');
		$comp_filter = $dom->createElement('c:comp-filter');
		$comp_filter->setAttribute('name', 'VCALENDAR');
		$filter->appendChild($comp_filter);
		$root->appendChild($filter);
		$xml = $dom->saveXML();
		# Add headers and CURL settings
		$settings = array(
			CURLOPT_CUSTOMREQUEST => 'REPORT',
			CURLOPT_HTTPHEADER => array(
				'Depth: '.$depth,
				'Content-Type: application/xml',
			),
			CURLOPT_URL => $this->host.$path,
			CURLOPT_POSTFIELDS => $xml,
		);
		# Do the request
		$ch = curl_init();
		curl_setopt_array($ch, $this->curl_settings);
		curl_setopt_array($ch, $settings);
		return $this->parse_multistatus($ch);
	}
	public function put($path, $content) {
		# Add headers and CURL settings
		$settings = array(
			CURLOPT_CUSTOMREQUEST => 'PUT',
			CURLOPT_HTTPHEADER => array(
				'Content-Type: text/calendar; charset=utf-8'
			),
			CURLOPT_URL => $this->host.$path,
			CURLOPT_POSTFIELDS => $content,
		);
		# Do the request
		$ch = curl_init();
		curl_setopt_array($ch, $this->curl_settings);
		curl_setopt_array($ch, $settings);
		curl_exec($ch);
		$infos = curl_getinfo($ch);
		return ($infos['http_code'] == 204) || ($infos['http_code'] == 201);
	}
	public function delete($path) {
		# Add headers and CURL settings
		$settings = array(
			CURLOPT_CUSTOMREQUEST => 'DELETE',
			CURLOPT_URL => $this->host.$path,
		);
		# Do the request
		$ch = curl_init();
		curl_setopt_array($ch, $this->curl_settings);
		curl_setopt_array($ch, $settings);
		curl_exec($ch);
		$infos = curl_getinfo($ch);
		return $infos['http_code'] == 204;
	}

	public function get_calendar() {
		$rep = current($this->propFind($this->path, array('d:current-user-principal')));
		if ($rep === false) { return false; }
		$this->register_namespace($rep);
		$user_url = (string)current($rep->xpath('.//d:href'));
		if ($user_url === false) { return false; }
		$rep = current($this->propFind($user_url, array('c:calendar-home-set')));
		if ($rep === false) { return false; }
		$this->register_namespace($rep);
		$calendar_home_url = (string)current($rep->xpath('.//d:href'));
		$rep = $this->propFind($calendar_home_url, array(
			'd:resourcetype',
			'cs:getctag',
			'c:supported-calendar-component-set'
		), 1);
		foreach ($rep as $url => $r) {
			$this->register_namespace($r);
			if (count($r->xpath('.//c:calendar')) == 1
				&& count($r->xpath('.//c:comp[@name="VEVENT"]')) == 1) {
				return $url;
			}
		}
		return false;
	}

# Not supported by Baikal server for now
/*	public function get_sync_token() {
		$rep = current($this->propFind($this->calendar, array('d:displayname')));
		if ($rep === false) { return false; }
		$this->register_namespace($rep);
		$a = $rep->xpath('./d:sync-token');
		if (count($a) == 1) {
			echo (string)$a[0];
			return (string)$a[0];
		}
		return '';
	}*/

	public function download_calendar() {
		$rep = $this->report($this->calendar, array('c:calendar-data'));
		$manager = Manager::getInstance();
		$manager->deleteVEvents();
		$caldavs = array();
		foreach ($rep as $url => $e) {
			$this->register_namespace($e);
			$data = $e->xpath('.//cal:calendar-data');
			if (count($data) == 1) {
				$vcal = Sabre\VObject\Reader::read((string)$data[0]);
				if (isset($vcal->VEVENT) && isset($vcal->VEVENT[0])) {
					if ($manager->addVEvent($url, $vcal->VEVENT[0])) {
						$caldavs[$url] = (string)$data[0];
					}
				}
			}
		}
		update_file(FILE_CALDAV, Text::hash($caldavs));
	}

	public function refresh_events() {
		# Could be improved
		$this->download_calendar();
	}

	protected function do_vevent($vcal, $post) {
		$summary = Text::unchars($post['title']);
		$description = $post['comment'];
		$categories = implode(',', $post['tags']);
		if (empty($post['time_start']) || empty($post['time_end'])) {
			$dtstart = $post['day_start'];
			$dtend = date('Ymd', strtotime($post['day_end'])+3600*24);
		}
		else {
			$dtstart = $post['day_start'].'T'.$post['time_start'].'00';
			$dtend = $post['day_end'].'T'.$post['time_end'].'00';
		}
		$vcal->VEVENT[0]->SUMMARY = $summary;
		$vcal->VEVENT[0]->DESCRIPTION = $description;
		if (!empty($categories)) {
			$vcal->VEVENT[0]->CATEGORIES = $categories;
		}
		$vcal->VEVENT[0]->DTSTART = $dtstart;
		$vcal->VEVENT[0]->DTEND = $dtend;
		$seq = intval((string)$vcal->VEVENT[0]->SEQUENCE);
		if ($seq) {
			$vcal->VEVENT[0]->SEQUENCE = $seq+1;
		}
		else {
			$vcal->VEVENT[0]->SEQUENCE = 1;
		}
		$vcal->VEVENT[0]->{'LAST-MODIFIED'} = gmdate('Ymd\THis\Z');
		$vcal->VEVENT[0]->DTSTAMP = gmdate('Ymd\THis\Z');
	}

	public function add_vevent($id, $post) {
		$path = $this->calendar.$id.'.ics';
		$vcal = new Sabre\VObject\Component\VCalendar();
		$vcal->add('VEVENT', array());
		$this->do_vevent($vcal, $post);
		$vcal->VEVENT[0]->CREATED = gmdate('Ymd\THis\Z');
		$vcal->VEVENT[0]->UID = $id;
		$caldavs = Text::unhash(get_file(FILE_CALDAV));
		$caldavs[$path] = $vcal->serialize();
		update_file(FILE_CALDAV, Text::hash($caldavs));
		if ($this->put($path, $vcal->serialize())) {
			return $path;
		}
		return false;
	}

	public function update_vevent($url, $post) {
		$caldavs = Text::unhash(get_file(FILE_CALDAV));
		if (!isset($caldavs[$url])) { return false; } # Should not happen
		$vcal = Sabre\VObject\Reader::read($caldavs[$url]);
		$this->do_vevent($vcal, $post);
		return $this->put($url, $vcal->serialize());
	}

	public function delete_vevent($url) {
		$caldavs = Text::unhash(get_file(FILE_CALDAV));
		if (!isset($caldavs[$url])) { return false; } # Should not happen
		if (!$this->delete($url)) { return false; }
		unset($caldavs[$url]);
		update_file(FILE_CALDAV, Text::hash($caldavs));
		return true;
	}

	public function set_calendar($calendar) {
		$this->calendar = $calendar;
	}

	protected function parse_multistatus($ch) {
		$response = curl_exec($ch);
		$infos = curl_getinfo($ch);
		# Return the result
		if ($infos['http_code'] == 207) {
			$xml = new SimpleXMLElement($response);
			$this->register_namespace($xml);
			$responses = array();
			foreach ($xml->xpath('d:response') as $rep) {
				$this->register_namespace($rep);
				$props = $rep->xpath('.//d:prop');
				$href = $rep->xpath('./d:href');
				if (current($rep->xpath('.//d:status')) == 'HTTP/1.1 200 OK'
					&& count($props) > 0 && count($href) > 0
				) {
					$responses[(string)$href[0]] = $props[0];
				}
			}
			return $responses;
		}
		return array();
	}

	protected function register_namespace($elm) {
		$elm->registerXPathNamespace('d', 'DAV:');
		$elm->registerXPathNamespace('cs', 'http://calendarserver.org/ns/');
		$elm->registerXPathNamespace('c', 'urn:ietf:params:xml:ns:caldav');
	}
}



?>