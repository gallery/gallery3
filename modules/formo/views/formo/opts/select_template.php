<?php if ($field->get('blank') === TRUE): ?>
<option></option>
<?php endif; ?>
<?php foreach ($opts as $key => $opt): ?>
	<?php if ($field->val() == $key): ?>
	<option value="<?=$key?>" selected="selected"><?=$opt?></option>
	<?php else: ?>
	<option value="<?=$key?>"><?=$opt?></option>
	<?php endif; ?>
<?php endforeach; ?>