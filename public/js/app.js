function isset(elm) {
	return (typeof(elm) != 'undefined' && elm !== null);
}

function Ajax(elm, action) {
	this.post = [];
	this.elm = undefined;
	this.loader = undefined;
	if (elm) {
		this.elm = elm;
		this.loader = document.createElement('span');
		this.loader.className = 'loading';
		this.loader.innerHTML = '<i class="n1"></i><i class="n2"></i><i class="n3"></i>';
		this.elm.parentNode.replaceChild(this.loader, this.elm);
	}
	this.post.push('action='+action);
	this.post.push('page='+page);
	this.addParam = function(name, value) {
		this.post.push(name+'='+encodeURIComponent(value));
	};
	this.send = function(callback_success, callback_error) {
		var ajax = this;
		var xhr = new XMLHttpRequest();
		xhr.open('POST', ajax_url);
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		xhr.send(this.post.join('&'));
		xhr.onreadystatechange = function() {
			if (xhr.readyState == xhr.DONE) {
				if (xhr.status == 200) {
					var ans = JSON.parse(xhr.responseText);
					if (ans['status'] == 'success') {
						if (typeof callback_success != 'undefined') {
							callback_success(ans);
						}
					}
					else {
						if (typeof callback_error != 'undefined') {
							callback_error(ans);
						}
					}
				}
				else if (xhr.status == 403) {
					alert(m_error_login);
				}
				else {
					alert(m_error_ajax);
				}
				ajax.cancel();
			}
		};
	};
	this.cancel = function() {
		if (this.loader) {
			this.loader.parentNode.replaceChild(this.elm, this.loader);
		}
	};
}

if (isset(document.getElementById('logout'))) {
	document.getElementById('logout').onclick = function() {
		document.getElementById('form-logout').submit();
		return false;
	};
}

function onload_tags() {
	var editTags = document.querySelector('.editTags');
	var list = editTags.querySelector('span');
	var tags = document.getElementById('tags');
	var addTag = document.getElementById('addTag');
	var pick = document.querySelector('.pick-tag');
	var pick_tags = pick.querySelectorAll('span');
	function update_tags_input() {
		var as = editTags.querySelectorAll('.tag');
		var arr = [];
		for (var i=0; i<as.length; i++) { arr.push(as[i].innerHTML); }
		tags.value = arr.join(',');
	}
	function remove_tags(e) {
		e.target.parentNode.removeChild(e.target);
		update_tags_input();
		return false;
	}
	function append_tag(tag) {
		var a = document.createElement('a');
		a.href = '#';
		a.className = 'tag';
		a.innerHTML = tag;
		list.appendChild(a);
		a.onclick = remove_tags;
	}
	function add_tag() {
		if (addTag.value !== '') {
			append_tag(addTag.value);
			addTag.value = '';
			update_tags_input();
		}
	}
	function update_tags() {
		if (tags.value !== '') {
			var arr = tags.value.split(/,/);
			for (var i=0; i<arr.length; i++) { append_tag(arr[i]); }
		}
	}
	var keepFocus = false;
	pick.onmousedown = function(e) {
		if (e.target.className == 'visible') {
			// on n'a pas cliqué sur la barre de défilemenent ni sur la bordure
			// mais bien sur un nom de tag
			addTag.value = e.target.innerHTML;
			add_tag();
			keepFocus = true; // on veut que addTag garde le focus
		}
	};
	addTag.onkeydown = function(e) {
		if ((('keyCode' in e) && (e.keyCode == 13 || e.keyCode == 188)) ||
			(('key' in e) && (e.key == 'Enter' || e.key == ','))) {
			add_tag();
			addTag.blur();
			addTag.focus();
			return false;
		}
		if (('keyCode' in e && e.keyCode == 9) ||
			('key' in e && e.key == 'Tab')) {
			var elm = form.list.querySelector('.visible');
			if (elm !== null) {
				// on récupère le premier élément de la liste déroulante
				addTag.value = elm.innerHTML;
				add_tag();
				addTag.blur();
				addTag.focus();
			}
			return false;
		}
	};
	addTag.onfocus = function() {
		var pos = addTag.getBoundingClientRect();
		pick.style.left = pos.left+'px';
		pick.style.top = pos.bottom+'px';
		addTag.onkeyup(); // On initialise la liste en fonction de addTag
	};
	addTag.onblur = function(e) {
		if (!keepFocus) {
			pick.style.left = '-9999px';
			pick.style.top = '-9999px';
		}
		else {
			keepFocus = false;
			// pour Firefox qui ne doit pas apprécier qu'on empêche cette action
			setTimeout("document.getElementById('addTag').focus()", 10);
		}
	};
	addTag.onkeyup = function() {
		var val = addTag.value;
		for (var i=0; i<pick_tags.length; i++) {
			if (pick_tags[i].innerHTML.indexOf(val) === -1) {
				pick_tags[i].className = '';
			}
			else {
				pick_tags[i].className = 'visible';
			}
		}
	};
	tags.onupdate = update_tags;
	update_tags();
}
if (isset(document.querySelector('.editTags'))) { onload_tags(); }

function show_done() {
	var done = document.querySelectorAll('.done');
	for (var i=0; i<done.length; i++) {
		done[i].style.display = 'block';
	}
	return false;
}
var a_done = document.querySelectorAll('.show-done');
if (isset(a_done[0])) {
	a_done[0].onclick = show_done;
}
if (isset(a_done[1])) {
	a_done[1].onclick = show_done;
}

var no = 0;
function changeNo(e) {
	var ajax = new Ajax(e, 'changeNo');
	ajax.addParam('no', no);
	ajax.send(function(ans) {
		var article = document.querySelector('article');
		article.innerHTML = ans['html'];
	});
}
function next(e) {
	no++;
	changeNo(e.target);
	return false;
}
function previous(e) {
	no--;
	changeNo(e.target);
	return false;
}
var a_next = document.querySelectorAll('.next');
if (isset(a_next[0])) {
	a_next[0].onclick = next;
}
if (isset(a_next[1])) {
	a_next[1].onclick = next;
}
var a_previous = document.querySelectorAll('.previous');
if (isset(a_previous[0])) {
	a_previous[0].onclick = previous;
}
if (isset(a_previous[1])) {
	a_previous[1].onclick = previous;
}

var a_edit = document.querySelector('.edit');
if (isset(a_edit)) {
	a_edit.onclick = function() {
		var article = document.querySelector('.display-text');
		var text = a_edit.dataset.text;
		if (isset(article)) {
			article.className = 'display-form';
			a_edit.dataset.text = a_edit.innerHTML;
			a_edit.innerHTML = text;
		}
		else {
			document.querySelector('.display-form').className = 'display-text';
			a_edit.dataset.text = a_edit.innerHTML;
			a_edit.innerHTML = text;
		}
		return false;
	};
}
var a_delete = document.querySelector('.delete');
if (isset(a_delete)) {
	a_delete.onclick = function() {
		if (confirm(m_confirm_delete)) {
			var form = document.querySelector('article form');
			form.querySelector('.npt-action').value = 'delete';
			form.submit();
		}
		return false;
	};
}

var a_calendar = document.querySelector('.a-calendar');
if (isset(a_calendar)) {
	var calendar = document.querySelector('.calendar');
	a_calendar.onclick = function(e) {
		e.preventDefault(); e.stopPropagation();
		if (calendar.className == 'calendar') {
			calendar.className = 'calendar display';
			document.querySelector('.div-hover').style.display = 'block';
			document.querySelector('.div-hover').onclick = function() {
				a_calendar.click();
			};
		}
		else {
			calendar.className = 'calendar';
			document.querySelector('.div-hover').style.display = 'none';
		}
		return false;
	};
}

var mois = new Array("Janvier", "Février", "Mars", "Avril", "Mai", "Juin",
	"Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre");

function gotoday(e) {
	no = parseInt(e.target.dataset.no, 10);
	changeNo(a_calendar);
	a_calendar.click();
}
function draw_calendar(month, year) {
	var html = '<tr>';
	var date = new Date();
	var today = new Date();
	date.setFullYear(year);
	date.setMonth(month);
	date.setDate(1);
	var nb = date.getDay()-1;
	if (nb < 0) { nb = 6; }
	for (var i=0; i<nb; i++) { html += '<td class="empty"></td>'; }
	while (date.getMonth() == month) {
		var nno = Math.round((date.getTime()-today.getTime())/(1000*3600*24));
		if (date.getDate() == today.getDate() &&
			date.getMonth() == today.getMonth() &&
			date.getFullYear() == today.getFullYear()
		) {
			html += '<td class="td-clk today" data-no="'+nno+'">'+
				date.getDate()+'</td>';
		}
		else {
			html += '<td class="td-clk" data-no="'+nno+'">'+
				date.getDate()+'</td>';
		}
		if (date.getDay() === 0) {
			html += '</tr><tr>';
		}
		date.setDate(date.getDate()+1);
	}
	html += '</tr>';
	document.querySelector('.calendar tbody').innerHTML = html;
	document.querySelector('.calendar span').innerHTML = mois[month]+' '+year;
	var tds = document.querySelectorAll('.calendar .td-clk');
	for (i=0; i<tds.length; i++) {
		tds[i].onclick = gotoday;
	}
}
var date = new Date();
var month = date.getMonth();
var year = date.getFullYear();
draw_calendar(month, year);
document.querySelector('.a-prev').onclick = function(e) {
	e.stopPropagation();
	month--;
	if (month < 0) { month = 11; year--; }
	draw_calendar(month, year);
	return false;
};
document.querySelector('.a-next').onclick = function(e) {
	e.stopPropagation();
	month++;
	if (month > 11) { month = 0; year++; }
	draw_calendar(month, year);
	return false;
};
