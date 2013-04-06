<div class="field control-group formo-<?=$field->get('driver')?><?php if ($error = $field->error()) echo ' error'; ?>" id="field-container-<?=$field->alias()?>">
	<label><?=$field->open().$field->html().$field->render_opts().$field->close()?> <span class="checkbox-label"><?=$field->label()?></span></label>

	<?php if ($msg = $field->error()): ?>
		<span class="help-block"><?=$msg?></span>
	<?php elseif ($msg = $field->get('message')): ?>
		<span class="help-block"><?=$msg?></span>
	<?php endif; ?>
</div>