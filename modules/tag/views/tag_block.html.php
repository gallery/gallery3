<? defined("SYSPATH") or die("No direct script access."); ?>
<ul>
  <? foreach ($tags as $tag): ?>
  <li class="size<?=(int)(($tag->count / $max_count) * 7) ?>">
    <span><?= $tag->count ?> photos are tagged with </span>
    <a href="<?=url::site("tag/$tag->id") ?>"><?= $tag->name ?></a>
  </li>
  <? endforeach ?>
</ul>

<form id="gAddTag">
  <ul>
    <li><input type="text" class="text" value="add new tags..." id="gNewTags" /></li>
    <li><input type="submit" value="add" /></li>
  </ul>
  <label for="gNewTags" class="gUnderState"><?= _("(separated by commas)") ?></label>
</form>

