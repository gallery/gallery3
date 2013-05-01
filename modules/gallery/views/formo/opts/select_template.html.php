<? if ($field->get('blank') === TRUE): ?>
<option></option>
<? endif; ?>
<? foreach ($opts as $key => $opt): ?>
  <? if ($field->val() == $key): ?>
  <option value="<?=$key?>" selected="selected"><?=$opt?></option>
  <? else: ?>
  <option value="<?=$key?>"><?=$opt?></option>
  <? endif; ?>
<? endforeach; ?>