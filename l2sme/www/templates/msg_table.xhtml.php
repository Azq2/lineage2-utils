
<div id="up_to_page"><?= L('Наверх') ?></div>

<div id="menu">
	<a href="?"><button class="btn"><?= L('Новый') ?></button></a>
	&nbsp;&nbsp;&nbsp;
	<a href="?action=download&amp;file_id=<?= $file_id ?>&amp;file_name=<?= urlencode($file_name) ?>"><button class="btn btn-download">
		<b><?= L('Скачать') ?></b>
	</button></a>
	&nbsp;&nbsp;&nbsp;
	
	<?php if ($is_diff): ?>
	<a href="?action=edit_file&amp;file_id=<?= $file_id ?>&amp;file_name=<?= urlencode($file_name) ?>"><button class="btn"><?= L('Редактор') ?></button></a>
	<?php endif; ?>
	
	&nbsp;&nbsp;&nbsp;
	ID: <input id="search_id" value="" type="text" size="6" />
	<input value="&gt;" type="submit" id="do_search_id" />
	&nbsp;&nbsp;&nbsp;
	<input id="search" value="" type="text" size="50" />
	<input value="<?= L('Поиск') ?>" type="submit" id="do_search_text" />
	
	<div style="margin-top: 3px">
		<?php if (!$is_diff): ?>
			<b><?= L('Фильтры:') ?></b>
			<label><input type="checkbox" value="1" name="filter_screen" id="filter_screen" class="msg_filter" /> <?= L('Только на экране') ?></label>
			&nbsp;&nbsp;
			<label><input type="checkbox" value="1" name="filter_sound" id="filter_sound" class="msg_filter" /> <?= L('Только со звуком') ?></label>
			&nbsp;&nbsp;
			<label><input type="checkbox" value="1" name="filter_colors" id="filter_colors" class="msg_filter" /> <?= L('Только цветные') ?></label>
		<?php endif; ?>
		
		<?php if ($is_diff): ?>
		<div>
			<b><?= L('Сравнивать:') ?></b><br />
			<table>
				<tr>
					<td><label><input type="checkbox" value="1" name="xuj" id="cb_diff_colors" class="msg_diff" /> <?= L('Цвет') ?></label></td>
					<td><label><input type="checkbox" value="1" name="xuj" id="cb_diff_opacity" class="msg_diff" /> <?= L('Прозрачность') ?></label></td>
				</tr>
				<tr>
					<td><label><input type="checkbox" value="1" name="xuj" id="cb_diff_chat_msg" class="msg_diff" checked="checked" /> <?= L('Сообщение чата') ?></label></td>
					<td><label><input type="checkbox" value="1" name="xuj" id="cb_diff_scr_msg" class="msg_diff" checked="checked" /> <?= L('Сообщение экрана') ?></label></td>
				</tr>
				<tr>
					<td><label><input type="checkbox" value="1" name="xuj" id="cb_diff_position" class="msg_diff" /> <?= L('Позиция') ?></label></td>
					<td><label><input type="checkbox" value="1" name="xuj" id="cb_diff_sound" class="msg_diff" /> <?= L('Звук') ?></label></td>
				</tr>
				<tr>
					<td><label><input type="checkbox" value="1" name="xuj" id="cb_diff_speed" class="msg_diff" /> <?= L('Скорость показа') ?></label></td>
					<td><label><input type="checkbox" value="1" name="xuj" id="cb_diff_duration" class="msg_diff" /> <?= L('Длительность') ?></label></td>
				</tr>
				<tr>
					<td><label><input type="checkbox" value="1" name="xuj" id="cb_diff_head" class="msg_diff" /> <?= L('Арка') ?></label></td>
					<td><label><input type="checkbox" value="1" name="xuj" id="cb_diff_not_found" class="msg_diff" /> <?= L('Новые сообщения') ?></label></td>
				</tr>
			</table>
		</div>
		<?php endif; ?>
		<?php if (!$is_diff): ?>
		&nbsp;&nbsp;
		<button class="btn" id="toggle_bulk"><?= L('Выбрать несколько') ?></button>
		<?php endif; ?>
	</div>
	
	<?php if ($is_diff): ?>
		<div class="hr"></div>
		<button class="btn js-merge"><?= L('Объединить') ?></button> &#8592; <?= L('жми сюда, когда выберешь сообщения') ?> (<b><?= L('выбрано:') ?></b> <span id="selected_cnt">0</span>)<br />
		<div class="hr"></div>
	<?php endif; ?>
	
	<div<?php if (!$is_diff): ?> style="margin-top: 3px; display: none" id="bulk_buttons"<?php endif; ?>>
		<?php if (!$is_diff): ?>
		<button class="btn js-open_bulk"><?= L('Пакетные операции') ?></button> &#8592; <?= L('жми сюда, когда выберешь сообщения') ?> (<b><?= L('выбрано:') ?></b> <span id="selected_cnt">0</span>)<br /><br />
		<?php endif; ?>
		
		<button class="btn js-selection" data-dir="1"><?= L('Выбрать всё') ?></button>
		&nbsp;&nbsp;
		<button class="btn js-selection" data-dir="0"><?= L('Сбросить') ?></button>
		&nbsp;&nbsp;
		<button class="btn js-selection" data-dir="-1"><?= L('Сбросить всё') ?></button>
	</div>
	
</div>
<?php if (!$is_diff): ?>
<table class="l2sysmsgs">
	<tr>
		<th>ID</th>
		<th><?= L('Сообщение') ?> (<span id="search_total"><?= count($messages) ?></span>)</th>
		<th><?= L('Доп. сообщение') ?></th>
	</tr>
<?php foreach ($messages as &$msg): ?>
<tr id="m<?= $msg[L2File\SystemMsg::ID] ?>" style="opacity:<?= round($msg[L2File\SystemMsg::A] / 255, 4) ?>;color:#<?= sprintf("%02X%02X%02X", $msg[L2File\SystemMsg::R], $msg[L2File\SystemMsg::G], $msg[L2File\SystemMsg::B]) ?>">
	<td><?= $msg[L2File\SystemMsg::ID] ?></td>
	<td><?= htmlspecialchars(un_null($msg[L2File\SystemMsg::MESSAGE])) ?></td>
	<td><?= htmlspecialchars(un_null($msg[L2File\SystemMsg::SUB_MSG])) ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php else: ?>
<?= L('Отображается:') ?> <span id="search_total"><?= $visible_cnt ?></span> <?= L('из') ?> <?= $total ?>
<table class="l2sysmsgs l2sysmsgs-select l2sysmsgs-diff">
	<tr>
		<th>ID</th>
		<th><?= L('Донор ({0})', htmlspecialchars($donor_name)) ?></th>
		<th><?= L('Ваш файл ({0})', htmlspecialchars($file_name)) ?></th>
	</tr>
<?php foreach ($messages as $id => &$msg): ?>
	<?php if (strlen(trim($msg[0][1])) || strlen(trim($msg[1][1]))): ?>
		<tbody id="m<?= $id ?>" class="blk<?= !$msg[0] ? ' disabled' : '' ?>"<?= $msg[2] ? ' style="display:none"' : '' ?>>
			<tr>
				<td rowspan="2"><?= $id ?></td>
				
				<?php if ($msg[0]): ?>
					<td style="color:#<?= $msg[0][2] ?>;opacity:<?= $msg[0][3] ?>"><?= htmlspecialchars($msg[0][0]) ?></td>
				<?php else: ?>
					<td></td>
				<?php endif; ?>
				
				<?php if ($msg[1]): ?>
					<td style="color:#<?= $msg[1][2] ?>;opacity:<?= $msg[1][3] ?>"><?= htmlspecialchars($msg[1][0]) ?></td>
				<?php else: ?>
					<td></td>
				<?php endif; ?>
			</tr>
			<tr class="sm">
				<?php if ($msg[0]): ?>
					<td style="color:#<?= $msg[0][2] ?>;opacity:<?= $msg[0][3] ?>"><?= htmlspecialchars($msg[0][1]) ?></td>
				<?php else: ?>
					<td></td>
				<?php endif; ?>
				
				<?php if ($msg[1]): ?>
					<td style="color:#<?= $msg[1][2] ?>;opacity:<?= $msg[1][3] ?>"><?= htmlspecialchars($msg[1][1]) ?></td>
				<?php else: ?>
					<td></td>
				<?php endif; ?>
			</tr>
		</tbody>
	<?php else: ?>
		<tr id="m<?= $id ?>"<?= $msg[2] ? ' style="display:none"' : '' ?><?= !$msg[0] ? ' class="disabled"' : '' ?>>
			<td><?= $id ?></td>
			<?php if ($msg[0]): ?>
				<td style="color:#<?= $msg[0][2] ?>;opacity:<?= $msg[0][3] ?>"><?= htmlspecialchars($msg[0][0]) ?></td>
			<?php else: ?>
				<td></td>
			<?php endif; ?>
			
			<?php if ($msg[1]): ?>
				<td style="color:#<?= $msg[1][2] ?>;opacity:<?= $msg[1][3] ?>"><?= htmlspecialchars($msg[1][0]) ?></td>
			<?php else: ?>
				<td></td>
			<?php endif; ?>
		</tr>
	<?php endif; ?>
<?php endforeach; ?>
</table>
<?php endif; ?>

<?php if ($is_diff): ?>
<div id="merge_window" class="modal_window">
	<div class="padd">
		<div class="save_error"></div>
		
		<?= L('Выбрано {0} сообщений.', '<span id="win_selected_cnt"></span>') ?>
		<div class="hr"></div>
		
		<?= L('Выберите, какие параметры скопировать при объединении:') ?>
		<div class="hr"></div>
		<label><input type="checkbox" value="1" name="xuj" checked="checked" id="cb_copy_message" /> <?= L('Текст в чате') ?></label><br />
		<label><input type="checkbox" value="1" name="xuj" checked="checked" id="cb_copy_sub_msg" /> <?= L('Текст на экране') ?></label><br />
		<label><input type="checkbox" value="1" name="xuj" checked="checked" id="cb_copy_position" /> <?= L('Позиция на экране') ?></label><br />
		<label><input type="checkbox" value="1" name="xuj" checked="checked" id="cb_copy_delay_speed" /> <?= L('Скорость появления') ?></label><br />
		<label><input type="checkbox" value="1" name="xuj" checked="checked" id="cb_copy_duration" /> <?= L('Время показа') ?></label><br />
		<label><input type="checkbox" value="1" name="xuj" checked="checked" id="cb_copy_sound" /> <?= L('Звук при сообщении') ?></label><br />
		<label><input type="checkbox" value="1" name="xuj" checked="checked" id="cb_copy_color" /> <?= L('Цвет') ?></label><br />
		<label><input type="checkbox" value="1" name="xuj" checked="checked" id="cb_copy_opacity" /> <?= L('Прозрачность') ?></label><br />
		<label><input type="checkbox" value="1" name="xuj" checked="checked" id="cb_copy_head" /> <?= L('Арка над сообщением') ?></label><br />
		<div class="hr"></div>
		
		<input type="submit" id="save_merge" value="<?= L('Сохранить') ?>" />
		<input type="submit" value="<?= L('Отмена') ?>" class="modal_window_close" w="merge_window" />
	</div>
</div>
<?php else: ?>
<div id="bulk_edit_dialog" class="modal_window">
	<div class="padd">
		<div class="save_error"></div>
		
		<?= L('Выбрано {0} сообщений.', '<span id="win_selected_cnt"></span>') ?>
		<div class="hr"></div>
		
		<label><input type="checkbox" value="1" name="xuj" id="cb_delete_chat_msg" /> <?= L('Удалить сообщение в чате') ?></label><br />
		<small><?= L('Будет установлено пустое сообщение вместо реального.') ?></small>
		<div class="hr"></div>
		
		<label><input type="checkbox" value="1" name="xuj" id="cb_delete_scr_msg" /> <?= L('Удалить сообщение на экране') ?></label><br />
		<small><?= L('Сообщение больше не будет выводиться на экране.') ?></small>
		<div class="hr"></div>
		
		<label><input type="checkbox" value="1" name="xuj" id="cb_delete_sound" /> <?= L('Отключить звуковое оповещение') ?></label><br />
		<?= L('Отключает звук при сообщении.') ?>
		<div class="hr"></div>
		
		<label><input type="checkbox" value="1" name="xuj" id="cb_change_color" /> <?= L('Перекрасить в цвет') ?></label><br />
		<?= L('Установить цвет текста') ?>: 
			<span id="bulk_color_block" class="js-open_color_picker" data-selector="#bulk_color_selector"></span>
			[<span class="js-reset_color pointer" data-selector="#bulk_color_selector"><?= L('сброс') ?></span>]
			<br />
		<div id="bulk_color_selector"></div>
		<?= L('Непрозрачность') ?> [0-255]:
		<input type="text" size="3" id="bulk_transparent_value" class="js-slider_update" data-slider="bulk_transparent" value="" /><br />
		<div id="bulk_transparent"></div>
		<div class="hr"></div>
		
		<b><?= L('Пример:') ?></b>
		<textarea id="bulk_example_text" class="max_width" readonly="readonly"><?= L("Съешь же ещё этих мягких французских булок, да выпей чаю.") ?></textarea>
		
		<div class="hr"></div>
		
		<input type="submit" id="save_bulk" value="<?= L('Сохранить') ?>" />
		<input type="submit" value="<?= L('Отмена') ?>" class="modal_window_close" w="bulk_edit_dialog" />
	</div>
</div>

<div id="edit_dialog" class="modal_window">
	<div class="padd">
		<input type="hidden" name="id" id="message_id" value="" />
		<div class="save_error"></div>
		
		<?= L('Сообщение (в чате)') ?>:<br />
		<textarea class="max_width" name="msg" id="message"></textarea><br />
		<?= L('Сообщение (на экране)') ?>:<br />
		<textarea class="max_width" name="submsg" id="sub_message"></textarea><br />
		<div class="hr"></div>
		<?= L('Положение на экране') ?>:<br />
		<table width="100%">
			<tr>
				<td width="40%">
					<table class="screen_position" width="100%">
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
				<td>
					<div>
						<?= L('Цвет текста') ?>:
						<span id="color_block" class="js-open_color_picker" data-selector="#color_selector"></span>
						[<span class="js-reset_color pointer" data-selector="#color_selector"><?= L('сброс') ?></span>]
						<br />
						<div id="color_selector"></div>
						<?= L('Непрозрачность') ?> [0-255]:
						<input type="text" size="3" id="transparent_value" value="" class="js-slider_update" data-slider="transparent" /><br />
						<div id="transparent"></div>
					</div>
				</td>
			</tr>
		</table>
		<div class="hr"></div>
		
		<?= L('Длительность показа сообщения') ?>:
		<input type="text" size="3" id="message_timeout" value="" class="js-slider_update" data-slider="message_timeout_slider" /> <?= L('секунд') ?>
		<div style="float: right">
			<?= L('Показать') ?>:
			<select name="message_show_speed" id="message_show_speed">
				<option value="0"><?= L('Мгновенно') ?></option>
				<option value="1"><?= L('Постепенно') ?></option>
				<option value="11"><?= L('Плавно') ?></option>
			</select>
		</div>
		<br />
		<div id="message_timeout_slider"></div>
		<div class="hr"></div>
		
		<label title="<?= L('Очень нихуёвая приебенция') ?>">
			<input type="checkbox" value="1" name="head" id="head_on_msg" /> <?= L('Декоративная хренька над сообщением') ?>
		</label><br />
		
		<?= L('Звук при сообщении') ?>:<br />
		<input type="text" value="" name="music" id="msg_music" style="width: 97%" />
		<div class="float">
			<div class="left"><?= L('Или выберите из списка') ?>:</div>
			<div class="right"><input type="text" value="" name="music_search" id="msg_music_search" size="14" placeholder="<?= L('Поиск...') ?>" class="right" /></div>
		</div>
		
		<div style="height: 150px; overflow-y: scroll" id="music_list">
		<?php foreach ($sounds as $sound): ?>
			<div class="music" data-sound="<?= htmlspecialchars($sound) ?>">
				<img src="static/images/play.png" alt="" data-sound="<?= htmlspecialchars(get_music_path($sound)) ?>" class="sound_play" />
				<?= htmlspecialchars(normalize_music_name($sound)) ?>
			</div>
		<?php endforeach; ?>
		</div>
		<div class="hr"></div>
		<input type="submit" id="save_lang" value="<?= L('Сохранить') ?>" />
		<input type="submit" value="<?= L('Отмена') ?>" class="modal_window_close" w="edit_dialog" />
	</div>
</div>
<?php endif; ?>

<script type="text/javascript">
L2SysMsgEditor.init({
	id: <?= json_encode($file_id) ?>, 
	name: <?= json_encode($file_name) ?>, 
	meta: <?= json_encode($metadata) ?>, 
	search: <?= json_encode($search) ?>, 
	isDiff: <?= json_encode($is_diff) ?>, 
	donor: {
<?php if ($is_diff): ?>
		id: <?= json_encode($donor_id) ?>, 
		name: <?= json_encode($donor_name) ?>
<?php endif; ?>
	}
});
</script>
