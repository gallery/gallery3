<span class="checkbox opts">
<? foreach ($opts as $key => $opt): ?>
  <label>
    <? if (in_array($key, $field->val())): ?>
    <span class="checkbox opt"><input type="checkbox" name="<?=$field->name()?>[]" value="<?=$key?>" checked="checked" /></span>
    <? else: ?>
    <span class="checkbox opt"><input type="checkbox" name="<?=$field->name()?>[]" value="<?=$key?>" /></span>
    <? endif; ?>
    <span class="checkbox label"><?=$opt?></span>
  </label>
<? endforeach; ?>
</span>