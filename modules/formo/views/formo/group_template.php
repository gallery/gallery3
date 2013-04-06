<div class="group <?=$field->get('driver')?>" id="form-container-<?=$field->alias()?>">
	<?=$field->open()?>
		<?php foreach ($field->as_array() as $_field): ?>
		<?=$_field->render()?>
		<?php endforeach; ?>
	<?=$field->close()?>
</div>