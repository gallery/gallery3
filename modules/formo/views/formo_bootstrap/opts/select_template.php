<?php if ($field->get('blank') === TRUE): ?>
<option></option>
<?php endif; ?>
<?php foreach ($opts as $key => $opt): ?>
	<?php if (is_array($opt)): ?>
		<optgroup label="<?=$key?>">
		<?php foreach ($opt as $_key => $_opt): ?>
			<?php if ($field->val() == $_key OR (is_array($field->val()) AND in_array($_key, $field->val()))): ?>
			<option value="<?=$_key?>" selected="selected"><?=$_opt?></option>
			<?php else: ?>
			<option value="<?=$_key?>"><?=$_opt?></option>
			<?php endif; ?>
		<?php endforeach; ?>
		</optgroup>
	<?php else: ?>
		<?php if ($field->val() == $key OR (is_array($field->val()) AND in_array($key, $field->val()))): ?>
		<option value="<?=$key?>" selected="selected"><?=$opt?></option>
		<?php else: ?>
		<option value="<?=$key?>"><?=$opt?></option>
		<?php endif; ?>
	<?php endif; ?>
<?php endforeach; ?>