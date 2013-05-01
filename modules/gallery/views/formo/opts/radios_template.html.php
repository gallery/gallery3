<span class="radio opts">
<? foreach ($opts as $key => $opt): ?>
  <label>
    <? if ($field->val() == $key): ?>
    <span class="radio opt"><input type="radio" name="<?=$field->name()?>" value="<?=$key?>" checked="checked"/></span>
    <? else: ?>
    <span class="radio opt"><input type="radio" name="<?=$field->name()?>" value="<?=$key?>" /></span>
    <? endif; ?>
    <span class="radio label"><?=$opt?></span>
  </label>
<? endforeach; ?>
</span>