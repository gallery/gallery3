<? defined("SYSPATH") or die("No direct script access."); ?>
<? if ($tags): ?>
<ul>
  <? foreach ($tags as $tag): ?>
  <li class="size<?=(int)(($tag->count / $max_count) * 7) ?>">
    <span><?= $tag->count ?> photos are tagged with </span>
    <a href="<?=url::site("tags/$tag->id") ?>"><?= $tag->name ?></a>
  </li>
  <? endforeach ?>
</ul>
<? endif ?>

<? if (isset($form)): ?>
<div id="gTagFormContainer">
  <?= $form ?>
</div>
<? endif; ?>

