<? defined("SYSPATH") or die("No direct script access."); ?>
<ul>
  <? foreach ($tag_list as $tag): ?>
    <li class="size<?=$tag["class"] ?>">
      <span><?= $tag["count"] ?> photos are tagged with </span>
      <a href="#"><?=$tag["name"] ?></a>
    </li>
  <? endforeach; ?>
</ul>

<form id="gAddTag">
  <ul class="gInline">
    <li><input type="text" class="text" value="add new tags..." id="gNewTags" /></li>
    <li class="gNoLabels"><input type="submit" value="add" /></li>
  </ul>
  <label for="gNewTags" class="gUnderState">(separated by commas)</label>
</form>
