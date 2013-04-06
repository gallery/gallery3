<span class="radio opts">
<?php foreach ($opts as $key => $opt): ?>
	<label>
		<?php if ($field->val() == $key): ?>
		<span class="radio opt"><input type="radio" name="<?=$field->name()?>" value="<?=$key?>" checked="checked"/></span>
		<?php else: ?>
		<span class="radio opt"><input type="radio" name="<?=$field->name()?>" value="<?=$key?>" /></span>
		<?php endif; ?>
		<span class="radio label"><?=$opt?></span>
	</label>
<?php endforeach; ?>
</span>