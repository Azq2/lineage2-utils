
<div style="display: block" class="modal_window">
	<div>
		<form action="?" method="POST" enctype="multipart/form-data">
			<div class="padd">
			<?= L('Этот сервис предназначен для редактирования системных сообщений клиентов игры Lineage II High Five. ') ?><br />
			<?= L('Для начала редактирования выгрузите файл') ?> <b>systemmsg-ru.dat</b> <?php L('или') ?> <b>SystemMsg-e.dat</b>
				 <?php L('или') ?> <b>SystemMsg-k.dat</b><br />
			<?= L('Поддерживается только 413 версия протокола. ') ?>
			</div>
			
			<div class="tabs">
				<div class="tabs-header">
					<div class="tab<?= $tab == 'edit' ? ' active' : '' ?>" data-content="edit"><?= L('Редактировать') ?></div>
					<div class="tab<?= $tab == 'diff' ? ' active' : '' ?>" data-content="diff"><?= L('Сравнить / Объединить') ?> <small>(beta)</small></div>
				</div>
				<div class="tab-content padd<?= $tab == 'edit' ? ' active' : '' ?>" data-id="edit">
					<form action="?redirect=edit_file&tab=edit&files=1" method="POST" enctype="multipart/form-data">
						<?= L('Файл') ?> <b>systemmsg-ru.dat</b> <?= L('или') ?> <b>SystemMsg-e.dat</b> (<?= L('до {0} Mb', 1) ?>):<br />
						<input type="file" name="file_0" /><br />
						<input type="hidden" name="key_0" value="file_id" />
						<input type="hidden" name="key_name_0" value="file_name" />
						
						<?php if (isset($errors[0]) && $tab == 'edit'): ?>
						<small style="color: red"><?= $errors[0] ?></small>
						<?php endif; ?>
						
						<div class="hr"></div>
						<input type="submit" value="<?= L('Редактировать') ?>" />
						<br /><br /><br />
						<div style="font-size: 1.1em;">
							<a href="http://zhumarin.ru"><?= L('Контакты для связи') ?></a><br />
							<?= L('Если что-то не работает или у Вас есть идеи по улучшению - пишите мне по этим контактам. ') ?><br />
						</div>
					</form>
				</div>
				<div class="tab-content padd<?= $tab == 'diff' ? ' active' : '' ?>" data-id="diff">
					<form action="?redirect=diff_file&tab=diff&files=2" method="POST" enctype="multipart/form-data">
						<?= L('Этот редактор позволяет смотреть разницу между двумя файлами и сливать их в один. ') ?><br />
						<?= L('Это полезно, когда нужно адаптировать файл для другого сервера Lineage 2. ') ?><br />
						<div class="hr"></div>
						<?= L('Реципиент (файл, <u>в</u> который импортировать сообщения)') ?>:<br />
						
						<input type="file" name="file_0" /><br />
						<input type="hidden" name="key_0" value="file_id" />
						<input type="hidden" name="key_name_0" value="file_name" />
						
						<?php if (isset($errors[0]) && $tab == 'diff'): ?>
						<small style="color: red"><?= $errors[0] ?></small>
						<?php endif; ?>
						
						<div class="hr"></div>
						<?= L('Донор (файл, <u>из</u> которого импортировать сообщения)') ?>:<br />
						
						<input type="file" name="file_1" /><br />
						<input type="hidden" name="key_1" value="donor_id" />
						<input type="hidden" name="key_name_1" value="donor_name" />
						
						<?php if (isset($errors[1]) && $tab == 'diff'): ?>
						<small style="color: red"><?= $errors[1] ?></small>
						<?php endif; ?>
						
						<div class="hr"></div>
						<input type="submit" value="<?= L('Сравнить') ?>" />
					</form>
				</div>
			</div>
		</form>
	</div>
</div>
