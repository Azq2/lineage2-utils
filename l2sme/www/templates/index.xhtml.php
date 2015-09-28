
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="static/js/soundmanager/script/soundmanager2-nodebug-jsmin.js"></script>
<link rel="stylesheet" media="screen" type="text/css" href="static/css/main.css" />
<link rel="stylesheet" media="screen" type="text/css" href="static/css/colorpicker.css" />
<script type="text/javascript" src="static/js/colorpicker.js"></script>
<script type="text/javascript" src="static/js/functions.js"></script>
<script type="text/javascript" src="static/js/msg_table.js"></script>

<div style="display: block" class="modal_window">
	<div>
		<form action="?" method="POST" enctype="multipart/form-data">
			<?= L('Этот сервис предназначен для редактирования системных сообщений клиентов игры Lineage II High Five. ') ?><br />
			<?= L('Для начала редактирования выгрузите файл') ?> <b>systemmsg-ru.dat</b> <?php L('или') ?> <b>SystemMsg-e.dat</b>
				 <?php L('или') ?> <b>SystemMsg-k.dat</b><br />
			<?= L('Поддерживается только 413 версия протокола. ') ?>
			<div class="hr"></div>
			<?= L('Файл') ?> <b>systemmsg-ru.dat</b> <?= L('или') ?> <b>SystemMsg-e.dat</b> (<?= L('до %d Mb', 1) ?>):<br />
			<input type="file" name="file" /><br />
			<?php if ($errors): ?>
			<small style="color: red"><?= implode("<br />", $errors) ?></small>
			<?php endif; ?>
			
			<div class="hr"></div>
			<input type="submit" value="<?= L('Редактировать') ?>" />
			<br /><br /><br />
			<a href="http://zhumarin.ru"><?= L('Контакты для связи') ?></a><br />
			<?= L('Если что-то не работает или у Вас есть идеи по улучшению - пишите мне по этим контактам. ') ?>
		</form>
	</div>
</div>
