var INVALID_EMAIL = /^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/;
var INVALID_PASSWORD = new RegExp('^[a-z0-9]{1,}$');
function send(target, query, callback, wait, c) {
	var req = new GAjax();
	req.inintLoading(wait || 'wait', false, c);
	req.send(target, query, function (xhr) {
		callback.call(this, xhr);
	});
}
function defaultSubmit(ds) {
	var _alert = '',
		_input = false,
		_url = false,
		_location = false,
		remove = /remove([0-9]{0,})/;
	for (var prop in ds) {
		var val = ds[prop];
		if (prop == 'error') {
			_alert = eval(val);
		} else if (prop == 'alert') {
			_alert = val;
		} else if (prop == 'location') {
			if (val == 'close') {
				if (modal) {
					modal.hide();
				}
			} else {
				_location = val;
			}
		} else if (prop == 'url') {
			_url = val;
			_location = val;
		} else if (prop == 'tab') {
			inintWriteTab("accordient_menu", val);
		} else if (remove.test(prop)) {
			if ($E(val)) {
				$G(val).remove();
			}
		} else if (prop == 'input') {
			var el = $G(val);
			var t = el.title.strip_tags();
			if (t == '' && el.placeholder) {
				t = el.placeholder.strip_tags();
			}
			if (_input != el) {
				el.invalid(t);
			}
			if (t != '' && _alert == '') {
				_alert = t;
				_input = el;
			}
		} else if ($E(prop)) {
			$G(prop).setValue(decodeURIComponent(val).replace('%', '&#37;'));
		} else if ($E(prop.replace('ret_', ''))) {
			var el = $G(prop.replace('ret_', ''));
			if (val == '') {
				el.valid();
			} else {
				if (_input != el) {
					el.invalid(val);
				}
				if (_alert == '') {
					_alert = val;
					_input = el;
				}
			}
		}
	}
	if (_alert != '') {
		alert(_alert);
	}
	if (_input) {
		_input.focus();
		var tag = _input.tagName.toLowerCase();
		if (tag != 'select') {
			_input.highlight();
		}
		if (tag == 'input' && _input.get('type').toLowerCase() == 'text') {
			_input.select();
		}
	}
	if (_location) {
		if (_location == 'reload') {
			reload();
		} else if (_location == _url) {
			window.location = decodeURIComponent(_location);
		} else {
			window.location = _location.replace(/&amp;/g, '&');
		}
	}
}
function doFormSubmit(xhr) {
	var datas = xhr.responseText.toJSON();
	if (datas) {
		defaultSubmit(datas);
	} else if (xhr.responseText != '') {
		alert(xhr.responseText);
	}
}
function checkUsername() {
	var patt = /[a-zA-Z0-9]+/;
	var value = this.input.value;
	var id = '&id=' + floatval($E('id').value);
	if (value == '') {
		this.invalid(this.title);
	} else if (patt.test(value)) {
		return 'action=username&value=' + encodeURIComponent(value) + id;
	} else {
		this.invalid(this.title);
	}
}
function checkEmail() {
	var value = this.input.value;
	var id = '&id=' + floatval($E('id').value);
	if (value == '') {
		this.invalid(this.title);
	} else if (INVALID_EMAIL.test(value)) {
		return 'action=email&value=' + encodeURIComponent(value) + id;
	} else {
		this.invalid(this.title);
	}
}
function checkPhone() {
	var value = this.input.value;
	var id = '&id=' + floatval($E('id').value);
	if (value != '') {
		return 'action=phone&value=' + encodeURIComponent(value) + id;
	}
}
function checkDisplayname() {
	var value = this.input.value;
	var id = '&id=' + floatval($E('id').value);
	if (value == '') {
		this.invalid(this.title);
	} else if (value.length < 2) {
		this.invalid(this.title);
	} else {
		return 'action=displayname&value=' + encodeURIComponent(value) + '&id=' + id;
	}
}
function checkPassword() {
	var id = '&id=' + floatval($E('id').value);
	var Password = $E('password');
	var Repassword = $E('repassword');
	if (Password.value == '' && Repassword.value == '') {
		if (id == 0) {
			Password.Validator.invalid(Password.Validator.title);
		} else {
			Password.Validator.reset();
		}
		Repassword.Validator.reset();
	} else if (Password.value == Repassword.value) {
		Repassword.Validator.valid();
		Password.Validator.valid();
	} else {
		this.input.Validator.invalid(Password.Validator.title);
	}
}
function checkAntispam() {
	var value = this.input.value;
	if (value.length > 3) {
		return 'action=antispam&value=' + value + '&antispam=' + $E('antispam').value;
	} else {
		this.invalid(this.placeholder);
	}
}
function checkIdcard() {
	var value = this.input.value;
	var id = '&id=' + floatval($E('id').value);
	var i, sum;
	if (value.length != 13) {
		this.invalid(this.title);
	} else {
		for (i = 0, sum = 0; i < 12; i++) {
			sum += parseFloat(value.charAt(i)) * (13 - i);
		}
		if ((11 - sum % 11) % 10 != parseFloat(value.charAt(12))) {
			this.invalid(this.title);
		} else {
			return 'action=idcard&value=' + encodeURIComponent(value) + '&id=' + id;
		}
	}
}
function checkAlias() {
	var value = this.input.value;
	if (value == '') {
		this.invalid(this.title);
	} else if (value.length < 3) {
		this.invalid(ALIAS_SHORT);
	} else {
		return 'action=alias&val=' + encodeURIComponent(value) + '&id=' + $E('id').value;
	}
}
function reload() {
	window.location = replaceURL('timestamp', new String(new Date().getTime()), window.location.toString());
}
function getWebUri() {
	var port = floatval(window.location.port);
	var protocol = window.location.protocol;
	if ((protocol == 'http:' && port == 80) || (protocol == 'https:' && port == 443)) {
		port = '';
	} else {
		port = port > 0 ? ':' + port : '';
	}
	return protocol + '//' + window.location.hostname + port + '/';
}
function replaceURL(keys, values, url) {
	var patt = /^(.*)=(.*)$/;
	var ks = keys.toLowerCase().split(',');
	var vs = values.split(',');
	var urls = new Object();
	var u = url || window.location.href;
	var us2 = u.split('#');
	u = us2.length == 2 ? us2[0] : u;
	var us1 = u.split('?');
	u = us1.length == 2 ? us1[0] : u;
	if (us1.length == 2) {
		forEach(us1[1].split('&'), function () {
			hs = patt.exec(this);
			if (!hs || ks.indexOf(hs[1].toLowerCase()) == -1) {
				urls[this] = this;
			}
		});
	}
	if (us2.length == 2) {
		forEach(us2[1].split('&'), function () {
			hs = patt.exec(this);
			if (!hs || ks.indexOf(hs[1].toLowerCase()) == -1) {
				urls[this] = this;
			}
		});
	}
	var us = new Array();
	for (var p in urls) {
		us.push(urls[p]);
	}
	forEach(ks, function (item, index) {
		if (vs[index] && vs[index] != '') {
			us.push(item + '=' + vs[index]);
		}
	});
	u += '?' + us.join('&');
	return u;
}
function setSelect(id, value) {
	forEach($E(id).getElementsByTagName('input'), function () {
		if (this.type.toLowerCase() == 'checkbox') {
			this.checked = value;
		}
	});
}
var doCustomConfirm = function (value) {
	return confirm(value);
};