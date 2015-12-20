// L2 Msg Editor
var L2SysMsgEditor = (function () {
	var file, main_player, 
		current_played, 
		bulk_selection = false, 
		selected_cnt = 0, 
		is_diff = false, 
		changed = false, 
		saved = false, 
		
		SEARCH_MAP = {
			POSITION: 0, 
			DURATION: 1, 
			DELAY_SPEED: 2, 
			HEAD: 3, 
			SOUND: 4, 
			MESSAGE: 5, 
			SUB_MSG: 6, 
			OPACITY: 7, 
			COLOR: 8
		};
	
	function init(_file) {
		file = _file;
		
		if (file.isDiff) {
			bulk_selection = true;
			is_diff = true;
		}
		
		if (!is_diff) {
			soundManager.setup({
				url: 'static/js/soundmanager/swf/', 
				onready: function() { },
				ontimeout: function() { }

			});
			soundManager.onready(function () {
				main_player = soundManager.createSound({
					id: 'main_player', 
					autoLoad: false, 
					onfinish: function () {
						setPlayIcon(false);
						current_played = null;
					}, 
					onstop: function () {
						setPlayIcon(false);
						current_played = null;
					}
				});
			});
			$('#transparent').on('sliderUpdate', function (e) {
				var val = Math.round(e.value);
				$('#color_block').css('opacity', val / 255);
				$('#message').css('opacity', val / 255);
				$('#transparent_value').val(val);
			}).slider({min: 0, max: 255});
			
			$('#bulk_transparent').on('sliderUpdate', function (e) {
				var val = Math.round(e.value);
				$('#bulk_color_block, #bulk_example_text').css('opacity', val / 255);
				$('#bulk_transparent_value').val(val);
			}).slider({min: 0, max: 255}).value(255);
			
			$('.js-slider_update').on('input', function () {
				var el = $(this), val = +el.val() || 0;
				$('#' + el.data('slider')).slider().value(val);
			});
			
			$('#message_timeout_slider').on('sliderUpdate', function (e) {
				$('#message_timeout').val(Math.round(e.value));
			}).slider({min: 0, max: 60});
			
			$('#color_selector').ColorPicker({
				flat: true, 
				onChange: function (hsb, hex, rgb) {
					setMsgColor(hex);
				}
			});
			$('#bulk_color_selector').ColorPicker({
				flat: true, 
				onChange: function (hsb, hex, rgb) {
					setBulkColor(hex);
				}
			});
			setTimeout(function () {
				setBulkColor('b09b79');
			}, 0);
			
			$('#color_block').bind('click', function() {
				var color_selector = $('#color_selector');
				color_selector.stop().animate({height: color_selector.height() != 0 ? 0 : 230}, 500);
			});
		}
		// Открытие модального окна
		$('body')
		.on('click', '.js-open_bulk', function (e) {
			e.preventDefault();
			$('#bulk_edit_dialog').show();
			$('#bulk_edit_dialog > div').css("marginTop", $('#bulk_edit_dialog').height() / 2 - $('#bulk_edit_dialog > div').height() / 2);
			
			// Кол-во выбранных
			$('#win_selected_cnt').text(selected_cnt);
			
			// Сброс чекбоксов
			$('#cb_delete_chat_msg, #cb_delete_scr_msg, #cb_delete_sound, #cb_change_color').prop("checked", false);
		})
		.on('click', '#toggle_bulk', function () {
			$('.l2sysmsgs').toggleClass('l2sysmsgs-select');
			$(this).toggleClass('clicked');
			$('#bulk_buttons').toggle();
			bulk_selection = !bulk_selection;
		})
		.on('click', '.js-selection', function (e) {
			e.preventDefault();
			var dir = $(this).data('dir') > 0, 
				no_filter = $(this).data('dir') < 0, 
				elements = $('.l2sysmsgs').find('tr, tbody'), 
				el, i = 0, total = elements.length;
			
			selected_cnt = 0
			var thread = function () {
				var chunk = [];
				for (var j = 0; el = elements[i], j < 1000 && i < total; ++j) {
					++i;
					var id = el.id.substr(1);
					if (!id)
						continue;
					if ((no_filter || el.style.display != 'none') && el.className.indexOf('disabled') < 0) {
						chunk.push(el);
						if (dir)
							++selected_cnt;
					} else {
						if (el.className.indexOf('checked') >= 0)
							++selected_cnt;
					}
				}
				$(chunk).toggleClass('checked', dir);
				if (i < total) {
					setTimeout(thread, 0);
				} else {
					$('#selected_cnt').text(selected_cnt);
				}
			};
			thread();
		})
		.on('click', 'tbody.blk, tr[id^=m]', function (e) {
			e.preventDefault();
			var el = $(this), 
				cols = el.find('td'), 
				id = el.attr("id").match(/^m(\d+)/)[1];
			
			changed = true;
			
			if (bulk_selection) {
				if (el.hasClass('disabled'))
					return;
				selected_cnt += (el.hasClass('checked') ? -1 : 1);
				el.toggleClass('checked');
				$('#selected_cnt').text(selected_cnt);
			} else {
				$('#color_selector').css("height", 0);
				$('#edit_dialog .save_error').hide();
				$('#message_id').val(id);
				
				$('#message').val($(cols[1]).text());
				$('#sub_message').val($(cols[2]).text());
				
				$('#edit_dialog').show();
				$('#edit_dialog > div').css("marginTop", $('#edit_dialog').height() / 2 - $('#edit_dialog > div').height() / 2);
				
				var meta = file.meta[id];
				
				// opacity
				$('#transparent').slider().value(meta[4]);
				
				// position
				$('.screen_position td[p="' + meta[0] + '"]').click();
				
				// message timeout
				$('#message_timeout_slider').slider().value(meta[1]);
				
				// speed
				$('#message_show_speed option').prop("selected", false);
				var opt = $('#message_show_speed option[value="' + meta[2] +'"]');
				if (opt.length > 0) {
					opt.prop("selected", true);
				} else {
					$('#message_show_speed').append('<option value="' + meta[2] + '">' + meta[2] + '</option>');
					$('#message_show_speed option[value="' + meta[2] +'"]').prop("selected", true);
				}
				
				// head
				$('#head_on_msg').prop("checked", meta[3] != 0);
				
				// music
				$('#msg_music').val(meta[5]);
				
				// color
				setMsgColor(parse_color(el.css("color")));
			}
		})
		
		// Позиция на экране
		.on('click', '.screen_position td', function (e) {
			$('.screen_position td').removeClass('active');
			$(this).toggleClass('active');
		})
		
		// Выбор цвета
		.on('click', '.js-open_color_picker', function (e) {
			e.preventDefault();
			var color_selector = $($(this).data('selector'));
			color_selector.stop().animate({height: color_selector.height() != 0 ? 0 : 230}, 500);
		})
		
		// Сброс цвета
		.on('click', '.js-reset_color', function (e) {
			e.preventDefault();
			var color_selector = $($(this).data('selector'));
			color_selector.ColorPickerSetColor('b09b79')
				// Адовый костыль
				.find('input:first').trigger('change');
		})
		
		// Поиск музыки
		.on('keyup', '#msg_music_search', function () {
			var list = $('#music_list');
			var search = $.trim(this.value).toLowerCase();
			if (!search.length) {
				list.children().show();
				return false;
			}
			list.children().each(function(k, v) {
				$(v).toggle(v.textContent.toLowerCase().indexOf(search) > -1);
			});
			list.animate({scrollTop: 0}, 500);
		})
		
		// Выбор музыки
		.on('click', '.music', function (e) {
			e.preventDefault();
			$('#msg_music').val($(this).data('sound'));
		})
		
		// Проигрывание музыки
		.on('click', '.sound_play', function (e) {
			e.preventDefault(); e.stopPropagation();
			
			var el = $(this), 
				allow_play = !current_played || current_played[0] != el[0];
			if (current_played) {
				setPlayIcon(false);
				current_played = null;
				main_player.stop();
			}
			
			if (allow_play) {
				current_played = el;
				setPlayIcon(true);
				main_player.load({
					url: 'files/' + el.data('sound')
				});
				main_player.play({volume: 100});
				current_played = el;
			}
		})
		
		// Окно мержа
		.on('click', '.js-merge', function (e) {
			e.preventDefault();
			
			// Кол-во выбранных
			$('#win_selected_cnt').text(selected_cnt);
			
			$('#merge_window').show();
			$('#merge_window > div').css("marginTop", $('#merge_window').height() / 2 - $('#merge_window > div').height() / 2);
		})
		
		// Применить пакетные операции
		.on('click', '#save_merge', function(e) {
			e.preventDefault();
			var error = $('#merge_window .save_error').hide(), 
				selected = $('.l2sysmsgs tr.checked, .l2sysmsgs tbody.checked');
			
			var ids = selected.map(function () {
				var el = $(this);
				if (!el.hasClass('disabled'))
					return el.attr("id").match(/^m(\d+)/)[1]
			}).toArray();
			
			var post = {
				ids: ids.join(",")
			};
			var flags = getFlags('cb_', ("copy_message copy_sub_msg copy_position copy_delay_speed " + 
				"copy_duration copy_sound copy_color copy_opacity copy_head").split(" "));
			$.extend(post, flags);
			
			var UPDATE_MAP = {
				copy_color: SEARCH_MAP.COLOR, 
				copy_opacity: SEARCH_MAP.OPACITY, 
				copy_message: SEARCH_MAP.MESSAGE, 
				copy_sub_msg: SEARCH_MAP.SUB_MSG, 
				copy_position: SEARCH_MAP.POSITION, 
				copy_sound: SEARCH_MAP.SOUND, 
				copy_delay_speed: SEARCH_MAP.DELAY_SPEED, 
				copy_duration: SEARCH_MAP.DURATION, 
				copy_head: SEARCH_MAP.HEAD
			};
			if (selected.length) {
				saved = true;
				$.post(
				"?action=update_file&type=merge&donor_id=" + file.donor.id + "&file_id=" + file.id, post, 
					function (data) {
						if (data.errors.length > 0) {
							error.html(data.errors.join('<br />')).show();
						} else {
							for (var i = 0; i < selected.length; ++i) {
								var el = $(selected[i]), 
									id = el.attr("id").match(/^m(\d+)/)[1], 
									message = {
										own: $(el.find('td')[2]), 
										donor: $(el.find('td')[1])
									}, 
									sub_msg = {
										own: $(el.find('td')[4]), 
										donor: $(el.find('td')[3])
									};
								
								if (flags.copy_message)
									message.own.text(message.donor.text());
								if (flags.copy_sub_msg)
									sub_msg.own.text(sub_msg.donor.text());
								
								if (flags.copy_color) {
									message.own.css("color", message.donor.css("color"));
									sub_msg.own.css("color", sub_msg.donor.css("color"));
								}
								
								if (flags.copy_opacity) {
									message.own.css("opacity", message.donor.css("opacity"));
									sub_msg.own.css("opacity", sub_msg.donor.css("opacity"));
								}
								
								var all_okey = true;
								if (file.search[id].length > 0) {
									for (var k in flags) {
										if (UPDATE_MAP[k] !== undefined) {
											if (flags[k])
												file.search[id][UPDATE_MAP[k]] = 0;
											if (file.search[id][UPDATE_MAP[k]])
												all_okey = false;
											
											console.warn(k, UPDATE_MAP[k], file.search[id][UPDATE_MAP[k]]);
										} else {
											console.error(k);
										}
									}
								}
								if (all_okey)
									el.remove();
							}
							$('#merge_window').hide();
							
							setTimeout(function () {
								doSearch();
							}, 0);
						}
					}, 
					"json"
				).fail(function (e) {
					error.html("Connection error. (code: " + e.status + ")").show();
				});
			} else {
				$('#merge_window').hide();
			}
		})
		
		// Применить пакетные операции
		.on('click', '#save_bulk', function(e) {
			e.preventDefault();
			var error = $('#bulk_edit_dialog .save_error').hide(), 
				selected = $('.l2sysmsgs tr.checked, .l2sysmsgs tbody.checked');
			
			var ids = selected.map(function () {
				var el = $(this);
				if (!el.hasClass('disabled'))
					return el.attr("id").match(/^m(\d+)/)[1]
			}).toArray();
			
			var post = {
				ids: ids.join(","), 
				opacity: $('#bulk_transparent_value').val(), 
				color: $('#bulk_color_block').text().substr(1)
			};
			$.extend(post, getFlags('cb_', "delete_chat_msg delete_scr_msg delete_sound change_color".split(" ")));
			
			if (selected.length) {
				saved = true;
				$.post(
				"?action=update_file&type=bulk&file_id=" + file.id, post, 
					function (data) {
						if (data.errors.length > 0) {
							error.html(data.errors.join('<br />')).show();
						} else {
							for (var i = 0; i < selected.length; ++i) {
								var el = $(selected[i]), 
									id = el.attr("id").match(/^m(\d+)/)[1];
								
								if (post.change_color) {
									el.css({
										"opacity": post.opacity / 255, 
										"color": '#' + post.color
									});
								}
								
								if (post.delete_chat_msg)
									$(el.find('td')[1]).text('');
								
								if (post.delete_scr_msg)
									file.meta[id][0] = 0;
								
								if (post.delete_sound)
									file.meta[id][5] = '';
							}
							$('#bulk_edit_dialog').hide();
						}
					}, 
					"json"
				).fail(function (e) {
					error.html("Connection error. (code: " + e.status + ")").show();
				});
			} else {
				$('#bulk_edit_dialog').hide();
			}
		})
		
		// Сохранить строку
		.on('click', '#save_lang', function(e) {
			e.preventDefault();
			var error = $('#edit_dialog .save_error');
			
			var post = {
				id: $('#message_id').val(), 
				message: $('#message').val(), 
				sub_message: $('#sub_message').val(), 
				color: $('#color_block').text().substr(1), 
				opacity: $('#transparent_value').val(), 
				position: $('.screen_position .active').attr("p"), 
				message_timeout: $('#message_timeout').val(), 
				message_show_speed: $('#message_show_speed').val(), 
				music: $.trim($('#msg_music').val()), 
				head_on_message: $('#head_on_msg').prop("checked") ? 1 : 0
			};
			
			saved = true;
			$.post(
				"?action=update_file&file_id=" + file.id, post, 
				function (data) {
					var row = $('#m' + post.id);
					row.css("color", $('#color_block').text());
					row.css("opacity", post.opacity / 255);
					$(row.find('td')[1]).text(post.message);
					$(row.find('td')[2]).text(post.sub_message);
					
					file.meta[post.id][0] = +post.position;
					file.meta[post.id][1] = +post.message_timeout;
					file.meta[post.id][2] = +post.message_show_speed;
					file.meta[post.id][3] = +post.head_on_message;
					file.meta[post.id][4] = +post.opacity;
					file.meta[post.id][5] = post.music;
					
					if (data.errors.length > 0)
						error.html(data.errors.join('<br />')).show();
					$('#edit_dialog').hide();
				}, 
				"json"
			).fail(function (e) {
				error.html("Connection error. (code: " + e.status + ")").show();
			});
		})
		
		// Поиск ID
		.on('click', '#do_search_id', function (e) {
			e.preventDefault();
			var row = $('#m' + $.trim($('#search_id').val()));
			if (row.length) {
				$('html, body').animate({
					scrollTop: row.show().offset().top
				}, 100);
			}
		})
		
		// Скачать
		.on('click', '#download', function (e) {
			e.preventDefault();
			document.location.href = this.getAttribute('href');
		})
		
		// Поиск
		.on('click', '#do_search_text, .msg_filter, .msg_diff', function(e) {
			doSearch();
		})
		
		// Закрытие окна
		.on('click', '.modal_window_close', function (e) {
			e.preventDefault();
			$('#' + $(this).attr('w')).hide();
		});
	}
	
	function doSearch() {
		val = $.trim($('#search').val()).toLowerCase();
		var elements = $('.l2sysmsgs').find('tr, tbody'), 
			el, i = 0, total = elements.length, 
			finded = 0;
		
		var filter = getFlags(!is_diff ? "filter_" : "cb_diff_", 
			!is_diff ? 
				"screen sound colors opacity".split(" ") : 
				"colors opacity chat_msg scr_msg position sound speed duration head not_found".split(" ")
		);
		var DIFF_SEARCH = {
			colors: SEARCH_MAP.COLOR, 
			opacity: SEARCH_MAP.OPACITY, 
			chat_msg: SEARCH_MAP.MESSAGE, 
			scr_msg: SEARCH_MAP.SUB_MSG, 
			position: SEARCH_MAP.POSITION, 
			sound: SEARCH_MAP.SOUND, 
			speed: SEARCH_MAP.DELAY_SPEED, 
			duration: SEARCH_MAP.DURATION, 
			head: SEARCH_MAP.HEAD, 
			not_found: false
		};
		
		var thread = function () {
			for (var j = 0; el = elements[i], j < 1000 && i < total; ++j) {
				++i;
				
				var id = el.id.substr(1), 
					meta = file.meta[id], 
					match = false;
				
				if (!id)
					continue;
				
				if (is_diff) {
					match = (!val.length || el.textContent.toLowerCase().indexOf(val) > -1);
					if (match) {
						var cnt = 0;
						for (var k in DIFF_SEARCH) {
							var v = DIFF_SEARCH[k];
							if (filter[k]) {
								match = false;
								if (k == "not_found") {
									if (!file.search[id].length) {
										match = true;
										break;
									}
								} else if ((!file.search[id].length || file.search[id][v])) {
									match = true;
									break;
								}
							}
						}
					}
				} else {
					match = 
						(!filter.screen || meta[0] != 0)
						&& (!filter.opacity || meta[4] < 0xFF)
						&& (!filter.sound || meta[5].length)
						&& (!filter.colors || parse_color(el.style.color) != "b09b79")
						&& (!val.length || el.textContent.toLowerCase().indexOf(val) > -1);
				}
				el.style.display = match ? '' : 'none';
				
				if (match)
					++finded;
			}
			if (i < total) {
				setTimeout(thread, 0);
			} else {
				$('#search_total').text(finded);
			}
		};
		thread();
	}
	
	function getFlags(prefix, flags) {
		var out = {};
		for (var i = 0; i < flags.length; ++i)
			out[flags[i].replace(prefix, '')] = $('#' + prefix + flags[i]).prop("checked") ? 1 : 0;
		return out;
	}
	
	function setPlayIcon(flag) {
		if (current_played) {
			var src = current_played.prop("src");
			current_played.prop("src", !flag ? src.replace(/stop/, 'play') : src.replace(/play/, 'stop'));
		}
	}
	
	function setMsgColor(hex) {
		$('#color_block').css('color', '#' + hex).html('#' + hex.toUpperCase());
		$('#message, #sub_message').css('color', '#' + hex);
		$('#color_selector').ColorPickerSetColor(hex);
	}
	
	function setBulkColor(hex) {
		$('#bulk_color_block').css('color', '#' + hex).html('#' + hex.toUpperCase());
		$('#bulk_example_text').css('color', '#' + hex);
		$('#bulk_color_selector').ColorPickerSetColor(hex);
	}
	
	return {
		init: init
	};
})();
