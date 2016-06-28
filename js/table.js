/*
 * GTable
 * Javascript data table for Kotchasan Framework
 *
 * @filesource js/table.js
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */
(function () {
	'use strict';
	window.GTable = GClass.create();
	GTable.prototype = {
		initialize: function (id, o) {
			this.options = {
				action: null,
				actionCallback: null,
				actionConfirm: null,
				pmButton: false,
				dragColumn: -1
			};
			for (var prop in o) {
				this.options[prop] = o[prop];
			}
			this.table = $E(id);
			this.search = o['search'] || '';
			this.sort = o['sort'] || null;
			this.page = o['page'] || 1;
			var hs,
				sort_patt = /sort_(none|asc|desc)\s(col_([\w]+))(|\s.*)$/,
				action_patt = /button[\s][a-z]+[\s]action/,
				temp = this;
			var _doSort = function (e) {
				if (hs = sort_patt.exec(this.className)) {
					var sort = new Array();
					if (GEvent.isCtrlKey(e)) {
						var patt = new RegExp(hs[3] + '[\\s](asc|desc|none)');
						forEach(temp.sort.split(','), function () {
							if (!patt.test(this)) {
								sort.push(this);
							}
						});
					}
					if (hs[1] == 'none') {
						sort.push(hs[3] + '%20asc');
					} else if (hs[1] == 'asc') {
						sort.push(hs[3] + '%20desc');
					} else {
						sort.push(hs[3] + '%20none');
					}
					temp.sort = sort.join(',');
					window.location = temp.redirect();
				}
			};
			if (this.table) {
				forEach($G(this.table).elems('th'), function () {
					if (sort_patt.test(this.className)) {
						callClick(this, _doSort);
					}
				});
				this.init(this.table);
				var doAction = function () {
					var action = '',
						cs = new Array(),
						chk = /check_[0-9]+/;
					forEach(temp.table.elems('a'), function () {
						if (chk.test(this.id) && $G(this).hasClass('icon-check')) {
							cs.push(this.id.replace('check_', ''));
						}
					});
					if (cs.length == 0) {
						alert(trans('Please select at least one item'));
					} else {
						cs = cs.join(',');
						var f = this.get('for');
						var fn = window[temp.options.actionConfirm];
						if (Object.isFunction(fn)) {
							action = fn($G(f).getText(), $E(f).value, cs);
						} else {
							if (confirm(trans('You want to XXX the selected items ?').replace(/XXX/, $G(f).getText()))) {
								action = 'module=' + f + '&action=' + $E(f).value + '&id=' + cs;
							}
						}
						if (action != '') {
							temp.callAction(this, action);
						}
					}
				};
				forEach(this.table.elems('label'), function () {
					if (action_patt.test(this.className)) {
						callClick(this, doAction);
					}
				});
				var doButton = function () {
					var action = '',
						patt = /^([a-z_\-]+)_([0-9]+)(_([0-9]+))?$/;
					if (temp.options.actionConfirm) {
						var fn = window[temp.options.actionConfirm];
						if (Object.isFunction(fn)) {
							hs = patt.exec(this.id);
							action = fn('action', hs[1], hs[2], hs[4]);
						}
					} else {
						hs = patt.exec(this.id);
						if (hs[1] == 'delete') {
							if (confirm(trans('You want to delete ?'))) {
								action = 'action=delete&id=' + hs[2];
							}
						} else {
							action = 'action=' + hs[1] + '&id=' + hs[2];
						}
					}
					if (action != '') {
						temp.callAction(this, action);
					}
				};
				if (this.options.action) {
					var move = /(check|move)_([0-9]+)/;
					forEach(this.table.elems('a'), function () {
						var id = this.id;
						if (id && !move.test(id)) {
							callClick(this, doButton);
						}
					});
				}
				if (this.options.dragColumn > -1) {
					new GSortTable(this.table, {
						'endDrag': function () {
							var trs = new Array();
							forEach(temp.table.elems('tr'), function () {
								if (this.id) {
									trs.push(this.id.replace(id + '_', ''));
								}
							});
							if (trs.length > 1) {
								temp.callAction(this, 'action=move&data=' + trs.join(','));
							}
						}
					});
				}
			}
		},
		callAction: function (el, action) {
			var hs = this.options.action.split('?');
			if (hs[1]) {
				action = hs[1] + '&' + action;
			}
			action += '&src=' + this.table.id;
			var temp = this;
			if (el.hasClass('button')) {
				el.addClass('wait');
			} else {
				var _class = el.className;
				el.className = 'icon-loading';
			}
			send(hs[0], action, function (xhr) {
				if (el.hasClass('button')) {
					el.removeClass('wait');
				} else {
					el.className = _class;
				}
				if (temp.options.actionCallback) {
					var fn = window[temp.options.actionCallback];
					if (Object.isFunction(fn)) {
						fn(xhr);
					}
				} else if (xhr.responseText != '') {
					alert(xhr.responseText);
				} else {
					window.location.reload();
				}
			});
		},
		initTR: function (el) {
			var row = 0,
				temp = this;
			forEach($G(el).elems('tr'), function () {
				if (this.parentNode.tagName.toLowerCase() == 'tbody') {
					this.id = temp.table.id + '_' + row;
					forEach($G(this).elems('input'), function () {
						this.id = this.name.replace(/([\[\]_]+)/g, '_') + row;
					});
					row++;
				}
			});
		},
		init: function (el) {
			var hs,
				a_patt = /(delete|icon)[_\-](plus|minus|[0-9]+)/,
				check_patt = /check_([0-9]+)/,
				temp = this;
			var aClick = function () {
				var c = this.className;
				if (c == 'icon-plus') {
					var tr = $G(this.parentNode.parentNode.parentNode);
					var tbody = tr.parentNode;
					var ntr = tr.copy(false);
					tr.after(ntr);
					temp.init(ntr);
					temp.initTR(tbody);
					ntr.highlight();
					ntr = ntr.getElementsByTagName('input')[0];
					if (ntr) {
						ntr.focus();
						ntr.select();
					}
				} else if (c == 'icon-minus') {
					var tr = $G(this.parentNode.parentNode.parentNode);
					var tbody = $G(tr.parentNode);
					if (tbody.elems('tr').length > 1 && confirm(trans('You want to delete ?'))) {
						tr.remove();
						temp.initTR(tbody);
					}
				} else if (hs = a_patt.exec(c)) {
					var action = '';
					if (hs[1] == 'delete' && confirm(trans('You want to delete ?'))) {
						action = 'action=delete&id=' + hs[2];
					}
					if (action != '' && temp.options.action) {
						send(temp.options.action, action, function (xhr) {
							var ds = xhr.responseText.toJSON();
							if (ds) {
								if (ds.alert && ds.alert != '') {
									alert(ds.alert);
								} else if (ds.action) {
									if (ds.action == 'delete') {
										var tr = $G(temp.table.id + '_' + ds.id);
										var tbody = tr.parentNode;
										tr.remove();
										temp.initTR(tbody);
									}
								}
							} else if (xhr.responseText != '') {
								alert(xhr.responseText);
							}
						}, this);
					}
				}
			};
			var saClick = function () {
				this.focus();
				var chk = this.hasClass('icon-check');
				forEach(el.elems('a'), function () {
					if (check_patt.test(this.id)) {
						this.className = chk ? 'icon-uncheck' : 'icon-check';
						this.title = chk ? trans('check') : trans('uncheck');
					} else if (this.hasClass('checkall')) {
						this.className = chk ? 'checkall icon-uncheck' : 'checkall icon-check';
						this.title = chk ? trans('select all') : trans('select none');
					}
				});
				return false;
			};
			var sClick = function () {
				this.focus();
				var chk = $G(this).hasClass('icon-check');
				this.className = chk ? 'icon-uncheck' : 'icon-check';
				this.title = chk ? trans('check') : trans('uncheck');
				forEach(el.elems('a'), function () {
					if (this.hasClass('checkall')) {
						this.className = 'checkall icon-uncheck';
						this.title = trans('select all');
					}
				});
				return false;
			};
			forEach($G(el).elems('a'), function () {
				if (a_patt.test(this.className)) {
					callClick(this, aClick);
				} else if ($G(this).hasClass('checkall')) {
					this.title = trans('select all');
					callClick(this, saClick);
				} else if (check_patt.test(this.id)) {
					this.title = trans('check');
					callClick(this, sClick);
				}
			});
			if (this.options.pmButton) {
				this.initTR(el);
			}
		},
		setSort: function (sort, patt) {
			var hs;
			forEach(this.table.elems('th'), function () {
				hs = patt.exec(this.className);
				if (hs) {
					if (sort == hs[2]) {
						this.className = this.className.replace('sort_' + hs[1], 'sort_' + (hs[1] == 'asc' ? 'desc' : 'asc'));
					} else {
						this.className = this.className.replace('sort_' + hs[1], 'sort_none');
					}
				}
			});
		},
		redirect: function () {
			var hs,
				patt = /^(.*)=(.*)$/,
				urls = new Object(),
				u = window.location.href,
				us2 = u.split('#'),
				us1 = us2[0].split('?');
			if (us1.length == 2) {
				forEach(us1[1].split('&'), function () {
					if (hs = patt.exec(this)) {
						urls[hs[1].toLowerCase()] = this;
					} else {
						urls[this] = this;
					}
				});
			}
			if (us2.length == 2) {
				forEach(us2[1].split('&'), function () {
					if (hs = patt.exec(this)) {
						urls[hs[1].toLowerCase()] = this;
					}
				});
			}
			var us = new Array();
			for (var p in urls) {
				if (p != 'sort' && p != 'page' && p != 'search') {
					us.push(urls[p]);
				}
			}
			us.push('page=' + this.page);
			if (this.sort) {
				us.push('sort=' + this.sort);
			}
			if (this.search) {
				us.push('search=' + encodeURIComponent(this.search));
			}
			u = us1[0];
			if (us.length > 0) {
				u += '?' + us.join('&');
			}
			return u;
		}
	};
}());