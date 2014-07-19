
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
			Этот сервис предназначен для редактирования системных сообщений клиентов игры Lineage II High Five. <br />
			Для начала редактирования выгрузите файл <b>systemmsg-ru.dat</b> или <b>SystemMsg-e.dat</b><br />
			Поддерживается только 413 версия протокола. 
			<div class="hr"></div>
			Файл <b>systemmsg-ru.dat</b> или <b>SystemMsg-e.dat</b> (до 1 Mb):<br />
			<input type="file" name="file" /><br />
			<?php if ($errors): ?>
			<small style="color: red"><?= implode("<br />", $errors) ?></small>
			<?php endif; ?>
			
			<div class="hr"></div>
			<input type="submit" value="Редактировать" />
		</form>
	</div>
</div>
