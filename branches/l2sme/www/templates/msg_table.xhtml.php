
<div id="up_to_page">Наверх</div>

<div id="menu">
	<a href="?"><button class="btn">Новый</button></a>
	<a href="?action=download&amp;file_id=<?= $file_id ?>&amp;file_name=<?= urlencode($file_name) ?>"><button class="btn">Скачать <?= htmlspecialchars($file_name) ?></button></a>
	
	&nbsp;&nbsp;&nbsp;
	ID: <input id="search_id" value="" type="text" size="6" /><input value="&gt;" type="submit" id="do_search_id" />
	&nbsp;&nbsp;&nbsp;
	<input id="search" value="" type="text" size="50" /><input value="Поиск" type="submit" id="do_search_text" />
</div>

<table class="l2sysmsgs">
	<tr>
		<th>ID</th>
		<th>Сообщение</th>
		<th>Доп. сообщение</th>
	</tr>
<?php foreach ($messages as &$msg): ?>
<tr
	N="<?= $msg[L2SystemMsg::ID] ?>"
	S="<?= $msg[L2SystemMsg::POSITION] ?>,<?= $msg[L2SystemMsg::DURATION] ?>,<?= $msg[L2SystemMsg::DELAY_SPEED] ?>,<?= $msg[L2SystemMsg::HEAD] ?>,<?= $msg[L2SystemMsg::A] ?>,<?= htmlspecialchars(un_null($msg[L2SystemMsg::SOUND])) ?>"
	style="opacity:<?= round($msg[L2SystemMsg::A] / 255, 4) ?>;color:#<?= sprintf("%02X%02X%02X", $msg[L2SystemMsg::R], $msg[L2SystemMsg::G], $msg[L2SystemMsg::B]) ?>"
>
	<td><?= $msg[L2SystemMsg::ID] ?></td>
	<td m><?= htmlspecialchars(un_null($msg[L2SystemMsg::MESSAGE])) ?></td>
	<td s><?= htmlspecialchars(un_null($msg[L2SystemMsg::SUB_MSG])) ?></td>
</tr>
<?php endforeach; ?>
</table>

<div id="edit_dialog" class="modal_window">
	<div>
		<input type="hidden" name="id" id="message_id" value="" />
		
		<small style="color: red; display: none" id="sysmsg_edit_error"></small>
		
		Сообщение:<br />
		<textarea class="max_width" name="msg" id="message"></textarea><br />
		Доп. сообщение:<br />
		<textarea class="max_width" name="submsg" id="sub_message"></textarea><br />
		<div class="hr"></div>
		Положение на экране:<br />
		<table>
			<tr>
				<td width="50%">
					<table class="screen_position">
						<tr>
							<td p="1">1</td>
							<td p="2">2</td>
							<td p="3">3</td>
						</tr>
						<tr>
							<td p="4">4</td>
							<td p="5">5</td>
							<td p="6">6</td>
						</tr>
						<tr>
							<td p="0">X</td>
							<td p="7">7</td>
							<td p="8">8</td>
						</tr>
					</table>
				</td>
				<td width="50%">
					<div>
						Цвет текста: <span id="color_block"></span><br />
						<div id="color_selector"></div>
						Непрозрачность [0-255]: <input type="text" size="3" id="transparent_value" value="" /><br />
						<div id="transparent"></div>
					</div>
				</td>
			</tr>
		</table>
		<div class="hr"></div>
		
		Длительность показа сообщения: <input type="text" size="3" id="message_timeout" value="" /> секунд
		<div style="float: right">
			Показать:
			<select name="message_show_speed" id="message_show_speed">
				<option value="0">Мгновенно</option>
				<option value="1">Постепенно</option>
				<option value="11">Плавно</option>
			</select>
		</div>
		<br />
		<div id="message_timeout_slider"></div>
		<div class="hr"></div>
		
		<label title="Очень нихуёвая приебенция"><input type="checkbox" value="1" name="head" id="head_on_msg" /> Декоративная хренька над сообщением</label><br />
		
		Звук при сообщении:<br />
		<input type="text" value="" name="music" id="msg_music" style="width: 97%" />
		Или выберите из списка:
		
		<div style="height: 150px; overflow-y: scroll">
		<?php foreach ($sounds as $sound): ?>
			<div class="music" sound-name="<?= htmlspecialchars($sound) ?>">
				<img src="static/images/play.png" alt="" play="<?= htmlspecialchars(get_music_path($sound)) ?>" />
				<?= htmlspecialchars(normalize_music_name($sound)) ?>
			</div>
		<?php endforeach; ?>
		</div>
		<div class="hr"></div>
		<input type="submit" id="save_lang" value="Сохранить" />
		<input type="submit" value="Отмена" class="modal_window_close" w="edit_dialog" />
	</div>
</div>

<script>
var file_id = <?= json_encode($file_id) ?>;
var file_name = <?= json_encode($file_name) ?>;
</script>
<script type="text/javascript">
$('#save_lang').click(function(e) {
	e.preventDefault();
	
	var post = {};
	
	post.id = $('#message_id').val();
	post.message = $('#message').val();
	post.sub_message = $('#sub_message').val();
	post.color = $('#color_block').text().substr(1);
	post.opacity = $('#transparent_value').val();
	post.position = $('.screen_position td.active').attr("p");
	post.opacity = $('#transparent')[0].sliderGetValue();
	post.message_timeout = $('#message_timeout_slider')[0].sliderGetValue();
	post.message_show_speed = $('#message_show_speed').val();
	post.music = $('#msg_music').val();
	post.head_on_message = $('#head_on_msg').prop("checked") ? 1 : 0;
	
	var tr = $('tr[N=' + post.id + ']');
	tr.attr("S", post.position + "," + post.message_timeout + "," + post.message_show_speed + "," + post.head_on_message + "," + post.opacity + "," + post.music);
	tr.css("color", '#' + post.color);
	tr.css("opacity", post.opacity / 255, 4);
	
	tr.find('td[m]').text(post.message);
	tr.find('td[s]').text(post.sub_message);
	
	$('#sysmsg_edit_error').hide();
	$.post(
		"?action=update_file&file_id=" + file_id, post, 
		function (data) {
			if (data.errors.length > 0)
				$('#sysmsg_edit_error').html(data.errors.join('<br />')).show();
			$('#edit_dialog').toggle();
		}, 
		"json"
	).fail(function (e) {
		$('#sysmsg_edit_error').html("Ошибка подключения к серверу. ").show();
	});
});

$(".l2sysmsgs tr[S]").click(function(e) {
	e.preventDefault();
	
	$('#sysmsg_edit_error').hide();
	$('#message_id').val(parseInt($(this).find("td").text()));
	
	$('#message').val($(this).find('td[m]').text());
	$('#sub_message').val($(this).find('td[s]').text());
	
	$('#edit_dialog').show();
	$('#edit_dialog > div').css("marginTop", $('#edit_dialog').height() / 2 - $('#edit_dialog > div').height() / 2);
	
	// color
	var color = $(this).css("color");
	var r = 0, g = 0, b = 0, hex_color = '000';
	var m;
	if ((m = color.match(/\((\d+), (\d+), (\d+)\)/i))) {
		r = m[1]; g = m[2]; b = m[3];
		hex_color = to_hex((r << 16) | (g << 8) | b, 6);
	}
	set_color(hex_color);
	
	var parts = this.getAttribute('S').split(',', 6);
	
	// position
	$('.screen_position td[p="' + parts[0] + '"]').click();
	
	// message timeout
	set_message_timeout(parts[1]);
	
	// speed
	$('#message_show_speed option').prop("selected", false);
	var opt = $('#message_show_speed option[value="' + parts[2] +'"]');
	if (opt.length > 0) {
		opt.prop("selected", true);
	} else {
		$('#message_show_speed').append('<option value="' + parts[2] + '">' + parts[3] + '</option>');
		$('#message_show_speed option[value="' + parts[2] +'"]').prop("selected", true);
	}
	
	// head
	$('#head_on_msg').prop("checked", parts[3] != 0);
	
	// opacity
	set_opacity(parts[4]);
	
	// music
	$('#msg_music').val(parts[5]);
	
	$('#edit_dialog').show();
});

$('.modal_window_close').click(function() {
	$('#' + this.getAttribute('w')).toggle();
});

var widt = false;
$('#color_block').bind('click', function() {
	if (!widt)
		$('#color_selector').ColorPickerSetColor($(this).text());
	$('#color_selector').stop().animate({height: widt ? 0 : 230}, 500);
	widt = !widt;
});
$('#transparent_value').keyup(function () {
	if (this.value > 255)
		this.value = 255;
	if (this.value < 0)
		this.value = 0;
	
	update_opacity(this.value);
});
Slider.init($('#transparent'), {
	max: 255, 
	min: 0, 
	onmove: function (value) {
		update_opacity(value);
	}
});
Slider.init($('#message_timeout_slider'), {
	max: 60, 
	min: 0, 
	onmove: function (value) {
		update_message_timeout(value);
	}
});

$('.music').click(function () {
	$('#msg_music').val(this.getAttribute('sound-name'));
});
var players = [];
$('img[play]').click(function () {
	var id = this.getAttribute('play').replace(/\//g, ".");
	var self = this;
	
	if (self.is_played) {
		soundManager.stop(id);
		return;
	}
	
	self.is_played = true;
	self.src = self.src.replace(/play/, 'stop');
	if (!document.getElementById(id)) {
		var p = soundManager.createSound({
			id: id, 
			url: 'files/' + this.getAttribute('play'), 
			autoLoad: false, 
			onfinish: function () {
				self.src = self.src.replace(/stop/, 'play');
				self.is_played = false;
			}, 
			onstop: function () {
				self.src = self.src.replace(/stop/, 'play');
				self.is_played = false;
			}
		});
		players.push(p);
	}
	for (var i = 0; i < players.length; ++i)
		players[i].stop();
	soundManager.play(id, {volume: 100});
});
$('#color_selector').ColorPicker({
	flat: true, 
	onChange: function (hsb, hex, rgb) {
		set_color(hex);
	}
});
$('.screen_position td').click(function (e) {
	$('.screen_position td').removeClass('active');
	$(this).toggleClass('active');
});
$('#up_to_page').click(function (e) {
	$('body').animate({scrollTop: 0}, 300);
});
$('#do_search_id').click(function (e) {
	$('body').animate({
		scrollTop: $('tr[N="' + $('#search_id').val() + '"]').show().offset().top
	}, 500);
});
$('#do_search_text').click(function(e) {
	do_search($('#search').val());
});
$('#download').click(function (e) {
	document.location.href = this.getAttribute('href');
	return false;
})

var is_fixed = false;
var nav = $('#up_to_page');
$(window).scroll(function () {
	if ($(this).scrollTop() > 0) {
		if (!is_fixed) {
			nav.show();
			is_fixed = true;
		}
	} else {
		if (is_fixed) {
			nav.hide();
			is_fixed = false;
		}
	}
});

soundManager.setup({
	url: 'static/js/soundmanager/swf/', 
	onready: function() { },
	ontimeout: function() { }

});

function set_color(hex) {
	$('#color_block').css('color', '#' + hex).html('#' + hex.toUpperCase());
	$('#message, #sub_message').css('color', '#' + hex);
}
function set_opacity(val) {
	$('#transparent')[0].sliderSetValue(val);
	update_opacity(val);
}
function set_message_timeout(val) {
	$('#message_timeout_slider')[0].sliderSetValue(val);
	update_message_timeout(val);
}
function update_opacity(val) {
	$('#color_block').css('opacity', val / 255);
	$('#message, #sub_message').css('opacity', val / 255);
	$('#transparent_value').val(val);
}
function update_message_timeout(val) {
	$('#message_timeout').val(val);
}
function to_hex(i, n) {
	var s = '';
	for (var j = 0; j < n; ++j)
		s += '0';
	s += i.toString(16);
	return s.substr(-n);
}

function do_search(value) {
	if (value.length == 0) {
		$('tr[N]').show();
		return;
	}
	
	var search = value.toLowerCase();
	$('tr[N]').each(function(k, v) {
		var $e = $(v);
		if ($e.find('td[m]').text().toLowerCase().indexOf(search) > -1) {
			$e.show();
		} else if ($e.find('td[s]').text().toLowerCase().indexOf(search) > -1) {
			$e.show();
		} else
			$e.hide();
	});
};
</script>
