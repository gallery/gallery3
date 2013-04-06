<span class="radio opts">
<?php foreach ($opts as $key => $opt): ?>
	<label class="radio">
		<input type="radio" name="<?=$field->name()?>" value="<?=$key?>" <?php if ($key == $field->val()) echo ' checked="checked"'; ?> /> <span class="opt-label"><?=$opt?></span>
	</label>
<?php endforeach; ?>
</span>