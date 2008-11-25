<? defined("SYSPATH") or die("No direct script access."); ?>
<div class="gTagFilter">
  <? if ($filter < (tag::$NUMBER_OF_BUCKETS - 1)): ?>
    <? $lessFilter = $filter + 1; ?>
    <a id="gTagLess" href="<?= url::site("tag/?filter=$lessFilter") ?>">See Less</a>
  <? endif; ?>
  <? if ($filter > 1): ?>
    <? $moreFilter = $filter - 1;?>
    <a  id="gTagMore" href="<?= url::site("tag/?filter=$moreFilter") ?>">See More</a>
  <? endif; ?>

  <div id="gTagReloadProgress" class="">Resizing Tag Cloud...</div>
  <hr />
</div>
<ul>
  <? foreach ($tag_list as $tag): ?>
    <li class="size<?=$tag["class"] ?>">
      <span><?= $tag["count"] ?> photos are tagged with </span>
      <a href="<?=url::site("/tag/{$tag["id"]}?filter=$filter") ?>"><?=$tag["name"] ?></a>
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
